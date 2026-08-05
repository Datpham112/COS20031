
CREATE TABLE Audit_Log (
    Log_ID INT AUTO_INCREMENT PRIMARY KEY,
    Staff_ID VARCHAR(10) NOT NULL,
    Table_Name VARCHAR(50) NOT NULL,
    Action_Type VARCHAR(10) NOT NULL,
    Record_Summary VARCHAR(255) NULL,
    Created_At DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_audit_staff
        FOREIGN KEY (Staff_ID)
        REFERENCES Staff(Staff_ID),

    CONSTRAINT chk_audit_action
        CHECK (Action_Type IN ('CREATE', 'UPDATE', 'DELETE'))
);

ALTER TABLE Staff
    ADD COLUMN Linked_Driver_ID VARCHAR(10) NULL AFTER Role_Type;

ALTER TABLE Staff
    ADD CONSTRAINT fk_staff_driver
        FOREIGN KEY (Linked_Driver_ID)
        REFERENCES Driver(Driver_ID);

ALTER TABLE Staff
    ADD COLUMN Linked_Mechanic_ID INT NULL AFTER Linked_Driver_ID;

ALTER TABLE Staff
    ADD CONSTRAINT fk_staff_mechanic
        FOREIGN KEY (Linked_Mechanic_ID)
        REFERENCES Mechanic(Mechanic_ID);
