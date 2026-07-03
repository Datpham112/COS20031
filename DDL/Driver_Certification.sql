
CREATE TABLE Driver_Certification (
    Driver_ID VARCHAR(10) NOT NULL,
    Certification_Name VARCHAR(50) NOT NULL,
    Expiry_Date DATE NOT NULL,

    PRIMARY KEY (Driver_ID, Certification_Name),

    CONSTRAINT fk_cert_driver
        FOREIGN KEY (Driver_ID)
        REFERENCES Driver(Driver_ID)
);
    
