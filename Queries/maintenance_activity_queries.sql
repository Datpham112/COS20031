SELECT
    ma.Activity_ID,
    ma.Job_ID,
    mj.VIN,
    mj.Workshop_ID,
    ma.Activity_Type,
    ma.Diagnostic_Result,
    ma.Repeat_Fault_Indicator,
    ma.Warranty_Indicator
FROM Maintenance_Activity ma
JOIN Maintenance_Job mj ON ma.Job_ID = mj.Job_ID
ORDER BY ma.Activity_ID DESC;

SELECT
    ma.Activity_ID,
    ma.Job_ID,
    ma.Activity_Type,
    me.Mechanic_ID,
    me.Full_Name,
    ama.Labour_Hours
FROM Maintenance_Activity ma
JOIN Activity_Mechanic_Assignment ama ON ma.Activity_ID = ama.Activity_ID
JOIN Mechanic me ON ama.Mechanic_ID = me.Mechanic_ID
ORDER BY ma.Activity_ID, me.Full_Name;