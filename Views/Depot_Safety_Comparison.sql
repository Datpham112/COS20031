CREATE VIEW vw_Depot_Safety_Comparison AS
SELECT
    dep.Depot_ID,
    dep.Location_Name,
    COUNT(DISTINCT d.Driver_ID) AS Total_Drivers,
    COUNT(se.Event_ID) AS Total_Incidents,
    ROUND(AVG(ds.Score),2) AS Average_Safety_Score
FROM Depot dep
LEFT JOIN Driver d
    ON dep.Depot_ID=d.Depot_ID
LEFT JOIN Driver_Safety_Score ds
    ON d.Driver_ID=ds.Driver_ID
LEFT JOIN Safety_Event se
    ON d.Driver_ID=se.Driver_ID
GROUP BY
    dep.Depot_ID,
    dep.Location_Name;
    
SELECT * FROM vw_Depot_Safety_Comparison;