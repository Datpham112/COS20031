CREATE TABLE Driver (
    Driver_ID INT AUTO_INCREMENT PRIMARY KEY,
    Depot_ID INT NOT NULL,
    Full_Name VARCHAR(100) NOT NULL,
    Contact_Information VARCHAR(100) NOT NULL,
    Emergency_Contact VARCHAR(100) NOT NULL,
    License_Type VARCHAR(50) NOT NULL,
    License_Expiry_Date DATE NOT NULL,
    Employment_Status VARCHAR(20) NOT NULL,

    CONSTRAINT fk_driver_depot
        FOREIGN KEY (Depot_ID)
        REFERENCES Depot(Depot_ID),
	
	CONSTRAINT chk_employment_status
        CHECK (Employment_Status IN ('Active','On Leave','Suspended','Terminated'))
);