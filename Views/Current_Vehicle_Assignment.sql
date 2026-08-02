CREATE VIEW View_Current_Vehicle_Assignment AS

SELECT
v.Registration_Number,
d.Full_Name,
dep.Location_Name,
a.Start_Date

FROM Vehicle_Driver_Assignment a

JOIN Vehicle v
ON a.VIN=v.VIN

JOIN Driver d
ON a.Driver_ID=d.Driver_ID

JOIN Depot dep
ON v.Depot_ID=dep.Depot_ID

WHERE a.End_Date IS NULL;