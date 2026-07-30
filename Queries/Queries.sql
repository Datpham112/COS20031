-- 1. MECHANIC QUERIES 
SELECT 
    ma.Activity_ID, 
    ma.Job_ID, 
    ma.Activity_Type, 
    ma.Diagnostic_Result,
    ama.Labour_Hours
FROM Maintenance_Activity ma
JOIN Activity_Mechanic_Assignment ama ON ma.Activity_ID = ama.Activity_ID
WHERE ama.Mechanic_ID = ?; 

SELECT 
    mj.Job_ID, 
    mj.Date_Opened, 
    mj.Date_Closed, 
    ma.Activity_Type, 
    ma.Diagnostic_Result,
    ma.Repeat_Fault_Indicator
FROM Maintenance_Job mj
JOIN Maintenance_Activity ma ON mj.Job_ID = ma.Job_ID
WHERE mj.VIN = ?
ORDER BY mj.Date_Opened DESC;


-- 2. DRIVER QUERIES 
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