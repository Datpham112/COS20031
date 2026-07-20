CREATE TABLE Event_Penalty (
    Event_Type VARCHAR(50) PRIMARY KEY,
    Penalty_Points INT NOT NULL,

    CONSTRAINT chk_penalty_points
        CHECK (Penalty_Points BETWEEN 0 AND 100)
);