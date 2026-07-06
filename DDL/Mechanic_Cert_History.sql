CREATE TABLE Mechanic_Cert_History (
    Cert_ID INT PRIMARY KEY,
    Mechanic_ID INT NOT NULL,
    Certificate_Name VARCHAR(255),
    issue_Date DATE,
    Expiry_Date DATE,

    CONSTRAINT fk_mechanic_cert_history_mechanic
        FOREIGN KEY (Mechanic_ID)
        REFERENCES Mechanic(Mechanic_ID)
);