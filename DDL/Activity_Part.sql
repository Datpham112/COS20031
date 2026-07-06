CREATE TABLE Activity_Part (
    Activity_ID INT,
    Part_ID INT,
    Quantity_Used INT CHECK (Quantity_Used > 0),
    Unit_Cost DECIMAL(10,2) CHECK (Unit_Cost >= 0),
    Total_Cost DECIMAL(10,2) CHECK (Total_Cost >= 0),

    PRIMARY KEY (Activity_ID, Part_ID),

    CONSTRAINT fk_activity_part_part
        FOREIGN KEY (Part_ID)
        REFERENCES Part(Part_ID)
);