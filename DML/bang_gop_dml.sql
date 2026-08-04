SET FOREIGN_KEY_CHECKS = 0;
INSERT IGNORE INTO Activity_Mechanic_Assignment
(
    Activity_ID,
    Mechanic_ID,
    Labour_Hours
)
VALUES
(1, 1, 2.50),
(2, 2, 1.00),
(3, 3, 6.00),
(4, 4, 5.00),
(5, 6, 1.50);
INSERT IGNORE INTO Activity_Part
(
    Activity_ID,
    Part_ID,
    Quantity_Used,
    Unit_Cost,
    Total_Cost
)
VALUES
(1, 1, 1, 380000.00, 380000.00),
(2, 2, 2, 2900000.00, 5800000.00),
(3, 3, 1, 23000000.00, 23000000.00),
(4, 4, 1, 1600000.00, 1600000.00),
(5, 5, 1, 100000.00, 100000.00),
(5, 6, 1, 75000.00, 75000.00);
INSERT IGNORE INTO Depot (Location_Name)
VALUES
('Ha Noi'),
('Da Nang'),
('Ho Chi Minh City'),
('Can Tho');
INSERT IGNORE INTO Driver_Certification
(
Driver_ID,
driver_name,
Certification_Name,
Expiry_Date
)
VALUES
('D-112','Nguyen Van An', 'Standard Licence','2027-02-14'),
('D-112','Nguyen Van An','EV Certification','2027-04-30'),
('D-204','Tran Thi Bich','Heavy Vehicle Licence','2028-12-08'),
('D-204','Tran Thi Bich','Refrigerated Transport Certification','2029-05-01'),
('D-331','Le Quoc Minh','Standard Licence','2026-06-21'),
('D-331','Le Quoc Minh','Hazardous Goods Certification','2027-11-18'),
('D-417','Pham Duc Long','Heavy Vehicle Licence','2028-01-15'),
('D-417','Pham Duc Long','Standard Licence','2025-08-20');

select * from driver_certification;INSERT IGNORE INTO Driver (
    Driver_ID,
    Depot_ID,
    Full_Name,
    Contact_Information,
    Emergency_Contact,
    License_Type,
    License_Expiry_Date,
    Employment_Status
)
VALUES
('D-112',1,'Nguyen Van An','0901234567','Tran Thi Hoa','Standard Licence','2027-02-14','Active'),
('D-204',1,'Tran Thi Bich','0902345678','Nguyen Van Nam','Heavy Vehicle Licence','2028-12-08','Active'),
('D-331',2,'Le Quoc Minh','0903456789','Pham Thi Lan','Standard Licence','2026-06-21','On Leave'),
('D-417',3,'Pham Duc Long','0904567890','Hoang Van Duc','Heavy Vehicle Licence','2027-11-18','Active'),
('D-302',4,'Nguyen Thi Mai','0905678901','Tran Quoc Bao','Standard Licence','2025-08-20','Suspended');

INSERT IGNORE INTO Driver_Safety_Score
(
    Driver_ID,
    Month,
    Year,
    Score
)
VALUES
('D-112', 1, 2026, 95),
('D-204', 1, 2026, 88),
('D-331', 1, 2026, 72),
('D-417', 1, 2026, 45),
('D-302', 1, 2026, 60),

