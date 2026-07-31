CREATE TABLE Workshop (
    Workshop_ID INT AUTO_INCREMENT PRIMARY KEY,
    Depot_ID INT NOT NULL UNIQUE,

    CONSTRAINT fk_workshop_depot
        FOREIGN KEY (Depot_ID)
        REFERENCES Depot(Depot_ID)
);
