SELECT
    mch.Mechanic_ID,
    m.Full_Name,
    mch.Cert_ID,
    mch.Certificate_Name,
    mch.issue_Date,
    mch.Expiry_Date
FROM Mechanic_Cert_History mch
JOIN Mechanic m ON mch.Mechanic_ID = m.Mechanic_ID
ORDER BY mch.Mechanic_ID, mch.Expiry_Date DESC;

SELECT
    mch.Mechanic_ID,
    m.Full_Name,
    COUNT(mch.Cert_ID) AS Total_Cert_History_Records
FROM Mechanic_Cert_History mch
JOIN Mechanic m ON mch.Mechanic_ID = m.Mechanic_ID
GROUP BY mch.Mechanic_ID, m.Full_Name
ORDER BY Total_Cert_History_Records DESC, m.Full_Name;