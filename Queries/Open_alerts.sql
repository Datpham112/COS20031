SELECT
Alert_ID,
VIN,
Alert_Type,
Created_Date,
Action_Taken
FROM Predictive_Alert
WHERE Action_Taken IS NULL;