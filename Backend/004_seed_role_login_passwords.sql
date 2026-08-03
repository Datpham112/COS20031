-- =====================================================================
-- Migration 004: make every role able to actually log in
-- =====================================================================
-- Run this AFTER migrations 002 and 003.
--
-- Problem this fixes:
--   DML/Staff_data.sql inserts all 8 sample staff with Password_Hash =
--   NULL. login_process.php (correctly) refuses to log anyone in when
--   Password_Hash is NULL, so none of those 8 accounts could ever sign
--   in. There was also no seeded 'Mechanic' role account, so that role
--   could not be demoed either (only 'Driver' had one, from migration
--   003).
--
-- What this does:
--   1) Sets a real bcrypt hash (password_hash() compatible, $2b$ format)
--      on all 8 existing seeded accounts. Password for every one of
--      them is:  Password123
--   2) Inserts one 'Mechanic' role test account, linked to Mechanic_ID
--      1 (Nguyen Van Hung, Workshop_ID 1 -- the first row inserted by
--      DML/mechanic_data.sql, so this assumes a fresh import with
--      auto-increment starting at 1).
--
-- Login table after this migration:
--   head.manager        / Password123   (Head Manager, company-wide, read-only)
--   depot.manager.hn    / Password123   (Depot Manager, Depot 1)
--   depot.manager.dn    / Password123   (Depot Manager, Depot 2)
--   depot.manager.hcm   / Password123   (Depot Manager, Depot 3)
--   depot.manager.ct    / Password123   (Depot Manager, Depot 4)
--   workshop.manager.hn / Password123   (Workshop Manager, Depot 1)
--   driver.manager.dn   / Password123   (Driver Manager, Depot 2)
--   inventory.manager   / Password123   (Inventory Manager, company-wide)
--   mechanic.test       / mechanic123   (Mechanic, Workshop 1)
--   driver.test         / driver123     (Driver -- from migration 003)
--
-- Change these before handing the project in / deploying anywhere real.
-- =====================================================================

UPDATE Staff SET Password_Hash = '$2b$12$zaoWEliUX61UisITW6e2jOIQLR1MoU7Qip30836ufCMx8y4Wl94WW'
WHERE Username IN (
    'head.manager',
    'depot.manager.hn',
    'depot.manager.dn',
    'depot.manager.hcm',
    'depot.manager.ct',
    'workshop.manager.hn',
    'driver.manager.dn',
    'inventory.manager'
);

INSERT INTO Staff (Staff_ID, Full_Name, Role_Type, Depot_ID, Contact_Info, Username, Password_Hash)
VALUES (
    'S-902',
    'Nguyen Van Hung',
    'Mechanic',
    1,
    'mechanic.test@fleetops.com',
    'mechanic.test',
    '$2b$12$bXYoJj6fM6rHvF/GlNwN4.WY81E3Ft.WJderSI7Nvu3PTseMLeg/e'
);
