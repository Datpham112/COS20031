SELECT 
    p.Part_Name, 
    p.Part_Category,
    SUM(ap.Quantity_Used) AS Total_Quantity_Used,
    SUM(ap.Total_Cost) AS Total_Cost_Incurred
FROM Activity_Part ap
JOIN Part p ON ap.Part_ID = p.Part_ID
GROUP BY p.Part_ID, p.Part_Name, p.Part_Category
ORDER BY Total_Cost_Incurred DESC;

SELECT DISTINCT
    pa.VIN, 
    pa.Alert_Type, 
    pa.Action_Taken,
    p.Part_Name, 
    p.Part_Category,
    p.Reorder_Level
FROM Predictive_Alert pa
LEFT JOIN Part p ON 
    (pa.Alert_Type = 'Brake Wear' AND p.Part_Category = 'Brake System') OR
    (pa.Alert_Type = 'Battery Degradation' AND p.Part_Category = 'Battery') OR
    (pa.Alert_Type = 'Cooling System Anomaly' AND p.Part_Category = 'Cooling System')
WHERE pa.Action_Taken IN ('Scheduled Repair', 'Emergency Repair')
ORDER BY pa.VIN;

-- 4: TRIGGERS 

DELIMITER //

-- -> [Activity_Part], [Warranty_Claim]
CREATE TRIGGER trg_Auto_Create_Warranty_Claim
AFTER INSERT ON Activity_Part
FOR EACH ROW
BEGIN
    DECLARE is_warranty BOOLEAN;

    SELECT Warranty_Indicator INTO is_warranty
    FROM Maintenance_Activity
    WHERE Activity_ID = NEW.Activity_ID;

    IF is_warranty = TRUE THEN
        INSERT INTO Warranty_Claims (
            Activity_ID, 
            Part_ID, 
            Claim_Status, 
            Claim_Date, 
            Claim_Type
        )
        VALUES (
            NEW.Activity_ID, 
            NEW.Part_ID, 
            'Pending', 
            CURDATE(), 
            'Auto-Generated (System)'
        );
    END IF;
END //

DELIMITER ;



INSERT INTO Activity_Part (Activity_ID, Part_ID, Quantity_Used, Unit_Cost, Total_Cost)
VALUES (3, 6, 1, 75000.00, 75000.00);
SELECT * FROM Warranty_Claims;
*/
