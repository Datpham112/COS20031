CREATE TABLE Part_Supplier (
    Part_ID INT,
    Supplier_ID INT,
    Supplier_Type VARCHAR(50) NULL,
    Unit_Cost DECIMAL(10,2) CHECK (Unit_Cost >= 0),
    Lead_Time_Days INT CHECK (Lead_Time_Days >= 0),

    PRIMARY KEY (Part_ID, Supplier_ID),

    CONSTRAINT fk_part_supplier_part
        FOREIGN KEY (Part_ID)
        REFERENCES Part(Part_ID),

    CONSTRAINT fk_part_supplier_supplier
        FOREIGN KEY (Supplier_ID)
        REFERENCES Supplier(Supplier_ID)
);