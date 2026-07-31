SELECT
    se.Event_ID,
    d.Driver_ID,
    d.Full_Name,
    dep.Location_Name,
    v.VIN,
    se.Event_Type,
    se.Severity_Level,
    se.Timestamp,
    se.Review_Comments
FROM Safety_Event se
JOIN Driver d
    ON se.Driver_ID=d.Driver_ID
JOIN Vehicle v
    ON se.VIN=v.VIN
JOIN Depot dep
    ON se.Depot_ID=dep.Depot_ID
WHERE se.Severity_Level IN ('High','Critical')
ORDER BY se.Timestamp DESC;