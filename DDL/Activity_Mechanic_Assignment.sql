CREATE TABLE Activity_Mechanic_Assignment (
    Activity_ID INT NOT NULL,
    Mechanic_ID INT NOT NULL,
    Labour_Hours DECIMAL(4,2) NOT NULL,

    PRIMARY KEY (Activity_ID, Mechanic_ID),

    CONSTRAINT fk_assignment_activity
        FOREIGN KEY (Activity_ID)
        REFERENCES Maintenance_Activity(Activity_ID),

    CONSTRAINT fk_assignment_mechanic
        FOREIGN KEY (Mechanic_ID)
        REFERENCES Mechanic(Mechanic_ID),

    CONSTRAINT chk_labour_hours
        CHECK (Labour_Hours >= 0)
);
