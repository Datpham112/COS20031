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
