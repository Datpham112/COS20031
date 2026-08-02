SELECT
    w.Workshop_ID,
    w.Depot_ID,
    COUNT(mj.Job_ID) AS Total_Jobs,
    SUM(COALESCE(mj.Total_Cost, 0)) AS Total_Maintenance_Cost,
    SUM(COALESCE(mj.Downtime_Hours, 0)) AS Total_Downtime_Hours
FROM Workshop w
LEFT JOIN Maintenance_Job mj ON w.Workshop_ID = mj.Workshop_ID
GROUP BY w.Workshop_ID, w.Depot_ID
ORDER BY Total_Maintenance_Cost DESC, Total_Jobs DESC;

SELECT
    w.Workshop_ID,
    mj.Job_ID,
    mj.VIN,
    mj.Date_Opened,
    mj.Date_Closed,
    CASE
        WHEN mj.Date_Closed IS NULL THEN 'Open'
        ELSE 'Closed'
    END AS Job_Status
FROM Workshop w
LEFT JOIN Maintenance_Job mj ON w.Workshop_ID = mj.Workshop_ID
ORDER BY w.Workshop_ID, mj.Date_Opened DESC;