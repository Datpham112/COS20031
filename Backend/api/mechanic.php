<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/api_helpers.php';
require_once __DIR__ . '/../auth/auth_check.php';

$pdo = get_db_connection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        if (isset($_GET['me'])) {
            $staff = require_login();
            if ($staff['role_type'] !== 'Mechanic') {
                json_fail(403, 'This endpoint is for logged-in mechanics only.');
            }

            $mechanicId = $staff['linked_mechanic_id'];
            if (!$mechanicId) {
                $mechanicStmt = $pdo->prepare('SELECT Mechanic_ID FROM Mechanic WHERE Full_Name = ? LIMIT 1');
                $mechanicStmt->execute([$staff['full_name']]);
                $mechanicId = $mechanicStmt->fetchColumn();
                if ($mechanicId !== false) {
                    $mechanicId = (int) $mechanicId;
                }
            }
            if (!$mechanicId) {
                json_fail(403, 'This endpoint is for logged-in mechanics only.');
            }

            $stmt = $pdo->prepare(
                'SELECT
                    m.Mechanic_ID AS Mechanic_ID,
                    m.Workshop_ID AS Workshop_ID,
                    m.Full_Name AS Full_Name,
                    w.Depot_ID AS Depot_ID,
                    d.Location_Name AS Depot_Name,
                    s.Contact_Info AS Contact_Info
                 FROM Mechanic m
                 LEFT JOIN Workshop w ON w.Workshop_ID = m.Workshop_ID
                 LEFT JOIN Depot d ON d.Depot_ID = w.Depot_ID
                 LEFT JOIN Staff s ON (
                     (s.Linked_Mechanic_ID = m.Mechanic_ID)
                     OR (s.Role_Type = ? AND s.Full_Name = m.Full_Name)
                 )
                 WHERE m.Mechanic_ID = ?'
            );
            $stmt->execute(['Mechanic', $mechanicId]);
            $mechanic = $stmt->fetch();
            if (!$mechanic) {
                json_response(['error' => 'Mechanic not found'], 404);
            }

            $mechanic['Employment_Status'] = 'Active';
            $mechanic['Status'] = 'Active';

            $stmt = $pdo->prepare(
                'SELECT
                    ma.Activity_ID AS Activity_ID,
                    ama.Labour_Hours AS Labour_Hours,
                    ama.Labour_Hours AS labour_hours,
                    ma.Job_ID AS Job_ID,
                    ma.Activity_Type AS Activity_Type,
                    ma.Diagnostic_Result AS Diagnostic_Result,
                    ma.Repeat_Fault_Indicator AS Repeat_Fault_Indicator,
                    ma.Warranty_Indicator AS Warranty_Indicator,
                    mj.VIN AS VIN,
                    mj.Workshop_ID AS Workshop_ID,
                    mj.Date_Opened AS Date_Opened,
                    mj.Date_Closed AS Date_Closed,
                    mj.Downtime_Hours AS Downtime_Hours,
                    mj.Downtime_Hours AS downtime_hours,
                    mj.Total_Cost AS Total_Cost,
                    mj.Priority AS Priority,
                    mj.Priority AS priority
                 FROM Maintenance_Activity ma
                 JOIN Maintenance_Job mj ON mj.Job_ID = ma.Job_ID
                 LEFT JOIN Activity_Mechanic_Assignment ama
                     ON ama.Activity_ID = ma.Activity_ID
                    AND ama.Mechanic_ID = ?
                 WHERE ama.Mechanic_ID = ?
                 ORDER BY mj.Date_Opened DESC, ma.Activity_ID DESC'
            );
            $stmt->execute([$mechanicId, $mechanicId]);
            $assignedWork = $stmt->fetchAll();

            if (!$assignedWork && !empty($mechanic['Workshop_ID'])) {
                $stmt = $pdo->prepare(
                    'SELECT
                        ma.Activity_ID AS Activity_ID,
                        NULL AS Labour_Hours,
                        ma.Job_ID AS Job_ID,
                        ma.Activity_Type AS Activity_Type,
                        ma.Diagnostic_Result AS Diagnostic_Result,
                        ma.Repeat_Fault_Indicator AS Repeat_Fault_Indicator,
                        ma.Warranty_Indicator AS Warranty_Indicator,
                        mj.VIN AS VIN,
                        mj.Workshop_ID AS Workshop_ID,
                        mj.Date_Opened AS Date_Opened,
                        mj.Date_Closed AS Date_Closed,
                        mj.Downtime_Hours AS downtime_hours,
                        mj.Total_Cost AS total_cost,
                        mj.Priority AS priority
                     FROM Maintenance_Activity ma
                     JOIN Maintenance_Job mj ON mj.Job_ID = ma.Job_ID
                     WHERE mj.Workshop_ID = ?
                     ORDER BY mj.Date_Opened DESC, ma.Activity_ID DESC'
                );
                $stmt->execute([(int) $mechanic['Workshop_ID']]);
                $assignedWork = $stmt->fetchAll();
            }

            $stmt = $pdo->prepare(
                'SELECT
                    Certification_Name AS Certification_Name,
                    Issue_Date AS Issue_Date,
                    Expiry_Date AS Expiry_Date
                 FROM Mechanic_Certification
                 WHERE Mechanic_ID = ?
                 ORDER BY Expiry_Date'
            );
            $stmt->execute([$mechanicId]);
            $certifications = $stmt->fetchAll();

            $stmt = $pdo->prepare(
                'SELECT
                    Certificate_Name AS Certificate_Name,
                    Issue_Date AS Issue_Date,
                    Expiry_Date AS Expiry_Date
                 FROM Mechanic_Cert_History
                 WHERE Mechanic_ID = ?
                 ORDER BY Expiry_Date'
            );
            $stmt->execute([$mechanicId]);
            $certHistory = $stmt->fetchAll();

            json_response([
                'mechanic'        => $mechanic,
                'assignedWork'    => $assignedWork,
                'assigned_work'   => $assignedWork,
                'certifications'  => $certifications,
                'certHistory'     => $certHistory,
                'cert_history'    => $certHistory,
            ]);
        }

        $staff = require_table_permission('Mechanic', 'read');

        if (isset($_GET['mechanic_id'])) {
            $stmt = $pdo->prepare('SELECT * FROM Mechanic WHERE Mechanic_ID = ?');
            $stmt->execute([$_GET['mechanic_id']]);
            $row = $stmt->fetch();
            if ($row && $staff['role_type'] !== 'Head Manager' && (int) $row['Workshop_ID'] !== own_workshop_id($pdo, $staff)) {
                json_response(['error' => 'Mechanic not found'], 404);
            }
            json_response($row ?: ['error' => 'Mechanic not found'], $row ? 200 : 404);
        }

        if ($staff['role_type'] === 'Head Manager') {
            json_response($pdo->query('SELECT * FROM Mechanic ORDER BY Full_Name')->fetchAll());
        }
        $stmt = $pdo->prepare('SELECT * FROM Mechanic WHERE Workshop_ID = ? ORDER BY Full_Name');
        $stmt->execute([own_workshop_id($pdo, $staff)]);
        json_response($stmt->fetchAll());
        break;

    case 'POST':
        $staff = require_table_permission('Mechanic', 'write');
        $data = get_request_body();
        $missing = missing_fields($data, ['workshop_id', 'full_name']);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }
        if ((int) $data['workshop_id'] !== own_workshop_id($pdo, $staff)) {
            json_fail(403, 'You can only add mechanics to your own workshop.');
        }

        run_write($pdo, 'INSERT INTO Mechanic (Workshop_ID, Full_Name) VALUES (?, ?)',
            [$data['workshop_id'], $data['full_name']], 'Mechanic created', 201, [
                'staff_id' => $staff['staff_id'], 'table' => 'Mechanic', 'action' => 'CREATE',
                'summary' => $data['full_name'],
            ]);
        break;

    case 'PUT':
        $staff = require_table_permission('Mechanic', 'write');
        if (!isset($_GET['mechanic_id'])) {
            json_response(['error' => 'Missing ?mechanic_id= in URL'], 422);
        }
        if (!mechanic_in_own_workshop($pdo, $_GET['mechanic_id'], $staff)) {
            json_fail(403, 'You can only edit mechanics in your own workshop.');
        }
        $data = get_request_body();
        $missing = missing_fields($data, ['workshop_id', 'full_name']);
        if ($missing) {
            json_response(['error' => 'Missing fields', 'fields' => $missing], 422);
        }
        if ((int) $data['workshop_id'] !== own_workshop_id($pdo, $staff)) {
            json_fail(403, 'You cannot move a mechanic to another workshop.');
        }

        run_write($pdo, 'UPDATE Mechanic SET Workshop_ID = ?, Full_Name = ? WHERE Mechanic_ID = ?',
            [$data['workshop_id'], $data['full_name'], $_GET['mechanic_id']], 'Mechanic updated', 200, [
                'staff_id' => $staff['staff_id'], 'table' => 'Mechanic', 'action' => 'UPDATE',
                'summary' => $data['full_name'],
            ]);
        break;

    case 'DELETE':
        $staff = require_table_permission('Mechanic', 'write');
        if (!isset($_GET['mechanic_id'])) {
            json_response(['error' => 'Missing ?mechanic_id= in URL'], 422);
        }
        if (!mechanic_in_own_workshop($pdo, $_GET['mechanic_id'], $staff)) {
            json_fail(403, 'You can only delete mechanics in your own workshop.');
        }
        run_write($pdo, 'DELETE FROM Mechanic WHERE Mechanic_ID = ?', [$_GET['mechanic_id']], 'Mechanic deleted', 200, [
            'staff_id' => $staff['staff_id'], 'table' => 'Mechanic', 'action' => 'DELETE',
            'summary' => $_GET['mechanic_id'],
        ]);
        break;

    default:
        json_response(['error' => 'Method not allowed'], 405);
}

function own_workshop_id(PDO $pdo, array $staff): ?int
{
    $stmt = $pdo->prepare('SELECT Workshop_ID FROM Workshop WHERE Depot_ID = ?');
    $stmt->execute([$staff['depot_id']]);
    $id = $stmt->fetchColumn();
    return $id !== false ? (int) $id : null;
}

function mechanic_in_own_workshop(PDO $pdo, string $mechanicId, array $staff): bool
{
    if ($staff['role_type'] === 'Head Manager') return true;
    $stmt = $pdo->prepare('SELECT Workshop_ID FROM Mechanic WHERE Mechanic_ID = ?');
    $stmt->execute([$mechanicId]);
    $workshopId = $stmt->fetchColumn();
    return $workshopId !== false && (int) $workshopId === own_workshop_id($pdo, $staff);
}
