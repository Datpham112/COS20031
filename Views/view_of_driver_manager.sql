CREATE VIEW vw_DriverManager_ExpiringLicences AS
SELECT Driver_ID, Full_Name, License_Type, License_Expiry_Date, Employment_Status
FROM Driver
WHERE License_Expiry_Date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY);

CREATE VIEW vw_DriverManager_LockedDrivers AS
SELECT d.Driver_ID, d.Full_Name, d.Employment_Status, ds.Month, ds.Year, ds.Score
FROM Driver d
JOIN Driver_Safety_Score ds ON d.Driver_ID = ds.Driver_ID
WHERE ds.Score <= 50 OR d.Employment_Status = 'Suspended';