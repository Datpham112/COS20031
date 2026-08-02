CREATE VIEW vw_High_Risk_Drivers AS
SELECT
    d.Driver_ID,
    d.Full_Name,
    dep.Location_Name,
    ds.Month,
    ds.Year,
    ds.Score
FROM Driver_Safety_Score ds
JOIN Driver d
    ON ds.Driver_ID=d.Driver_ID
JOIN Depot dep
    ON d.Depot_ID=dep.Depot_ID
WHERE ds.Score<=50;

SELECT * FROM vw_High_Risk_Drivers;