SELECT
    me.Mechanic_ID,
    me.Workshop_ID,
    me.Full_Name,
    COUNT(DISTINCT ma.Job_ID) AS Total_Jobs_Handled,
    SUM(ama.Labour_Hours) AS Total_Labour_Hours
FROM Mechanic me
LEFT JOIN Activity_Mechanic_Assignment ama ON me.Mechanic_ID = ama.Mechanic_ID
LEFT JOIN Maintenance_Activity ma ON ama.Activity_ID = ma.Activity_ID
GROUP BY me.Mechanic_ID, me.Workshop_ID, me.Full_Name
ORDER BY Total_Jobs_Handled DESC, Total_Labour_Hours DESC;

SELECT
    me.Mechanic_ID,
    me.Workshop_ID,
    me.Full_Name
FROM Mechanic me
ORDER BY me.Workshop_ID, me.Full_Name;