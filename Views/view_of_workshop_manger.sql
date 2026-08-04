CREATE VIEW vw_WorkshopManager_OpenJobs AS
SELECT Job_ID, VIN, Date_Opened, Priority, Downtime_Hours
FROM Maintenance_Job
WHERE Date_Closed IS NULL;

CREATE VIEW vw_WorkshopManager_UnresolvedAlerts AS
SELECT Alert_ID, VIN, Depot_ID, Alert_Type, Action_Taken
FROM Predictive_Alert
WHERE Action_Taken != 'Resolved';

CREATE VIEW vw_WorkshopManager_MechanicWorkload AS
SELECT 
    m.Mechanic_ID, 
    m.Full_Name, 
    SUM(ama.Labour_Hours) AS Total_Hours_Assigned,
    COUNT(ama.Activity_ID) AS Total_Activities
FROM Mechanic m
LEFT JOIN Activity_Mechanic_Assignment ama ON m.Mechanic_ID = ama.Mechanic_ID
GROUP BY m.Mechanic_ID, m.Full_Name;