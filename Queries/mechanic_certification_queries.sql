SELECT
    mc.Mechanic_ID,
    m.Full_Name,
    mc.Certification_Name,
    mc.Issue_Date,
    mc.Expiry_Date,
    CASE
        WHEN mc.Expiry_Date < CURDATE() THEN 'Expired'
        WHEN mc.Expiry_Date <= DATE_ADD(CURDATE(), INTERVAL 90 DAY) THEN 'Expiring Soon'
        ELSE 'Valid'
    END AS Cert_Status
FROM Mechanic_Certification mc
JOIN Mechanic m ON mc.Mechanic_ID = m.Mechanic_ID
ORDER BY mc.Expiry_Date ASC, m.Full_Name;

SELECT
    mc.Mechanic_ID,
    m.Full_Name,
    mc.Certification_Name,
    mc.Expiry_Date
FROM Mechanic_Certification mc
JOIN Mechanic m ON mc.Mechanic_ID = m.Mechanic_ID
WHERE mc.Expiry_Date < CURDATE()
   OR mc.Expiry_Date <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)
ORDER BY mc.Expiry_Date ASC, m.Full_Name;