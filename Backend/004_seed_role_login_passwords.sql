
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
)
ON DUPLICATE KEY UPDATE
    Full_Name = VALUES(Full_Name),
    Role_Type = VALUES(Role_Type),
    Depot_ID = VALUES(Depot_ID),
    Contact_Info = VALUES(Contact_Info),
    Password_Hash = VALUES(Password_Hash);

INSERT INTO Staff (Staff_ID, Full_Name, Role_Type, Depot_ID, Contact_Info, Username, Password_Hash)
VALUES (
    'S-009',
    'System Administrator',
    'Head Manager',
    NULL,
    'admin@fleetops.com',
    'admin',
    '$2b$12$zaoWEliUX61UisITW6e2jOIQLR1MoU7Qip30836ufCMx8y4Wl94WW'
)
ON DUPLICATE KEY UPDATE
    Full_Name = VALUES(Full_Name),
    Role_Type = VALUES(Role_Type),
    Depot_ID = VALUES(Depot_ID),
    Contact_Info = VALUES(Contact_Info),
    Password_Hash = VALUES(Password_Hash);