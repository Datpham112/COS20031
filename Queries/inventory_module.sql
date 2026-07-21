
-- 1: VIEWS 

-- -> [Part], [Supplier], [Part_Supplier]
CREATE VIEW vw_Part_Supplier_Details AS
SELECT 
    p.Part_ID, 
    p.Part_Name, 
    p.Part_Category, 
    p.Reorder_Level,
    s.Supplier_Name, 
    s.Contact_Name,
    s.Phone_Number,
    ps.Supplier_Type, 
    ps.Unit_Cost AS Supply_Cost, 
    ps.Lead_Time_Days
FROM Part p
JOIN Part_Supplier ps ON p.Part_ID = ps.Part_ID
JOIN Supplier s ON ps.Supplier_ID = s.Supplier_ID;

-- -> [Warranty_Claim], [Part], [Activity_Part]
CREATE VIEW vw_Warranty_Claim_Status AS
SELECT 
    wc.Claim_ID, 
    wc.Claim_Status, 
    wc.Claim_Date, 
    wc.Claim_Type,
    p.Part_Name, 
    p.Brand,
    ap.Quantity_Used,
    ap.Total_Cost AS Part_Total_Cost
FROM Warranty_Claims wc
JOIN Part p ON wc.Part_ID = p.Part_ID
JOIN Activity_Part ap ON wc.Activity_ID = ap.Activity_ID AND wc.Part_ID = ap.Part_ID;


-- 2: SQL QUERIES 

-- -> [Activity_Part], [Part]
SELECT 
    p.Part_Name, 
    p.Part_Category,
    SUM(ap.Quantity_Used) AS Total_Quantity_Used,
    SUM(ap.Total_Cost) AS Total_Cost_Incurred
FROM Activity_Part ap
JOIN Part p ON ap.Part_ID = p.Part_ID
GROUP BY p.Part_ID, p.Part_Name, p.Part_Category
ORDER BY Total_Cost_Incurred DESC;

-- -> [Predictive_Alert], [Part]
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


-- 3: STORED PROCEDURES 

DELIMITER //

-- -> [Warranty_Claim]
CREATE PROCEDURE sp_Register_Warranty_Claim(
    IN p_Activity_ID INT,
    IN p_Part_ID INT,
    IN p_Claim_Type VARCHAR(50)
)
BEGIN
    INSERT INTO Warranty_Claims (
        Activity_ID, 
        Part_ID, 
        Claim_Status, 
        Claim_Date, 
        Claim_Type
    )
    VALUES (
        p_Activity_ID, 
        p_Part_ID, 
        'Pending', 
        CURDATE(), 
        p_Claim_Type
    );
END //

-- -> [Part], [Supplier], [Part_Supplier]
CREATE PROCEDURE sp_Get_Optimal_Supplier(
    IN p_Part_Name_Keyword VARCHAR(100)
)
BEGIN
    SELECT 
        p.Part_Name,
        s.Supplier_Name,
        s.Phone_Number,
        ps.Supplier_Type,
        ps.Unit_Cost,
        ps.Lead_Time_Days
    FROM Part p
    JOIN Part_Supplier ps ON p.Part_ID = ps.Part_ID
    JOIN Supplier s ON ps.Supplier_ID = s.Supplier_ID
    WHERE p.Part_Name LIKE CONCAT('%', p_Part_Name_Keyword, '%')
    ORDER BY ps.Lead_Time_Days ASC, ps.Unit_Cost ASC;
END //

DELIMITER ;


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


-- 5: SCRIPTS TEST
/*
-- 1. Test Views
SELECT * FROM vw_Part_Supplier_Details;
SELECT * FROM vw_Warranty_Claim_Status;

-- 2. Test Procedure find supplier
CALL sp_Get_Optimal_Supplier('Brake');

-- 3. Test Procedure create a manual claim
CALL sp_Register_Warranty_Claim(3, 3, 'Manufacturer Defect');
SELECT * FROM Warranty_Claims;

-- 4. Test Trigger automatic
INSERT INTO Activity_Part (Activity_ID, Part_ID, Quantity_Used, Unit_Cost, Total_Cost)
VALUES (3, 6, 1, 75000.00, 75000.00);
SELECT * FROM Warranty_Claims;
*/
