CREATE TABLE Predictive_Alert (
    Alert_ID INT AUTO_INCREMENT PRIMARY KEY,
    VIN VARCHAR(17) NOT NULL,
    Depot_ID INT NOT NULL,
    Alert_Type VARCHAR(50) NOT NULL,
    Action_Taken VARCHAR(30) NOT NULL,

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
                'Engine Fault',
                'Tyre Pressure'
            )
        ),
 
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
