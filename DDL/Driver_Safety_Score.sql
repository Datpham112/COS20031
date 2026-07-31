CREATE TABLE Driver_Safety_Score (
    Score_ID INT AUTO_INCREMENT PRIMARY KEY,
    Driver_ID VARCHAR(10) NOT NULL,
    Month INT NOT NULL,
    Year INT NOT NULL,
    Score DECIMAL(5,2) NOT NULL,

    CONSTRAINT fk_score_driver
        FOREIGN KEY (Driver_ID)
        REFERENCES Driver(Driver_ID),

    CONSTRAINT chk_month
        CHECK (Month BETWEEN 1 AND 12),

    CONSTRAINT chk_score
        CHECK (Score BETWEEN 0 AND 100)
);