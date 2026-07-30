SELECT
    ama.Activity_ID,
    ma.Job_ID,
    ma.Activity_Type,
    ama.Mechanic_ID,
    me.Full_Name,
    ama.Labour_Hours
FROM Activity_Mechanic_Assignment ama
JOIN Maintenance_Activity ma ON ama.Activity_ID = ma.Activity_ID
JOIN Mechanic me ON ama.Mechanic_ID = me.Mechanic_ID
ORDER BY ma.Job_ID, ama.Activity_ID, me.Full_Name;

SELECT
    ama.Mechanic_ID,
    me.Full_Name,
    COUNT(DISTINCT ama.Activity_ID) AS Total_Activities,
    SUM(ama.Labour_Hours) AS Total_Labour_Hours
FROM Activity_Mechanic_Assignment ama
JOIN Mechanic me ON ama.Mechanic_ID = me.Mechanic_ID
GROUP BY ama.Mechanic_ID, me.Full_Name
ORDER BY Total_Activities DESC, Total_Labour_Hours DESC;