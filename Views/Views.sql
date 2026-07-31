-- 1. HEAD MANAGER VIEWS
CREATE VIEW vw_HeadManager_DepotComparison AS
SELECT 
    d.Depot_ID,
    d.Location_Name,
    (SELECT ROUND(AVG(dss.Score), 2) 
     FROM Driver dr JOIN Driver_Safety_Score dss ON dr.Driver_ID = dss.Driver_ID 
     WHERE dr.Depot_ID = d.Depot_ID) AS Avg_Safety_Score,
    (SELECT SUM(mj.Total_Cost) 
     FROM Workshop w JOIN Maintenance_Job mj ON w.Workshop_ID = mj.Workshop_ID 
     WHERE w.Depot_ID = d.Depot_ID) AS Total_Maintenance_Cost
FROM Depot d;

-- 2. DEPOT MANAGER VIEWS
CREATE VIEW vw_DepotManager_ActivitySummary AS
SELECT 
    d.Depot_ID,
    d.Location_Name,
    COUNT(DISTINCT dr.Driver_ID) AS Total_Drivers,
    COUNT(DISTINCT v.VIN) AS Total_Vehicles,
    COUNT(DISTINCT mj.Job_ID) AS Open_Maintenance_Jobs,
    COUNT(DISTINCT se.Event_ID) AS Total_Safety_Events
FROM Depot d
LEFT JOIN Driver dr ON d.Depot_ID = dr.Depot_ID
LEFT JOIN Vehicle v ON d.Depot_ID = v.Depot_ID
LEFT JOIN Workshop w ON d.Depot_ID = w.Depot_ID
LEFT JOIN Maintenance_Job mj ON w.Workshop_ID = mj.Workshop_ID AND mj.Date_Closed IS NULL
LEFT JOIN Safety_Event se ON d.Depot_ID = se.Depot_ID
GROUP BY d.Depot_ID, d.Location_Name;


-- 3. WORKSHOP MANAGER VIEWS
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

-- 4. DRIVER MANAGER VIEWS
CREATE VIEW vw_DriverManager_ExpiringLicences AS
SELECT Driver_ID, Full_Name, License_Type, License_Expiry_Date, Employment_Status
FROM Driver
WHERE License_Expiry_Date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY);

CREATE VIEW vw_DriverManager_LockedDrivers AS
SELECT d.Driver_ID, d.Full_Name, d.Employment_Status, ds.Month, ds.Year, ds.Score
FROM Driver d
JOIN Driver_Safety_Score ds ON d.Driver_ID = ds.Driver_ID
WHERE ds.Score <= 50 OR d.Employment_Status = 'Suspended';


-- 5. INVENTORY MANAGER VIEWS
CREATE VIEW vw_InventoryManager_PartsToReorder AS
SELECT Part_ID, Part_Name, Part_Category, Reorder_Level, Unit_Price
FROM Part
ORDER BY Reorder_Level DESC;

CREATE VIEW vw_InventoryManager_PendingWarranty AS
SELECT Claim_ID, Activity_ID, Part_ID, Claim_Date, Claim_Type
FROM Warranty_Claims
WHERE Claim_Status = 'Pending';