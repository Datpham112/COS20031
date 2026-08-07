DROP PROCEDURE IF EXISTS Update_Predictive_Alert_Status;

DELIMITER $$

CREATE PROCEDURE Update_Predictive_Alert_Status
(
    IN p_alert_id INT,
    IN p_action VARCHAR(100)
)
BEGIN
    IF p_action NOT IN ('Acknowledged', 'Scheduled Repair', 'Emergency Repair', 'Resolved') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Invalid Action_Taken value. Must be one of: Acknowledged, Scheduled Repair, Emergency Repair, Resolved.';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM Predictive_Alert WHERE Alert_ID = p_alert_id) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Alert_ID not found.';
    END IF;

    UPDATE Predictive_Alert
    SET Action_Taken = p_action
    WHERE Alert_ID = p_alert_id;
END$$

DELIMITER ;

CALL Update_Predictive_Alert_Status
(1, 'Scheduled Repair');
