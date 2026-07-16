CREATE TABLE Safety_Event (
    Event_ID INT AUTO_INCREMENT PRIMARY KEY,
    Driver_ID VARCHAR(10) NOT NULL,
    VIN VARCHAR(17) NOT NULL,
    Depot_ID INT NOT NULL,
    Timestamp DATETIME NOT NULL,
    Event_Type VARCHAR(50) NOT NULL,
    Severity_Level VARCHAR(20) NOT NULL,
    Odometer_At_Event DECIMAL(10,2) NOT NULL,
    Review_Comments TEXT,

    CONSTRAINT fk_event_driver
        FOREIGN KEY (Driver_ID)
        REFERENCES Driver(Driver_ID),

    CONSTRAINT fk_event_vehicle
        FOREIGN KEY (VIN)
        REFERENCES Vehicle(VIN),

    CONSTRAINT fk_event_depot
        FOREIGN KEY (Depot_ID)
        REFERENCES Depot(Depot_ID),

    CONSTRAINT chk_severity
        CHECK (Severity_Level IN ('Low','Medium','High','Critical'))
);
