CREATE VIEW vw_DepotManager_ActivitySummary AS
SELECT 
    d.Depot_ID,
    d.Location_Name,
    COUNT(DISTINCT dr.Driver_ID) AS Total_Drivers,
    COUNT(DISTINCT v.VIN) AS Total_Vehicles,
    COUNT(DISTINCT mj.Job_ID) AS Open_Maintenance_Jobs,
    COUNT(DISTINCT se.Event_ID) AS Total_Safety_Events
FROM Depot d
LEFT JOIN Driver dr ON d.Depot_ID = dr.Depot_ID
LEFT JOIN Vehicle v ON d.Depot_ID = v.Depot_ID
LEFT JOIN Workshop w ON d.Depot_ID = w.Depot_ID
LEFT JOIN Maintenance_Job mj ON w.Workshop_ID = mj.Workshop_ID AND mj.Date_Closed IS NULL
LEFT JOIN Safety_Event se ON d.Depot_ID = se.Depot_ID
GROUP BY d.Depot_ID, d.Location_Name;