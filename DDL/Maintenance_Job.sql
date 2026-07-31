CREATE TABLE Maintenance_Job (
    Job_ID INT AUTO_INCREMENT PRIMARY KEY,
    VIN VARCHAR(17) NOT NULL,
    Workshop_ID INT NOT NULL,
    Linked_Alert_ID INT,
    Date_Opened DATE NOT NULL,
    Date_Closed DATE,
    Downtime_Hours DECIMAL(6,2),
    Total_Cost DECIMAL(10,2),
 
    CONSTRAINT fk_job_vehicle
        FOREIGN KEY (VIN)
        REFERENCES Vehicle(VIN),
 
    CONSTRAINT fk_job_workshop
        FOREIGN KEY (Workshop_ID)
        REFERENCES Workshop(Workshop_ID),
 
    CONSTRAINT fk_job_alert
        FOREIGN KEY (Linked_Alert_ID)
        REFERENCES Predictive_Alert(Alert_ID),
 
    CONSTRAINT chk_downtime
        CHECK (Downtime_Hours >= 0),
 
    CONSTRAINT chk_total_cost
        CHECK (Total_Cost >= 0),
 
    CONSTRAINT chk_job_dates
        CHECK (Date_Closed IS NULL OR Date_Closed >= Date_Opened)
);
