CREATE TABLE Safety_Event (
    Event_ID INT PRIMARY KEY,
    Driver_ID VARCHAR(10),
    VIN VARCHAR(17),
    Depot_ID INT,
    Timestamp DATETIME,
    Event_Type VARCHAR(50),
    Severity_Level VARCHAR(20),
    Odometer_At_Event DECIMAL(10,2),
    Review_Comments TEXT
);
