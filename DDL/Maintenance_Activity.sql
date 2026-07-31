CREATE TABLE Maintenance_Activity (
    Activity_ID INT AUTO_INCREMENT PRIMARY KEY,
    Job_ID INT NOT NULL,
    Activity_Type VARCHAR(100) NOT NULL,
    Diagnostic_Result TEXT,
    Repeat_Fault_Indicator BOOLEAN DEFAULT FALSE,
    Warranty_Indicator BOOLEAN DEFAULT FALSE,
 
    CONSTRAINT fk_activity_job
        FOREIGN KEY (Job_ID)
        REFERENCES Maintenance_Job(Job_ID),
 
    CONSTRAINT chk_activity_type
        CHECK (
            Activity_Type IN (
                'Brake Inspection',
                'Tyre Replacement',
                'Battery Replacement',
                'Oil Change',
                'Cooling System Repair',
                'Electrical Repair',
                'General Inspection'
            )
        )
);
