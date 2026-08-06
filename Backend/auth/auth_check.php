<?php
/**
 * Backend/auth/auth_check.php
 * ------------------------------------------------------------------
 * Shared session helpers for API endpoints. Include this, then call
 * require_login() or require_role([...]) at the top of any
 * Backend/api/*.php file that should be protected.
 *
 * This does NOT redirect (API endpoints return JSON, not HTML pages).
 * On failure it sends a 401/403 with a JSON body and stops execution.
 * Static Frontend/*.html pages guard themselves client-side by
 * calling Backend/api/me.php on load (see assets/auth-guard.js).
 * ------------------------------------------------------------------
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Roles considered "management" -- can see everything across depots. */
const MANAGEMENT_ROLES = ['Head Manager', 'Depot Manager', 'Driver Manager', 'Workshop Manager', 'Inventory Manager'];

/** Roles considered "field" -- restricted to their own records. */
const FIELD_ROLES = ['Mechanic', 'Driver'];

/**
 * Returns the logged-in staff member's session data, or null if
 * nobody is logged in.
 */
function current_staff(): ?array
{
    if (!isset($_SESSION['staff_id'])) {
        return null;
    }
    return [
        'staff_id'         => $_SESSION['staff_id'],
        'full_name'        => $_SESSION['full_name'],
        'role_type'        => $_SESSION['role_type'],
        'depot_id'         => $_SESSION['depot_id'],
        'linked_driver_id' => $_SESSION['linked_driver_id'] ?? null,
        'linked_mechanic_id' => $_SESSION['linked_mechanic_id'] ?? null,
    ];
}

/**
 * Stops the request with 401 JSON unless someone is logged in.
 * Returns the staff array so callers can use it immediately:
 *   $staff = require_login();
 */
function require_login(): array
{
    $staff = current_staff();
    if ($staff === null) {
        json_fail(401, 'Not logged in.');
    }
    return $staff;
}

/**
 * Stops the request with 403 JSON unless the logged-in staff's role
 * is in $allowedRoles. Returns the staff array on success.
 */
function require_role(array $allowedRoles): array
{
    $staff = require_login();
    if (!in_array($staff['role_type'], $allowedRoles, true)) {
        json_fail(403, 'You do not have permission to do that.');
    }
    return $staff;
}

function json_fail(int $status, string $message): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => $message]);
    exit;
}

/**
 * ------------------------------------------------------------------
 * Table-level permission map, taken directly from the role/scope
 * matrix: Head Manager (company-wide, READ-ONLY), Depot Manager
 * (own depot), Driver Manager (own depot), Workshop Manager (own
 * workshop), Inventory Manager (company-wide), Mechanic (own
 * assignments), Driver (own records).
 *
 * 'scope' says how to further filter rows for non-Head-Manager roles:
 *   'depot'     -> filter by Depot_ID (Vehicle, Driver-family tables)
 *   'workshop'  -> filter by Workshop_ID (Maintenance-family tables)
 *   'self'      -> Driver only sees their own row; Mechanic only sees
 *                  activities they're assigned to
 *   'none'      -> company-wide, no row filtering (Part/Supplier/Staff/...)
 * ------------------------------------------------------------------
 */
