SELECT
    d.Driver_ID,
    d.Full_Name,
    dep.Location_Name,
    MONTH(se.Timestamp) AS Month,
    YEAR(se.Timestamp) AS Year,
    GREATEST(0, 100 - SUM(ep.Penalty_Points)) AS Safety_Score
FROM Safety_Event se
JOIN Driver d
    ON se.Driver_ID = d.Driver_ID
JOIN Depot dep
    ON d.Depot_ID = dep.Depot_ID
JOIN Event_Penalty ep
    ON se.Event_Type = ep.Event_Type
GROUP BY
    d.Driver_ID,
    d.Full_Name,
    dep.Location_Name,
    YEAR(se.Timestamp),
    MONTH(se.Timestamp)
ORDER BY
    Year,
    Month,
    Safety_Score DESC;