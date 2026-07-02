CREATE TABLE Mechanic (
    Mechanic_ID INT AUTO_INCREMENT PRIMARY KEY,
    Workshop_ID INT NOT NULL,
    Full_Name VARCHAR(100) NOT NULL,

    CONSTRAINT fk_mechanic_workshop
        FOREIGN KEY (Workshop_ID)
        REFERENCES Workshop(Workshop_ID)
);
