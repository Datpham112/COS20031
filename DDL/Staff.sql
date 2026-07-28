CREATE TABLE Staff (
    Staff_ID VARCHAR(10) PRIMARY KEY,
    Full_Name VARCHAR(100) NOT NULL,
    Role_Type VARCHAR(30) NOT NULL,
    Depot_ID INT NULL,
    Contact_Info VARCHAR(100) NOT NULL,
    Username VARCHAR(50) NOT NULL UNIQUE,
    Password_Hash VARCHAR(255) NULL,

    CONSTRAINT fk_staff_depot
        FOREIGN KEY (Depot_ID)
        REFERENCES Depot(Depot_ID),

    CONSTRAINT chk_staff_role
        CHECK (
            Role_Type IN (
                'Head Manager',
                'Depot Manager',
                'Driver Manager',
                'Workshop Manager',
                'Inventory Manager',
                'Mechanic',
                'Driver'
            )
        ),

    CONSTRAINT chk_staff_depot_scope
        CHECK (
            (Role_Type IN ('Head Manager', 'Inventory Manager') AND Depot_ID IS NULL)
            OR
            (Role_Type IN ('Depot Manager', 'Driver Manager', 'Workshop Manager', 'Mechanic', 'Driver') AND Depot_ID IS NOT NULL)
        )
);