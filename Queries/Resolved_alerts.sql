SELECT
Alert_ID,
VIN,
Alert_Type,
Action_Taken
FROM Predictive_Alert
WHERE Action_Taken IS NOT NULL;