START TRANSACTION;

SELECT Operational_Status
FROM Vehicle
WHERE VIN = 'VIN00000000000001'
FOR UPDATE;

INSERT INTO Vehicle_Driver_Assignment
(
    Driver_ID,
    VIN,
    Start_Date,
    End_Date
)
SELECT
    'D-101',
    'VIN00000000000001',
    NOW(),
    NULL
FROM Vehicle
WHERE VIN='VIN00000000000001'
AND Operational_Status IN ('Active','Available');

COMMIT;