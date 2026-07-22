CREATE VIEW vw_Part_Supplier_Details AS
SELECT 
    p.Part_ID, 
    p.Part_Name, 
    p.Part_Category, 
    p.Reorder_Level,
    s.Supplier_Name, 
    s.Contact_Name,
    s.Phone_Number,
    ps.Supplier_Type, 
    ps.Unit_Cost AS Supply_Cost, 
    ps.Lead_Time_Days
FROM Part p
JOIN Part_Supplier ps ON p.Part_ID = ps.Part_ID
JOIN Supplier s ON ps.Supplier_ID = s.Supplier_ID;

-- -> [Warranty_Claim], [Part], [Activity_Part]
CREATE VIEW vw_Warranty_Claim_Status AS
SELECT 
    wc.Claim_ID, 
    wc.Claim_Status, 
    wc.Claim_Date, 
    wc.Claim_Type,
    p.Part_Name, 
    p.Brand,
    ap.Quantity_Used,
    ap.Total_Cost AS Part_Total_Cost
FROM Warranty_Claims wc
JOIN Part p ON wc.Part_ID = p.Part_ID
JOIN Activity_Part ap ON wc.Activity_ID = ap.Activity_ID AND wc.Part_ID = ap.Part_ID;
