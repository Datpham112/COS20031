CREATE TABLE Part (
    Part_ID INT PRIMARY KEY AUTO_INCREMENT,
    Part_Name VARCHAR(100) NOT NULL,
    Part_Category VARCHAR(50) NULL,
    Brand VARCHAR(50) NULL,
    Unit_Price DECIMAL(10,2) CHECK (Unit_Price >= 0),
    Reorder_Level INT CHECK (Reorder_Level >= 0)
);