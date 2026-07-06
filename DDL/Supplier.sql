CREATE TABLE Supplier (
    Supplier_ID INT PRIMARY KEY AUTO_INCREMENT,
    Supplier_Name VARCHAR(150) NOT NULL,
    Contact_Name VARCHAR(100) NULL,
    Phone_Number VARCHAR(15) UNIQUE NOT NULL,
    Email_Address VARCHAR(100) NULL,
    Address VARCHAR(255) NULL,
    Delivery_Time DATETIME NULL
);