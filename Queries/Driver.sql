SELECT 
    Event_ID,
    Timestamp, 
    Event_Type, 
    Severity_Level, 
    Review_Comments
FROM Safety_Event
WHERE Driver_ID = ?
ORDER BY Timestamp DESC;

SELECT 
    Month, 
    Year, 
    Score
FROM Driver_Safety_Score
WHERE Driver_ID = ?
ORDER BY Year DESC, Month DESC;