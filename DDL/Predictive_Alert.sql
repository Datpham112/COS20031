CREATE TABLE Predictive_Alert (
    Alert_ID INT AUTO_INCREMENT PRIMARY KEY,
    VIN VARCHAR(17) NOT NULL,
    Depot_ID INT NOT NULL,
    Alert_Type VARCHAR(50) NOT NULL,
    Action_Taken VARCHAR(30) NOT NULL,

    FOREIGN KEY (VIN)
        REFERENCES Vehicle(VIN),

    FOREIGN KEY (Depot_ID)
        REFERENCES Depot(Depot_ID),

    CHECK (
        Alert_Type IN (
            'Brake Wear',
            'Overheating Risk',
            'Battery Degradation',
            'Engine Fault',
            'Tyre Pressure'
        )
    ),

    CHECK (
        Action_Taken IN (
            'Acknowledged',
            'Scheduled Repair',
            'Emergency Repair',
            'Resolved'
        )
    )
);