('D-112', 2, 2026, 92),
('D-204', 2, 2026, 85),
('D-331', 2, 2026, 70),
('D-417', 2, 2026, 40),
('D-302', 2, 2026, 58);INSERT IGNORE INTO Event_Penalty
(Event_Type, Penalty_Points)
VALUES
('Speeding', 10),
('Harsh Braking', 8),
('Rapid Acceleration', 5),
('Fatigue Driving', 20),
('Phone Usage', 15),
('Seatbelt Violation', 10),
('Lane Departure', 12),
('Collision Warning', 25),
('Unsafe Following Distance', 8),
('Engine Overspeed', 6);
INSERT IGNORE INTO Maintenance_Activity
(
    Job_ID,
    Activity_Type,
    Diagnostic_Result,
    Repeat_Fault_Indicator,
    Warranty_Indicator
)
VALUES
(1, 'Brake Inspection', 'Front brake pads worn, replaced with new set', FALSE, FALSE),
(2, 'Tyre Replacement', 'Rear tyre pressure sensor faulty, replacement scheduled', FALSE, FALSE),
(3, 'Battery Replacement', 'Battery degraded below 70 percent capacity, replaced under warranty', TRUE, TRUE),
(4, 'Cooling System Repair', 'Radiator leak found and repaired', FALSE, FALSE),
(5, 'General Inspection', 'Routine service inspection, no issues found', FALSE, FALSE);
INSERT IGNORE INTO Maintenance_Job
(
    VIN,
    Workshop_ID,
    Linked_Alert_ID,
    Date_Opened,
    Date_Closed,
    Downtime_Hours,
    Total_Cost,
    Priority
)
VALUES
('VIN00000000000001', 1, 1, '2026-02-01', '2026-02-03', 8.50, 1500000.00, 'Moderate'),
('VIN00000000000002', 1, 2, '2026-03-05', NULL, NULL, NULL, 'Low'),
('VIN00000000000003', 2, 3, '2026-01-20', '2026-01-25', 40.00, 8500000.00, 'High'),
('VIN00000000000004', 3, 4, '2025-12-10', '2025-12-15', 30.00, 6200000.00, 'High'),
('VIN00000000000005', 4, NULL, '2026-04-01', '2026-04-02', 5.00, 500000.00, 'Low');
INSERT IGNORE INTO Mechanic_Certification
(
  Mechanic_ID,
  Certification_Name,
  Issue_Date,
  Expiry_Date
)
VALUES
(1, 'Standard Automotive Mechanic', '2023-01-10', '2027-01-10'),
(2, 'Standard Automotive Mechanic', '2022-06-15', '2026-06-15'),
(3, 'EV Technician', '2023-03-01', '2026-03-01'),
(4, 'Refrigeration System Technician', '2022-11-20', '2026-11-20'),
(4, 'Standard Automotive Mechanic', '2021-05-05', '2025-05-05'),
(5, 'Standard Automotive Mechanic', '2023-07-07', '2027-07-07'),
(6, 'Standard Automotive Mechanic', '2022-09-09', '2026-09-09');
INSERT IGNORE INTO Mechanic_Cert_History
(
  Cert_ID,
  Mechanic_ID,
  Certificate_Name,
  issue_Date,
  Expiry_Date
)
VALUES
(1, 1, 'Engine Diagnostics Certificate', '2024-01-15', '2027-01-15'),
(2, 2, 'Brake System Safety Certificate', '2024-03-10', '2027-03-10'),
(3, 3, 'HV Battery Service Certificate', '2024-05-20', '2027-05-20'),
(4, 4, 'Heavy Vehicle Inspection Certificate', '2024-07-08', '2027-07-08'),
(5, 5, 'Air Conditioning Repair Certificate', '2024-09-12', '2027-09-12');
INSERT IGNORE INTO Mechanic
(
  Workshop_ID,
  Full_Name,
  Employment_Type,
  Employment_Status
)
VALUES
(1, 'Nguyen Van Hung', 'Full-Time',  'Active'),
(1, 'Tran Van Duc',    'Full-Time',  'Active'),
(2, 'Le Thi Mai',      'Part-Time',  'Active'),
(3, 'Pham Van Son',    'Full-Time',  'Active'),
(3, 'Hoang Thi Lan',   'Contractor', 'On Leave'),
(4, 'Vo Van Tam',      'Apprentice', 'Active');
INSERT IGNORE INTO Part
(
  Part_Name,
  Part_Category,
  Brand,
  Unit_Price,
  Reorder_Level
)
VALUES
('Brake Pad Set', 'Brake System', 'Bosch', 450000.00, 10),
('Tyre - Heavy Duty', 'Tyre', 'Bridgestone', 3200000.00, 5),
('EV Battery Module', 'Battery', 'BYD', 25000000.00, 2),
('Radiator Assembly', 'Cooling System', 'Denso', 1800000.00, 3),
('Engine Oil Filter', 'Engine', 'Mann Filter', 120000.00, 20),
('Cabin Air Filter', 'General', 'Mann Filter', 90000.00, 15);
INSERT IGNORE INTO Part_Supplier
(
  Part_ID,
  Supplier_ID,
  Supplier_Type,
  Unit_Cost,
  Lead_Time_Days
)
VALUES
(1, 1, 'Primary', 380000.00, 3),
(1, 4, 'Backup', 400000.00, 5),
(2, 4, 'Primary', 2900000.00, 7),
(3, 2, 'Primary', 23000000.00, 14),
(4, 3, 'Primary', 1600000.00, 5),
(5, 1, 'Primary', 100000.00, 2),
(6, 1, 'Primary', 75000.00, 2);
INSERT IGNORE INTO Predictive_Alert
(
  VIN,
  Depot_ID,
  Alert_Type,
  Action_Taken
)
VALUES
('VIN00000000000001', 1, 'Brake Wear', 'Scheduled Repair'),
('VIN00000000000002', 1, 'Tyre Pressure', 'Acknowledged'),
('VIN00000000000003', 2, 'Battery Degradation', 'Emergency Repair'),
('VIN00000000000004', 3, 'Overheating Risk', 'Resolved'),
('VIN00000000000005', 4, 'Engine Fault', 'Acknowledged');

