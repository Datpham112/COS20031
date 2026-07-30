-- =====================================================================
-- Optional seed: a test "Driver" role login account
-- =====================================================================
-- Your sample data (Staff_data.sql) has no Role_Type = 'Driver' row at
-- all -- only 'Driver Manager'. Run this AFTER migration 002 (needs
-- the Linked_Driver_ID column) to create one test Driver account so
-- you can log in and see Frontend/my_profile.html working.
--
-- Login:  driver.test / driver123
-- Linked to existing Driver_ID 'D-112' (Nguyen Van An, Depot 1) from
-- your sample data.
-- =====================================================================

INSERT INTO Staff (Staff_ID, Full_Name, Role_Type, Depot_ID, Linked_Driver_ID, Contact_Info, Username, Password_Hash)
VALUES (
    'S-901',
    'Nguyen Van An',
    'Driver',
    1,
    'D-112',
    'driver.test@fleetops.com',
    'driver.test',
    '$2b$12$wbGxHf1UKT/Yhi4APTz9H.bF.UpLugJNxbdVTSD0JznL4s8HCSC9O'
);
