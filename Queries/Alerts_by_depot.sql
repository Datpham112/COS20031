SELECT
p.Alert_ID,
v.Registration_Number,
d.Location_Name,
p.Alert_Type,
p.Action_Taken
FROM Predictive_Alert p
JOIN Vehicle v
ON p.VIN=v.VIN
JOIN Depot d
ON p.Depot_ID=d.Depot_ID;