ALTER TABLE Predictive_Alert DROP CHECK chk_alert_type;

UPDATE Predictive_Alert
SET Alert_Type = 'Oil Quality Deterioration'
WHERE Alert_Type = 'Engine Fault';

ALTER TABLE Predictive_Alert
    ADD CONSTRAINT chk_alert_type
        CHECK (
            Alert_Type IN (
                'Brake Wear',
                'Overheating Risk',
                'Battery Degradation',
                'Oil Quality Deterioration',
                'Transmission Fault',
                'Cooling System Anomaly',
                'Tyre Pressure'
            )
        );
INSERT IGNORE INTO Safety_Event
(
    Event_ID,
    Driver_ID,
    VIN,
    Depot_ID,
    Timestamp,
    Event_Type,
    Severity_Level,
    Odometer_At_Event,
    Review_Comments
)
VALUES
(1, 'D-112', 'VIN00000000000001', 1, '2026-01-14 08:15:00', 'Hard Braking', 'Medium', 45218.40, 'Driver braked sharply while entering the depot gate.'),
(2, 'D-204', 'VIN00000000000002', 1, '2026-01-18 16:42:00', 'Speeding', 'High', 120884.70, 'Vehicle exceeded the speed limit on a suburban route.'),
(3, 'D-331', 'VIN00000000000003', 2, '2026-02-03 11:05:00', 'Lane Departure', 'Low', 12412.90, 'Brief lane drift detected during heavy traffic conditions.'),
(4, 'D-417', 'VIN00000000000004', 3, '2026-02-11 19:27:00', 'Sharp Cornering', 'Medium', 112942.10, 'Cornering speed was higher than expected for road conditions.'),
(5, 'D-302', 'VIN00000000000005', 4, '2026-02-19 07:50:00', 'Sudden Acceleration', 'High', 5488.60, 'Rapid acceleration observed during morning departure from depot.');INSERT IGNORE INTO Staff (
    Staff_ID,
    Full_Name,
    Role_Type,
    Depot_ID,
    Contact_Info,
    Username,
    Password_Hash
)
VALUES
('S-001','Nguyen Thanh Son','Head Manager',NULL,'0901000001 | head.manager@fleetops.com','head.manager',NULL),
('S-002','Tran Minh Khoa','Depot Manager',1,'0901000002 | depot.manager.hn@fleetops.com','depot.manager.hn',NULL),
('S-003','Le Thu Ha','Depot Manager',2,'0901000003 | depot.manager.dn@fleetops.com','depot.manager.dn',NULL),
('S-004','Pham Quang Huy','Depot Manager',3,'0901000004 | depot.manager.hcm@fleetops.com','depot.manager.hcm',NULL),
('S-005','Vu Ngoc Anh','Depot Manager',4,'0901000005 | depot.manager.ct@fleetops.com','depot.manager.ct',NULL),
('S-006','Hoang Gia Bao','Workshop Manager',1,'0901000006 | workshop.manager.hn@fleetops.com','workshop.manager.hn',NULL),
('S-007','Doan Minh Tuan','Driver Manager',2,'0901000007 | driver.manager.dn@fleetops.com','driver.manager.dn',NULL),
('S-008','Nguyen Thuy Linh','Inventory Manager',NULL,'0901000008 | inventory.manager@fleetops.com','inventory.manager',NULL);INSERT IGNORE INTO Supplier
(
  Supplier_Name,
  Contact_Name,
  Phone_Number,
  Email_Address,
  Address,
  Delivery_Time
)
VALUES
('AutoParts Vietnam Co., Ltd', 'Nguyen Thanh', '0281234567', 'contact@autopartsvn.com', '123 Nguyen Trai, Ho Chi Minh City', NULL),
('EV Components Asia', 'Tran Minh', '0282345678', 'sales@evcomponents.asia', '45 Le Loi, Da Nang', NULL),
('Cool Truck Supplies', 'Le Hoa', '0283456789', 'info@coolsupplies.vn', '78 Tran Hung Dao, Ha Noi', NULL),
('National Auto Distributors', 'Pham Long', '0284567890', 'orders@natauto.vn', '12 Vo Van Kiet, Can Tho', NULL);
INSERT IGNORE INTO Vehicle
(
VIN,
Depot_ID,
Registration_Number,
Vehicle_Category,
Manufacturer_and_Model,
Year_of_Manufacture,
Current_Odometer,
Operational_Status
)
VALUES
('VIN00000000000001',1,'29A12345','Delivery Van','Toyota Hiace',2022,45100.50,'Active'),
('VIN00000000000002',1,'29C56789','Heavy Transport Truck','Isuzu Giga',2021,120560.30,'Available'),
('VIN00000000000003',2,'43E45678','Electric Van','BYD T3',2023,12300.00,'Under Maintenance'),
('VIN00000000000004',3,'51C78901','Refrigerated Truck','Hino 500',2020,112480.40,'Out of Service'),
('VIN00000000000005',4,'65A11223','Service Vehicle','Ford Ranger',2024,5200.80,'Awaiting Inspection');
INSERT IGNORE INTO Vehicle_Driver_Assignment
(
    Driver_ID,
    VIN,
    Start_Date,
    End_Date
)
VALUES
('D-112','VIN00000000000001','2026-01-01 08:00:00','2026-03-31 17:00:00'),
('D-204','VIN00000000000002','2026-04-01 08:00:00',NULL),
('D-331','VIN00000000000003','2026-02-10 07:30:00',NULL),
('D-417','VIN00000000000004','2026-01-15 09:00:00','2026-05-15 18:00:00'),
('D-302','VIN00000000000005','2026-03-01 08:00:00',NULL);
INSERT IGNORE INTO Warranty_Claims
(
  Activity_ID,
  Part_ID,
  Claim_Status,
  Claim_Date,
  Claim_Type
)
VALUES
(1, 1, 'Approved', '2024-02-18', 'Part Replacement'),
(2, 2, 'Pending', '2024-04-05', 'Tyre Warranty'),
(3, 3, 'Approved', '2024-06-21', 'Battery Warranty'),
(4, 4, 'Rejected', '2024-08-14', 'Cooling System Repair'),
(5, 5, 'Pending', '2024-10-02', 'Oil Filter Replacement');
INSERT  INTO Workshop
(
    Depot_ID
)
VALUES
(1),
(2),
(3),
(4);
SET FOREIGN_KEY_CHECKS = 1;