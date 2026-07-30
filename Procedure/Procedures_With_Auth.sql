DELIMITER //
CREATE PROCEDURE sp_AssignDriverToVehicle(
    IN p_Staff_ID VARCHAR(20),    
    IN p_Driver_ID VARCHAR(10),
    IN p_VIN VARCHAR(17),
    IN p_Start_Date DATETIME
)
BEGIN
    DECLARE v_RoleType VARCHAR(50);
    DECLARE v_StaffDepot INT;
    DECLARE v_DriverDepot INT;

    SELECT Role_Type, Depot_ID INTO v_RoleType, v_StaffDepot
    FROM Staff WHERE Staff_ID = p_Staff_ID;

    SELECT Depot_ID INTO v_DriverDepot FROM Driver WHERE Driver_ID = p_Driver_ID;

    IF (v_RoleType = 'Head Manager') OR (v_RoleType = 'Driver Manager' AND v_StaffDepot = v_DriverDepot) THEN
        INSERT INTO Vehicle_Driver_Assignment (Driver_ID, VIN, Start_Date)
        VALUES (p_Driver_ID, p_VIN, p_Start_Date);
        SELECT 'Success: Driver assigned to vehicle.' AS Result;
    ELSE
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Permission Denied: You must be a Driver Manager of this Depot or Head Manager.';
    END IF;
END //

CREATE PROCEDURE sp_ApproveWarrantyClaim(
    IN p_Staff_ID VARCHAR(20),
    IN p_Claim_ID INT
)
BEGIN
    DECLARE v_RoleType VARCHAR(50);

    -- 1. Lấy Role của nhân viên
    SELECT Role_Type INTO v_RoleType FROM Staff WHERE Staff_ID = p_Staff_ID;

    -- 2. Kiểm tra quyền: Chỉ Inventory Manager hoặc Head Manager mới được duyệt
    IF (v_RoleType = 'Inventory Manager' OR v_RoleType = 'Head Manager') THEN
        UPDATE Warranty_Claims
        SET Claim_Status = 'Approved'
        WHERE Claim_ID = p_Claim_ID;
        SELECT CONCAT('Success: Warranty Claim ', p_Claim_ID, ' has been approved.') AS Result;
    ELSE
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Permission Denied: Only Inventory Manager can approve warranty claims.';
    END IF;
END //

CREATE PROCEDURE sp_ReorderPart(
    IN p_Staff_ID VARCHAR(20)
)
BEGIN
    DECLARE v_RoleType VARCHAR(50);
    
    SELECT Role_Type INTO v_RoleType FROM Staff WHERE Staff_ID = p_Staff_ID;

    IF (v_RoleType = 'Inventory Manager' OR v_RoleType = 'Head Manager') THEN
        SELECT 
            Part_ID, 
            Part_Name, 
            Part_Category, 
            Reorder_Level
        FROM Part
        ORDER BY Reorder_Level DESC;
    ELSE
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Permission Denied: Unauthorized access to inventory data.';
    END IF;
END //

DELIMITER ;
