CREATE TABLE Predictive_Alert (
    Alert_ID INT AUTO_INCREMENT PRIMARY KEY,
    VIN VARCHAR(17) NOT NULL,
    Depot_ID INT NOT NULL,
    Alert_Type VARCHAR(50) NOT NULL,
    Severity_Level VARCHAR(20) NOT NULL DEFAULT 'Medium',
    Action_Taken VARCHAR(30) NOT NULL,
    Created_Date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    Raised_At DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

     CONSTRAINT fk_alert_vehicle
        FOREIGN KEY (VIN)
        REFERENCES Vehicle(VIN),
 
    CONSTRAINT fk_alert_depot
        FOREIGN KEY (Depot_ID)
        REFERENCES Depot(Depot_ID),
 
    CONSTRAINT chk_alert_type
        CHECK (
            Alert_Type IN (
                'Brake Wear',
                'Overheating Risk',
                'Battery Degradation',
                'Oil Quality Deterioration',
                'Transmission Fault',
                'Cooling System Anomaly',
                'Tyre Pressure'
            )
        ),

    CONSTRAINT chk_alert_severity
        CHECK (Severity_Level IN ('Low','Medium','High','Critical')),
  
    CONSTRAINT chk_action_taken
        CHECK (
            Action_Taken IN (
                'Acknowledged',
                'Scheduled Repair',
                'Emergency Repair',
                'Resolved'
            )
        )
);
