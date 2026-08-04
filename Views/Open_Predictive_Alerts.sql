CREATE VIEW View_Open_Predictive_Alerts AS

SELECT
Alert_ID,
VIN,
Alert_Type,
Created_Date

FROM Predictive_Alert

WHERE Action_Taken IS NULL;