CREATE TABLE Vehicle_Driver_Assignment (
    Assignment_ID INT AUTO_INCREMENT PRIMARY KEY,
    Driver_ID INT NOT NULL,
    VIN VARCHAR(17) NOT NULL,
    Start_Date DATETIME NOT NULL,
    End_Date DATETIME,

    CONSTRAINT fk_assignment_driver
        FOREIGN KEY (Driver_ID)
        REFERENCES Driver(Driver_ID),

    CONSTRAINT fk_assignment_vehicle
        FOREIGN KEY (VIN)
        REFERENCES Vehicle(VIN),

    CONSTRAINT chk_assignment_dates
        CHECK (End_Date IS NULL OR End_Date >= Start_Date)
);