-- =====================================================================
-- Migration 002: Audit_Log table + link Staff accounts to Driver records
-- =====================================================================
-- Run this AFTER migration 001 (Predictive_Alert severity/timestamp).
-- Safe to run once; re-running will error on "already exists", which
-- just means it already applied.
--
-- NOTE: this file lives directly in Backend/ (not Backend/migrations/)
-- because in your zip, Backend/migrations came through as a single
-- file rather than a folder -- so this avoids colliding with it.
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1) Audit_Log: one row per Create/Update/Delete made through the web
--    app, so managers can see who entered what, and when.
-- ---------------------------------------------------------------------
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

-- ---------------------------------------------------------------------
-- 2) Link a Staff login account (Role_Type = 'Driver') to its matching
--    row in the Driver table, so a driver who logs in can be shown
--    their own safety violations.
-- ---------------------------------------------------------------------
ALTER TABLE Staff
    ADD COLUMN Linked_Driver_ID VARCHAR(10) NULL AFTER Role_Type;

ALTER TABLE Staff
    ADD CONSTRAINT fk_staff_driver
        FOREIGN KEY (Linked_Driver_ID)
        REFERENCES Driver(Driver_ID);

-- ---------------------------------------------------------------------
-- 3) Same idea for Role_Type = 'Mechanic' -> Mechanic table, so a
--    mechanic who logs in only sees jobs assigned to them.
-- ---------------------------------------------------------------------
ALTER TABLE Staff
    ADD COLUMN Linked_Mechanic_ID INT NULL AFTER Linked_Driver_ID;

ALTER TABLE Staff
    ADD CONSTRAINT fk_staff_mechanic
        FOREIGN KEY (Linked_Mechanic_ID)
        REFERENCES Mechanic(Mechanic_ID);
