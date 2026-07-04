CREATE TABLE Mechanic_Certification (
    Mechanic_ID INT NOT NULL,
    Certification_Name VARCHAR(100) NOT NULL,
    Issue_Date DATE NOT NULL,
    Expiry_Date DATE NOT NULL,

    PRIMARY KEY (Mechanic_ID, Certification_Name),

    CONSTRAINT fk_mechanic_certification
        FOREIGN KEY (Mechanic_ID)
        REFERENCES Mechanic(Mechanic_ID),

    CONSTRAINT chk_mechanic_certificate
        CHECK (
            Certification_Name IN (
                'Standard Automotive Mechanic',
                'EV Technician',
                'Refrigeration System Technician'
            )
        ),

    CONSTRAINT chk_mechanic_dates
        CHECK (Expiry_Date > Issue_Date)
);
