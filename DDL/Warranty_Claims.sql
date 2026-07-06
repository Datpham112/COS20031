CREATE TABLE Warranty_Claims (
    Claim_ID INT PRIMARY KEY AUTO_INCREMENT,
    Activity_ID INT,
    Part_ID INT,
    Claim_Status VARCHAR(50) CHECK (Claim_Status IN ('Pending', 'Approved', 'Rejected')),
    Claim_Date DATE NOT NULL,
    Claim_Type VARCHAR(50) NULL,

    CONSTRAINT fk_warranty_claims_activity
        FOREIGN KEY (Activity_ID)
        REFERENCES Maintenance_Activity(Activity_ID),

    CONSTRAINT fk_warranty_claims_part
        FOREIGN KEY (Part_ID)
        REFERENCES Part(Part_ID)
);