<?php
/**
 * Backend/api/driver_certification.php
 * ------------------------------------------------------------------
 *   GET    driver_certification.php                          -> list (scoped by role)
 *   GET    driver_certification.php?cert_key=DR001|Defensive+Driving  -> one record
 *   POST   driver_certification.php                           -> create
 *   PUT    driver_certification.php?cert_key=XXX               -> update (Expiry_Date only)
 *   DELETE driver_certification.php?cert_key=XXX               -> delete
 *
 * Driver_Certification has a composite primary key (Driver_ID,
 * Certification_Name). To keep this consistent with the rest of the
 * Manage Data API (one ID per row), the pair is exposed to the
 * frontend as a single "cert_key" string: "<Driver_ID>|<Certification_Name>".
 *
 * Required fields for POST: driver_id, certification_name, expiry_date
 * Driver_Name is filled in automatically from the Driver table -- it
 * is not accepted from the frontend, so it can never drift out of
 * sync with the actual driver record.
 *
 * Permissions:
 *   Read:  Head Manager (all), Depot Manager (own depot),
 *          Driver Manager (own depot), Driver (own record only)
 *   Write: Driver Manager (own depot only)
 * ------------------------------------------------------------------
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/api_helpers.php';
require_once __DIR__ . '/../auth/auth_check.php';

$pdo = get_db_connection();
$method = $_SERVER['REQUEST_METHOD'];

const CERT_SELECT = "
    SELECT dc.*, CONCAT(dc.Driver_ID, '|', dc.Certification_Name) AS Cert_Key
    FROM Driver_Certification dc
";

switch ($method) {

    case 'GET':
        $staff = require_table_permission('Driver_Certification', 'read');

        if (isset($_GET['cert_key'])) {
            [$driverId, $certName] = split_cert_key($_GET['cert_key']);
            $stmt = $pdo->prepare(CERT_SELECT . ' WHERE dc.Driver_ID = ? AND dc.Certification_Name = ?');
            $stmt->execute([$driverId, $certName]);
            $row = $stmt->fetch();
            if ($row && !cert_row_visible($pdo, $row, $staff)) {
                json_response(['error' => 'Certification not found'], 404);
            }
            json_response($row ?: ['error' => 'Certification not found'], $row ? 200 : 404);
        }

        if ($staff['role_type'] === 'Head Manager') {
            json_response($pdo->query(CERT_SELECT . ' ORDER BY dc.Driver_ID, dc.Certification_Name')->fetchAll());
        }
        if ($staff['role_type'] === 'Driver') {
            $stmt = $pdo->prepare(CERT_SELECT . ' WHERE dc.Driver_ID = ?');
            $stmt->execute([$staff['linked_driver_id']]);
            json_response($stmt->fetchAll());
        }
        // Depot Manager / Driver Manager -> own depot only
        $stmt = $pdo->prepare(CERT_SELECT . '
            JOIN Driver d ON d.Driver_ID = dc.Driver_ID
            WHERE d.Depot_ID = ?
            ORDER BY dc.Driver_ID, dc.Certification_Name
        ');
        $stmt->execute([$staff['depot_id']]);
        json_response($stmt->fetchAll());
        break;

    case 'POST':
        $staff = require_table_permission('Driver_Certification', 'write');
        $data = get_request_body();
        $required = ['driver_id', 'certification_name', 'expiry_date'];
        $missing = missing_fields($data, $required);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }
        if (!driver_in_own_depot_cert($pdo, $data['driver_id'], $staff)) {
            json_fail(403, 'You can only add certifications for drivers in your own depot.');
        }

        run_write($pdo, '
            INSERT INTO Driver_Certification (Driver_ID, Driver_Name, Certification_Name, Expiry_Date)
            VALUES (?, ?, ?, ?)
        ', [
            $data['driver_id'],
            lookup_driver_name($pdo, $data['driver_id']),
            $data['certification_name'],
            $data['expiry_date'],
        ], 'Certification created', 201, [
            'staff_id' => $staff['staff_id'], 'table' => 'Driver_Certification', 'action' => 'CREATE',
            'summary' => $data['driver_id'] . ' - ' . $data['certification_name'],
        ]);
        break;

    case 'PUT':
        $staff = require_table_permission('Driver_Certification', 'write');
        if (!isset($_GET['cert_key'])) {
            json_response(['error' => 'Missing ?cert_key= in URL'], 422);
        }
        [$driverId, $certName] = split_cert_key($_GET['cert_key']);
        if (!driver_in_own_depot_cert($pdo, $driverId, $staff)) {
            json_fail(403, 'You can only edit certifications for drivers in your own depot.');
        }
        $data = get_request_body();
        $missing = missing_fields($data, ['expiry_date']);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }

        // Driver_ID / Certification_Name form the primary key, so only the
        // expiry date can be changed here -- renaming either would really
        // mean creating a new certification row, not editing this one.
        run_write($pdo, '
            UPDATE Driver_Certification SET Expiry_Date = ?
            WHERE Driver_ID = ? AND Certification_Name = ?
        ', [
            $data['expiry_date'],
            $driverId,
            $certName,
        ], 'Certification updated', 200, [
            'staff_id' => $staff['staff_id'], 'table' => 'Driver_Certification', 'action' => 'UPDATE',
            'summary' => $driverId . ' - ' . $certName,
        ]);
        break;

    case 'DELETE':
        $staff = require_table_permission('Driver_Certification', 'write');
        if (!isset($_GET['cert_key'])) {
            json_response(['error' => 'Missing ?cert_key= in URL'], 422);
        }
        [$driverId, $certName] = split_cert_key($_GET['cert_key']);
        if (!driver_in_own_depot_cert($pdo, $driverId, $staff)) {
            json_fail(403, 'You can only delete certifications for drivers in your own depot.');
        }
        run_write($pdo, 'DELETE FROM Driver_Certification WHERE Driver_ID = ? AND Certification_Name = ?',
            [$driverId, $certName], 'Certification deleted', 200, [
                'staff_id' => $staff['staff_id'], 'table' => 'Driver_Certification', 'action' => 'DELETE',
                'summary' => $driverId . ' - ' . $certName,
            ]);
        break;

    default:
        json_response(['error' => 'Method not allowed'], 405);
}

/** Splits the "<Driver_ID>|<Certification_Name>" composite key from the URL. */
function split_cert_key(string $key): array
{
    $parts = explode('|', $key, 2);
    if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
        json_response(['error' => 'Malformed cert_key'], 422);
    }
    return $parts;
}

function cert_row_visible(PDO $pdo, array $row, array $staff): bool
{
    if ($staff['role_type'] === 'Head Manager') return true;
    if ($staff['role_type'] === 'Driver') return $row['Driver_ID'] === $staff['linked_driver_id'];
    return driver_in_own_depot_cert($pdo, $row['Driver_ID'], $staff);
}

function driver_in_own_depot_cert(PDO $pdo, string $driverId, array $staff): bool
{
    if ($staff['role_type'] === 'Head Manager') return true;
    $stmt = $pdo->prepare('SELECT Depot_ID FROM Driver WHERE Driver_ID = ?');
    $stmt->execute([$driverId]);
    $depotId = $stmt->fetchColumn();
    return $depotId !== false && (int) $depotId === (int) $staff['depot_id'];
}

function lookup_driver_name(PDO $pdo, string $driverId): ?string
{
    $stmt = $pdo->prepare('SELECT Full_Name FROM Driver WHERE Driver_ID = ?');
    $stmt->execute([$driverId]);
    $name = $stmt->fetchColumn();
    return $name !== false ? $name : null;
}
