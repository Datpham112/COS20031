SELECT
    dep.Location_Name,
    d.Driver_ID,
    d.Full_Name,
    v.VIN,
    v.Vehicle_Category
FROM Vehicle_Driver_Assignment a
JOIN Driver d
    ON a.Driver_ID = d.Driver_ID
JOIN Vehicle v
    ON a.VIN = v.VIN
JOIN Depot dep
    ON d.Depot_ID = dep.Depot_ID
WHERE a.End_Date IS NULL;