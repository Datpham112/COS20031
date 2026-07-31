SELECT
    d.Driver_ID,
    d.Full_Name,
    dc.Certification_Name,
    dc.Expiry_Date
FROM Driver d
JOIN Driver_Certification dc
    ON d.Driver_ID = dc.Driver_ID
WHERE dc.Expiry_Date < CURDATE()
ORDER BY dc.Expiry_Date;