const TABLE_PERMISSIONS = [
    'Vehicle' => [
        'read'  => ['Head Manager', 'Depot Manager', 'Workshop Manager', 'Driver Manager'],
        'write' => ['Depot Manager'],
        'scope' => 'depot',
    ],
    'Driver' => [
        'read'  => ['Head Manager', 'Depot Manager', 'Driver Manager', 'Driver'],
        'write' => ['Driver Manager'],
        'scope' => 'depot_or_self',
    ],
    'Driver_Certification' => [
        'read'  => ['Head Manager', 'Depot Manager', 'Driver Manager', 'Driver'],
        'write' => ['Driver Manager'],
        'scope' => 'depot_or_self',
    ],
    'Vehicle_Driver_Assignment' => [
        'read'  => ['Head Manager', 'Depot Manager', 'Driver Manager', 'Driver'],
        'write' => ['Driver Manager'],
        'scope' => 'depot_or_self',
    ],
    'Safety_Event' => [
        'read'  => ['Head Manager', 'Driver Manager', 'Driver'],
        'write' => ['Driver Manager'],
        'scope' => 'depot_or_self',
    ],
    'Driver_Safety_Score' => [
        'read'  => ['Head Manager', 'Driver Manager', 'Driver'],
        'write' => ['Driver Manager'],
        'scope' => 'depot_or_self',
    ],
    'Maintenance_Job' => [
        'read'  => ['Head Manager', 'Workshop Manager', 'Mechanic'],
        'write' => ['Workshop Manager'],
        'scope' => 'workshop_or_assigned',
    ],
    'Predictive_Alert' => [
        'read'  => ['Head Manager', 'Workshop Manager'],
        'write' => ['Workshop Manager'],
        'scope' => 'workshop',
    ],
    'Activity_Mechanic_Assignment' => [
        'read'  => ['Head Manager', 'Workshop Manager', 'Mechanic'],
        'write' => ['Workshop Manager'],
        'scope' => 'workshop_or_assigned',
    ],
    'Maintenance_Activity' => [
        'read'  => ['Head Manager', 'Workshop Manager', 'Mechanic'],
        'write' => ['Workshop Manager', 'Mechanic'],
        'scope' => 'workshop_or_assigned',
    ],
    'Activity_Part' => [
        'read'  => ['Head Manager', 'Workshop Manager', 'Mechanic', 'Inventory Manager'],
        'write' => ['Workshop Manager', 'Mechanic'],
        'scope' => 'workshop_or_assigned',
    ],
    'Mechanic' => [
        'read'  => ['Head Manager', 'Workshop Manager'],
        'write' => ['Workshop Manager'],
        'scope' => 'workshop',
    ],
    'Part' => [
        'read'  => ['Head Manager', 'Inventory Manager', 'Mechanic'],
        'write' => ['Inventory Manager'],
        'scope' => 'none',
    ],
    'Supplier' => [
        'read'  => ['Head Manager', 'Inventory Manager'],
        'write' => ['Inventory Manager'],
        'scope' => 'none',
    ],
    'Part_Supplier' => [
        'read'  => ['Head Manager', 'Inventory Manager'],
        'write' => ['Inventory Manager'],
        'scope' => 'none',
    ],
    'Warranty_Claims' => [
        'read'  => ['Head Manager', 'Inventory Manager'],
        'write' => ['Inventory Manager'],
        'scope' => 'none',
    ],
    'Staff' => [
        'read'  => ['Head Manager', 'Depot Manager', 'Driver Manager', 'Workshop Manager', 'Inventory Manager'],
        'write' => ['Head Manager'],
        'scope' => 'none',
    ],
    'Depot' => [
        'read'  => ['Head Manager', 'Depot Manager', 'Driver Manager', 'Workshop Manager', 'Inventory Manager'],
        'write' => ['Head Manager'],
        'scope' => 'none',
    ],
    'Workshop' => [
        'read'  => ['Head Manager', 'Depot Manager', 'Workshop Manager'],
        'write' => ['Head Manager'],
        'scope' => 'none',
    ],
    'Audit_Log' => [
        'read'  => ['Head Manager', 'Depot Manager', 'Driver Manager', 'Workshop Manager', 'Inventory Manager'],
        'write' => [],
        'scope' => 'none',
    ],
];

/**
 * Checks the logged-in staff's role against TABLE_PERMISSIONS for
 * $table + $mode ('read' or 'write'). Stops with 401/403 JSON on
 * failure. Returns the staff array on success.
 *
 * Head Manager is always allowed for 'read' (company-wide, read-only)
 * and always BLOCKED for 'write', regardless of what's listed above --
 * enforced here once so no endpoint can accidentally grant Head
 * Manager write access.
 */
function require_table_permission(string $table, string $mode): array
{
    $staff = require_login();
    $role = $staff['role_type'];

    if ($role === 'Head Manager') {
        if ($mode === 'write') {
            json_fail(403, 'Head Manager has read-only access.');
        }
        return $staff;
    }

    $allowed = TABLE_PERMISSIONS[$table][$mode] ?? [];
    if (!in_array($role, $allowed, true)) {
        json_fail(403, 'You do not have permission to do that.');
    }
    return $staff;
}
