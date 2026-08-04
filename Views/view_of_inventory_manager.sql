CREATE VIEW vw_InventoryManager_PartsToReorder AS
SELECT Part_ID, Part_Name, Part_Category, Reorder_Level, Unit_Price
FROM Part
ORDER BY Reorder_Level DESC;

CREATE VIEW vw_InventoryManager_PendingWarranty AS
SELECT Claim_ID, Activity_ID, Part_ID, Claim_Date, Claim_Type
FROM Warranty_Claims
WHERE Claim_Status = 'Pending';