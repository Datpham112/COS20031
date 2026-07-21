SELECT
    d.Driver_ID,
    d.Full_Name,
    v.VIN,
    v.Vehicle_Category,
    dc.Certification_Name,
    dc.Expiry_Date
FROM Vehicle_Driver_Assignment a
JOIN Driver d
    ON a.Driver_ID = d.Driver_ID
JOIN Vehicle v
    ON a.VIN = v.VIN
JOIN Driver_Certification dc
    ON d.Driver_ID = dc.Driver_ID
WHERE
    a.End_Date IS NULL
    AND dc.Expiry_Date < CURDATE();