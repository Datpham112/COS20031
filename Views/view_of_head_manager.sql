CREATE VIEW vw_HeadManager_DepotComparison AS
SELECT 
    d.Depot_ID,
    d.Location_Name,
    (SELECT ROUND(AVG(dss.Score), 2) 
     FROM Driver dr JOIN Driver_Safety_Score dss ON dr.Driver_ID = dss.Driver_ID 
     WHERE dr.Depot_ID = d.Depot_ID) AS Avg_Safety_Score,
    (SELECT SUM(mj.Total_Cost) 
     FROM Workshop w JOIN Maintenance_Job mj ON w.Workshop_ID = mj.Workshop_ID 
     WHERE w.Depot_ID = d.Depot_ID) AS Total_Maintenance_Cost
FROM Depot d;