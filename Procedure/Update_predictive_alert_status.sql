DELIMITER $$

CREATE PROCEDURE Update_Predictive_Alert_Status
(
    IN p_alert_id INT,
    IN p_action VARCHAR(100)
)
BEGIN

UPDATE Predictive_Alert
SET Action_Taken = p_action
WHERE Alert_ID = p_alert_id;
END$$

DELIMITER ;

CALL Update_Predictive_Alert_Status
(1,'Schedule Inspection');