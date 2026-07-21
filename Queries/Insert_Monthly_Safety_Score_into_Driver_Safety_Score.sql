INSERT INTO Driver_Safety_Score
(
    Driver_ID,
    Month,
    Year,
    Score
)
SELECT
    d.Driver_ID,
    MONTH(se.Timestamp),
    YEAR(se.Timestamp),
    GREATEST(0,100-SUM(ep.Penalty_Points))
FROM Safety_Event se
JOIN Driver d
    ON se.Driver_ID=d.Driver_ID
JOIN Event_Penalty ep
    ON se.Event_Type=ep.Event_Type
GROUP BY
    d.Driver_ID,
    YEAR(se.Timestamp),
    MONTH(se.Timestamp);