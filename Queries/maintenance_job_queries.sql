SELECT
    mj.Job_ID,
    mj.VIN,
    mj.Workshop_ID,
    mj.Linked_Alert_ID,
    mj.Date_Opened,
    mj.Date_Closed,
    mj.Downtime_Hours,
    mj.Total_Cost,
    CASE
        WHEN mj.Date_Closed IS NULL THEN 'Open'
        ELSE 'Closed'
    END AS Job_Status
FROM Maintenance_Job mj
ORDER BY mj.Date_Opened DESC;

SELECT
    mj.Job_ID,
    mj.VIN,
    mj.Workshop_ID,
    mj.Date_Opened,
    mj.Date_Closed,
    mj.Downtime_Hours,
    mj.Total_Cost
FROM Maintenance_Job mj
WHERE mj.VIN = 'VIN00000000000001'
ORDER BY mj.Date_Opened DESC;

SELECT
    mj.Job_ID,
    mj.VIN,
    mj.Workshop_ID,
    mj.Date_Opened,
    mj.Linked_Alert_ID
FROM Maintenance_Job mj
WHERE mj.Date_Closed IS NULL
ORDER BY mj.Date_Opened ASC;