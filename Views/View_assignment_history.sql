SELECT
    v.Registration_Number,
    d.Driver_ID,
    d.Full_Name,
    dep.Location_Name AS Depot,
    a.Start_Date,
    a.End_Date
FROM Vehicle_Driver_Assignment a
JOIN Driver d
ON a.Driver_ID=d.Driver_ID
JOIN Vehicle v
ON a.VIN=v.VIN
JOIN Depot dep
ON v.Depot_ID=dep.Depot_ID
ORDER BY
a.Start_Date DESC;