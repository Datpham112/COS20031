-- Consolidated SQL import for Fleet Management Database
-- Generated on 2026-07-30

CREATE DATABASE IF NOT EXISTS fleet_management;
USE fleet_management;


-- =========================
-- DDL/Depot.sql
-- =========================

create table Depot (
	Depot_ID int auto_increment primary key,
	Location_Name varchar(255) not null
);



-- =========================
-- DDL/Workshop.sql
-- =========================
CREATE TABLE Workshop (
    Workshop_ID INT AUTO_INCREMENT PRIMARY KEY,
    Depot_ID INT NOT NULL UNIQUE,

    CONSTRAINT fk_workshop_depot
        FOREIGN KEY (Depot_ID)
        REFERENCES Depot(Depot_ID)
);


-- =========================
-- DDL/Staff.sql
-- =========================
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

-- =========================
-- DDL/Roles_and_Permissions.sql
-- =========================
CREATE ROLE 'head_manager', 'depot_manager', 'workshop_manager', 
            'driver_manager', 'inventory_manager', 'mechanic_role', 'driver_role';
-- [Head Manager]
GRANT ALL PRIVILEGES ON *.* TO 'head_manager';

-- [Depot Manager]
GRANT SELECT, INSERT, UPDATE ON Vehicle TO 'depot_manager';
GRANT SELECT, INSERT, UPDATE ON Driver TO 'depot_manager';
GRANT SELECT ON Maintenance_Job TO 'depot_manager';
GRANT SELECT ON Safety_Event TO 'depot_manager';

-- [Workshop Manager]
GRANT SELECT, INSERT, UPDATE ON Maintenance_Job TO 'workshop_manager';
GRANT SELECT, INSERT, UPDATE ON Maintenance_Activity TO 'workshop_manager';
GRANT SELECT, INSERT, UPDATE ON Mechanic TO 'workshop_manager';
GRANT SELECT, INSERT, UPDATE ON Activity_Mechanic_Assignment TO 'workshop_manager';
GRANT SELECT, UPDATE ON Predictive_Alert TO 'workshop_manager'; 

-- [Driver Manager]
GRANT SELECT, INSERT, UPDATE ON Driver TO 'driver_manager';
GRANT SELECT, INSERT, UPDATE ON Driver_Certification TO 'driver_manager';
GRANT SELECT, INSERT, UPDATE ON Vehicle_Driver_Assignment TO 'driver_manager';
GRANT SELECT, INSERT, UPDATE ON Driver_Safety_Score TO 'driver_manager';
GRANT SELECT, UPDATE ON Safety_Event TO 'driver_manager';

-- [Inventory Manager]
GRANT SELECT, INSERT, UPDATE, DELETE ON Part TO 'inventory_manager';
GRANT SELECT, INSERT, UPDATE, DELETE ON Supplier TO 'inventory_manager';
GRANT SELECT, INSERT, UPDATE, DELETE ON Part_Supplier TO 'inventory_manager';
GRANT SELECT, INSERT, UPDATE ON Activity_Part TO 'inventory_manager';
GRANT SELECT, INSERT, UPDATE ON Warranty_Claims TO 'inventory_manager';

-- [Mechanic Role]
GRANT SELECT ON Maintenance_Job TO 'mechanic_role';
GRANT SELECT, UPDATE ON Maintenance_Activity TO 'mechanic_role';
GRANT SELECT, INSERT ON Activity_Part TO 'mechanic_role';

-- [Driver Role]
GRANT SELECT ON Driver TO 'driver_role';
GRANT SELECT ON Driver_Safety_Score TO 'driver_role';
GRANT SELECT ON Vehicle_Driver_Assignment TO 'driver_role';

FLUSH PRIVILEGES;


-- =========================
-- DDL/Supplier.sql
-- =========================
CREATE TABLE Supplier (
    Supplier_ID INT PRIMARY KEY AUTO_INCREMENT,
    Supplier_Name VARCHAR(150) NOT NULL,
    Contact_Name VARCHAR(100) NULL,
    Phone_Number VARCHAR(15) UNIQUE NOT NULL,
    Email_Address VARCHAR(100) NULL,
    Address VARCHAR(255) NULL,
    Delivery_Time DATETIME NULL
);

-- =========================
-- DDL/Part.sql
-- =========================
CREATE TABLE Part (
    Part_ID INT PRIMARY KEY AUTO_INCREMENT,
    Part_Name VARCHAR(100) NOT NULL,
    Part_Category VARCHAR(50) NULL,
    Brand VARCHAR(50) NULL,
    Unit_Price DECIMAL(10,2) CHECK (Unit_Price >= 0),
    Reorder_Level INT CHECK (Reorder_Level >= 0)
);

-- =========================
-- DDL/Part_Supplier.sql
-- =========================
CREATE TABLE Part_Supplier (
    Part_ID INT,
    Supplier_ID INT,
    Supplier_Type VARCHAR(50) NULL,
    Unit_Cost DECIMAL(10,2) CHECK (Unit_Cost >= 0),
    Lead_Time_Days INT CHECK (Lead_Time_Days >= 0),

    PRIMARY KEY (Part_ID, Supplier_ID),

    CONSTRAINT fk_part_supplier_part
        FOREIGN KEY (Part_ID)
        REFERENCES Part(Part_ID),

    CONSTRAINT fk_part_supplier_supplier
        FOREIGN KEY (Supplier_ID)
        REFERENCES Supplier(Supplier_ID)
);

-- =========================
-- DDL/Vehicle.sql
-- =========================
create table Vehicle (
	Vin varchar(17) not null primary key,
    Depot_ID int not null,
    Registration_Number varchar(20) not null unique,
    Vehicle_Category varchar(50) not null,
    Manufacturer_and_Model varchar(100) not null,
    Year_of_Manufacture year not null,
    Current_Odometer decimal(10,2) default 0,
    Operational_Status varchar(30) not null,
    
    constraint fk_vehicle_depot
		foreign key (Depot_ID)
		references Depot(Depot_ID),
        
	constraint chk_vehicle_category
		check (Vehicle_Category in ('Delivery Van', 'Refrigerated Truck', 'Electric Van', 'Service Vehicle', 'Heavy Transport Truck')), 
	    
	constraint chk_manufacture_year
        check (Year_of_Manufacture >= 2000),

    constraint chk_odometer
        check (Current_Odometer >= 0),

    constraint chk_operational_status
        check (Operational_Status in ('Active','Available','Under Maintenance','Awaiting Inspection','Out of Service','Retired'))
);


-- =========================
-- DDL/Driver.sql
-- =========================
CREATE TABLE Driver (
    Driver_ID VARCHAR(10) PRIMARY KEY,
    Depot_ID INT NOT NULL,
    Full_Name VARCHAR(100) NOT NULL,
    Contact_Information VARCHAR(100) NOT NULL,
    Emergency_Contact VARCHAR(100) NOT NULL,
    License_Type VARCHAR(50) NOT NULL,
    License_Expiry_Date DATE NOT NULL,
    Employment_Status VARCHAR(20) NOT NULL,

    CONSTRAINT fk_driver_depot
        FOREIGN KEY (Depot_ID)
        REFERENCES Depot(Depot_ID),
	
	CONSTRAINT chk_employment_status
        CHECK (Employment_Status IN ('Active','On Leave','Suspended','Terminated'))
);


-- =========================
-- DDL/Driver_Certification.sql
-- =========================

CREATE TABLE Driver_Certification (
    Driver_ID VARCHAR(10) NOT NULL,
    Driver_Name VARCHAR(100),
    Certification_Name VARCHAR(50) NOT NULL,
    Expiry_Date DATE NOT NULL,

    PRIMARY KEY (Driver_ID, Certification_Name),

    CONSTRAINT fk_cert_driver
        FOREIGN KEY (Driver_ID)
        REFERENCES Driver(Driver_ID)
);
    


-- =========================
-- DDL/Driver_Safety_Score.sql
-- =========================
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

-- =========================
-- DDL/Mechanic.sql
-- =========================
CREATE TABLE Mechanic (
    Mechanic_ID INT AUTO_INCREMENT PRIMARY KEY,
    Workshop_ID INT NOT NULL,
    Full_Name VARCHAR(100) NOT NULL,

    CONSTRAINT fk_mechanic_workshop
        FOREIGN KEY (Workshop_ID)
        REFERENCES Workshop(Workshop_ID)
);


-- =========================
-- DDL/Mechanic_Certification.sql
-- =========================
CREATE TABLE Mechanic_Certification (
    Mechanic_ID INT NOT NULL,
    Certification_Name VARCHAR(100) NOT NULL,
    Issue_Date DATE NOT NULL,
    Expiry_Date DATE NOT NULL,
 
    PRIMARY KEY (Mechanic_ID, Certification_Name),
 
    CONSTRAINT fk_mechanic_certification
        FOREIGN KEY (Mechanic_ID)
        REFERENCES Mechanic(Mechanic_ID),
 
    CONSTRAINT chk_mechanic_certificate
        CHECK (
            Certification_Name IN (
                'Standard Automotive Mechanic',
                'EV Technician',
                'Refrigeration System Technician'
            )
        ),
 
    CONSTRAINT chk_mechanic_dates
        CHECK (Expiry_Date > Issue_Date)
);


-- =========================
-- DDL/Mechanic_Cert_History.sql
-- =========================
CREATE TABLE Mechanic_Cert_History (
    Cert_ID INT PRIMARY KEY,
    Mechanic_ID INT NOT NULL,
    Certificate_Name VARCHAR(255),
    issue_Date DATE,
    Expiry_Date DATE,

    CONSTRAINT fk_mechanic_cert_history_mechanic
        FOREIGN KEY (Mechanic_ID)
        REFERENCES Mechanic(Mechanic_ID)
);

-- =========================
-- DDL/Maintenance_Job.sql
-- =========================
CREATE TABLE Maintenance_Job (
    Job_ID INT AUTO_INCREMENT PRIMARY KEY,
    VIN VARCHAR(17) NOT NULL,
    Workshop_ID INT NOT NULL,
    Linked_Alert_ID INT,
    Date_Opened DATE NOT NULL,
    Date_Closed DATE,
    Downtime_Hours DECIMAL(6,2),
    Total_Cost DECIMAL(10,2),
 
    CONSTRAINT fk_job_vehicle
        FOREIGN KEY (VIN)
        REFERENCES Vehicle(VIN),
 
    CONSTRAINT fk_job_workshop
        FOREIGN KEY (Workshop_ID)
        REFERENCES Workshop(Workshop_ID),
 
    CONSTRAINT fk_job_alert
        FOREIGN KEY (Linked_Alert_ID)
        REFERENCES Predictive_Alert(Alert_ID),
 
    CONSTRAINT chk_downtime
        CHECK (Downtime_Hours >= 0),
 
    CONSTRAINT chk_total_cost
        CHECK (Total_Cost >= 0),
 
    CONSTRAINT chk_job_dates
        CHECK (Date_Closed IS NULL OR Date_Closed >= Date_Opened)
);


-- =========================
-- DDL/Maintenance_Activity.sql
-- =========================
CREATE TABLE Maintenance_Activity (
    Activity_ID INT AUTO_INCREMENT PRIMARY KEY,
    Job_ID INT NOT NULL,
    Activity_Type VARCHAR(100) NOT NULL,
    Diagnostic_Result TEXT,
    Repeat_Fault_Indicator BOOLEAN DEFAULT FALSE,
    Warranty_Indicator BOOLEAN DEFAULT FALSE,
 
    CONSTRAINT fk_activity_job
        FOREIGN KEY (Job_ID)
        REFERENCES Maintenance_Job(Job_ID),
 
    CONSTRAINT chk_activity_type
        CHECK (
            Activity_Type IN (
                'Brake Inspection',
                'Tyre Replacement',
                'Battery Replacement',
                'Oil Change',
                'Cooling System Repair',
                'Electrical Repair',
                'General Inspection'
            )
        )
);


-- =========================
-- DDL/Activity_Part.sql
-- =========================
CREATE TABLE Activity_Part (
    Activity_ID INT,
    Part_ID INT,
    Quantity_Used INT CHECK (Quantity_Used > 0),
    Unit_Cost DECIMAL(10,2) CHECK (Unit_Cost >= 0),
    Total_Cost DECIMAL(10,2) CHECK (Total_Cost >= 0),

    PRIMARY KEY (Activity_ID, Part_ID),

    CONSTRAINT fk_activity_part_part
        FOREIGN KEY (Part_ID)
        REFERENCES Part(Part_ID)
);

-- =========================
-- DDL/Activity_Mechanic_Assignment.sql
-- =========================
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


-- =========================
-- DDL/Vehicle_Driver_Assignment.sql
-- =========================
CREATE TABLE Vehicle_Driver_Assignment (
    Assignment_ID INT AUTO_INCREMENT PRIMARY KEY,
    Driver_ID VARCHAR(100) NOT NULL,
    VIN VARCHAR(17) NOT NULL,
    Start_Date DATETIME NOT NULL,
    End_Date DATETIME,

    CONSTRAINT fk_assignment_driver
        FOREIGN KEY (Driver_ID)
        REFERENCES Driver(Driver_ID),

    CONSTRAINT fk_assignment_vehicle
        FOREIGN KEY (VIN)
        REFERENCES Vehicle(VIN),

    CONSTRAINT chk_assignment_dates
        CHECK (End_Date IS NULL OR End_Date >= Start_Date)
);


-- =========================
-- DDL/Safety_Event.sql
-- =========================
CREATE TABLE Safety_Event (
    Event_ID INT AUTO_INCREMENT PRIMARY KEY,
    Driver_ID VARCHAR(10) NOT NULL,
    VIN VARCHAR(17) NOT NULL,
    Depot_ID INT NOT NULL,
    Timestamp DATETIME NOT NULL,
    Event_Type VARCHAR(50) NOT NULL,
    Severity_Level VARCHAR(20) NOT NULL,
    Odometer_At_Event DECIMAL(10,2) NOT NULL,
    Review_Comments TEXT,

    CONSTRAINT fk_event_driver
        FOREIGN KEY (Driver_ID)
        REFERENCES Driver(Driver_ID),

    CONSTRAINT fk_event_vehicle
        FOREIGN KEY (VIN)
        REFERENCES Vehicle(VIN),

    CONSTRAINT fk_event_depot
        FOREIGN KEY (Depot_ID)
        REFERENCES Depot(Depot_ID),

    CONSTRAINT chk_severity
        CHECK (Severity_Level IN ('Low','Medium','High','Critical'))
);


-- =========================
-- DDL/Event_penalty.sql
-- =========================
CREATE TABLE Event_Penalty (
    Event_Type VARCHAR(50) PRIMARY KEY,
    Penalty_Points INT NOT NULL,

    CONSTRAINT chk_penalty_points
        CHECK (Penalty_Points BETWEEN 0 AND 100)
);

-- =========================
-- DDL/Predictive_Alert.sql
-- =========================
CREATE TABLE Predictive_Alert (
    Alert_ID INT AUTO_INCREMENT PRIMARY KEY,
    VIN VARCHAR(17) NOT NULL,
    Depot_ID INT NOT NULL,
    Alert_Type VARCHAR(50) NOT NULL,
    Action_Taken VARCHAR(30) NOT NULL,

     CONSTRAINT fk_alert_vehicle
        FOREIGN KEY (VIN)
        REFERENCES Vehicle(VIN),
 
    CONSTRAINT fk_alert_depot
        FOREIGN KEY (Depot_ID)
        REFERENCES Depot(Depot_ID),
 
    CONSTRAINT chk_alert_type
        CHECK (
            Alert_Type IN (
                'Brake Wear',
                'Overheating Risk',
                'Battery Degradation',
                'Engine Fault',
                'Tyre Pressure'
            )
        ),
 
    CONSTRAINT chk_action_taken
        CHECK (
            Action_Taken IN (
                'Acknowledged',
                'Scheduled Repair',
                'Emergency Repair',
                'Resolved'
            )
        )
);


-- =========================
-- DDL/Warranty_Claims.sql
-- =========================
CREATE TABLE Warranty_Claims (
    Claim_ID INT PRIMARY KEY AUTO_INCREMENT,
    Activity_ID INT,
    Part_ID INT,
    Claim_Status VARCHAR(50) CHECK (Claim_Status IN ('Pending', 'Approved', 'Rejected')),
    Claim_Date DATE NOT NULL,
    Claim_Type VARCHAR(50) NULL,

    CONSTRAINT fk_warranty_claims_activity
        FOREIGN KEY (Activity_ID)
        REFERENCES Maintenance_Activity(Activity_ID),

    CONSTRAINT fk_warranty_claims_part
        FOREIGN KEY (Part_ID)
        REFERENCES Part(Part_ID)
);

-- =========================
-- DML/depot_data.sql
-- =========================
INSERT INTO Depot (Location_Name)
VALUES
('Ha Noi'),
('Da Nang'),
('Ho Chi Minh City'),
('Can Tho');


-- =========================
-- DML/workshop_data.sql
-- =========================
INSERT  INTO Workshop
(
    Depot_ID
)
VALUES
(1),
(2),
(3),
(4);


-- =========================
-- DML/Staff_data.sql
-- =========================
INSERT INTO Staff (
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
('S-008','Nguyen Thuy Linh','Inventory Manager',NULL,'0901000008 | inventory.manager@fleetops.com','inventory.manager',NULL);

-- =========================
-- DML/supplier_data.sql
-- =========================
INSERT INTO Supplier
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


-- =========================
-- DML/part_data.sql
-- =========================
INSERT INTO Part
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


-- =========================
-- DML/part_supplier_data.sql
-- =========================
INSERT INTO Part_Supplier
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


-- =========================
-- DML/vehicle_data.sql
-- =========================
INSERT INTO Vehicle
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


-- =========================
-- DML/driver_data.sql
-- =========================
INSERT INTO Driver (
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



-- =========================
-- DML/driver_certification_data.sql
-- =========================
INSERT INTO Driver_Certification
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

select * from driver_certification;

-- =========================
-- DML/driver_safety_score_data.sql
-- =========================
INSERT INTO Driver_Safety_Score
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
('D-302', 2, 2026, 58);

-- =========================
-- DML/mechanic_data.sql
-- =========================
INSERT INTO Mechanic
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


-- =========================
-- DML/mechanic_certification_data.sql
-- =========================
INSERT INTO Mechanic_Certification
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


-- =========================
-- DML/mechanic_cert_history_data.sql
-- =========================
INSERT INTO Mechanic_Cert_History
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
(5, 5, 'Air Conditioning Repair Certificate', '2024-09-12', '2027-09-12')

-- =========================
-- DML/maintenance_job_data.sql
-- =========================
INSERT INTO Maintenance_Job
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


-- =========================
-- DML/maintenance_activity_data.sql
-- =========================
INSERT INTO Maintenance_Activity
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


-- =========================
-- DML/activity_part_data.sql
-- =========================
INSERT INTO Activity_Part
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


-- =========================
-- DML/acitivity_mechanic_assignment_data.sql
-- =========================
INSERT INTO Activity_Mechanic_Assignment
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


-- =========================
-- DML/vehicle_driver_assignment_data.sql
-- =========================
INSERT INTO Vehicle_Driver_Assignment
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


-- =========================
-- DML/safety_event_data.sql
-- =========================
INSERT INTO Safety_Event
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
(5, 'D-302', 'VIN00000000000005', 4, '2026-02-19 07:50:00', 'Sudden Acceleration', 'High', 5488.60, 'Rapid acceleration observed during morning departure from depot.');

-- =========================
-- DML/Event_penalty_data.sql
-- =========================
INSERT INTO Event_Penalty
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


-- =========================
-- DML/predictive_alert_data.sql
-- =========================
INSERT INTO Predictive_Alert
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


-- =========================
-- DML/warranty_claims_data.sql
-- =========================
INSERT INTO Warranty_Claims
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
(5, 5, 'Pending', '2024-10-02', 'Oil Filter Replacement')

-- =========================
-- Procedure/inventory_procedures.sql
-- =========================
DELIMITER //

CREATE PROCEDURE sp_Register_Warranty_Claim(
    IN p_Activity_ID INT,
    IN p_Part_ID INT,
    IN p_Claim_Type VARCHAR(50)
)
BEGIN
    INSERT INTO Warranty_Claims (
        Activity_ID, 
        Part_ID, 
        Claim_Status, 
        Claim_Date, 
        Claim_Type
    )
    VALUES (
        p_Activity_ID, 
        p_Part_ID, 
        'Pending', 
        CURDATE(), 
        p_Claim_Type
    );
END //

CREATE PROCEDURE sp_Get_Optimal_Supplier(
    IN p_Part_Name_Keyword VARCHAR(100)
)
BEGIN
    SELECT 
        p.Part_Name,
        s.Supplier_Name,
        s.Phone_Number,
        ps.Supplier_Type,
        ps.Unit_Cost,
        ps.Lead_Time_Days
    FROM Part p
    JOIN Part_Supplier ps ON p.Part_ID = ps.Part_ID
    JOIN Supplier s ON ps.Supplier_ID = s.Supplier_ID
    WHERE p.Part_Name LIKE CONCAT('%', p_Part_Name_Keyword, '%')
    ORDER BY ps.Lead_Time_Days ASC, ps.Unit_Cost ASC;
END //

DELIMITER ;

-- Trigger_Auto_Create_Warranty_Claim
DELIMITER //

CREATE TRIGGER trg_Auto_Create_Warranty_Claim
AFTER INSERT ON Activity_Part
FOR EACH ROW
BEGIN
    DECLARE is_warranty BOOLEAN;

    SELECT Warranty_Indicator INTO is_warranty
    FROM Maintenance_Activity
    WHERE Activity_ID = NEW.Activity_ID;

    IF is_warranty = TRUE THEN
        INSERT INTO Warranty_Claims (
            Activity_ID, 
            Part_ID, 
            Claim_Status, 
            Claim_Date, 
            Claim_Type
        )
        VALUES (
            NEW.Activity_ID, 
            NEW.Part_ID, 
            'Pending', 
            CURDATE(), 
            'Auto-Generated (System)'
        );
    END IF;
END //

DELIMITER ;


-- =========================
-- Procedure/Procedure_and_Update_Monthly_Safety_Scores.sql
-- =========================
DELIMITER $$

CREATE PROCEDURE UpdateMonthlySafetyScores()
BEGIN

    DELETE FROM Driver_Safety_Score;

    INSERT INTO Driver_Safety_Score
    (
        Driver_ID,
        Month,
        Year,
        Score
    )
    SELECT
        d.Driver_ID,
        MONTH(se.Timestamp),
        YEAR(se.Timestamp),
        GREATEST(0,100-SUM(ep.Penalty_Points))
    FROM Safety_Event se
    JOIN Driver d
        ON se.Driver_ID=d.Driver_ID
    JOIN Event_Penalty ep
        ON se.Event_Type=ep.Event_Type
    GROUP BY
        d.Driver_ID,
        YEAR(se.Timestamp),
        MONTH(se.Timestamp);

END$$

DELIMITER ;

CALL UpdateMonthlySafetyScores();

-- =========================
-- Procedure/Procedures_With_Auth.sql
-- =========================
DELIMITER //
CREATE PROCEDURE sp_AssignDriverToVehicle(
    IN p_Staff_ID VARCHAR(20),    
    IN p_Driver_ID VARCHAR(10),
    IN p_VIN VARCHAR(17),
    IN p_Start_Date DATETIME
)
BEGIN
    DECLARE v_RoleType VARCHAR(50);
    DECLARE v_StaffDepot INT;
    DECLARE v_DriverDepot INT;

    SELECT Role_Type, Depot_ID INTO v_RoleType, v_StaffDepot
    FROM Staff WHERE Staff_ID = p_Staff_ID;

    SELECT Depot_ID INTO v_DriverDepot FROM Driver WHERE Driver_ID = p_Driver_ID;

    IF (v_RoleType = 'Head Manager') OR (v_RoleType = 'Driver Manager' AND v_StaffDepot = v_DriverDepot) THEN
        INSERT INTO Vehicle_Driver_Assignment (Driver_ID, VIN, Start_Date)
        VALUES (p_Driver_ID, p_VIN, p_Start_Date);
        SELECT 'Success: Driver assigned to vehicle.' AS Result;
    ELSE
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Permission Denied: You must be a Driver Manager of this Depot or Head Manager.';
    END IF;
END //

CREATE PROCEDURE sp_ApproveWarrantyClaim(
    IN p_Staff_ID VARCHAR(20),
    IN p_Claim_ID INT
)
BEGIN
    DECLARE v_RoleType VARCHAR(50);

    -- 1. Lấy Role của nhân viên
    SELECT Role_Type INTO v_RoleType FROM Staff WHERE Staff_ID = p_Staff_ID;

    -- 2. Kiểm tra quyền: Chỉ Inventory Manager hoặc Head Manager mới được duyệt
    IF (v_RoleType = 'Inventory Manager' OR v_RoleType = 'Head Manager') THEN
        UPDATE Warranty_Claims
        SET Claim_Status = 'Approved'
        WHERE Claim_ID = p_Claim_ID;
        SELECT CONCAT('Success: Warranty Claim ', p_Claim_ID, ' has been approved.') AS Result;
    ELSE
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Permission Denied: Only Inventory Manager can approve warranty claims.';
    END IF;
END //

CREATE PROCEDURE sp_ReorderPart(
    IN p_Staff_ID VARCHAR(20)
)
BEGIN
    DECLARE v_RoleType VARCHAR(50);
    
    SELECT Role_Type INTO v_RoleType FROM Staff WHERE Staff_ID = p_Staff_ID;

    IF (v_RoleType = 'Inventory Manager' OR v_RoleType = 'Head Manager') THEN
        SELECT 
            Part_ID, 
            Part_Name, 
            Part_Category, 
            Reorder_Level
        FROM Part
        ORDER BY Reorder_Level DESC;
    ELSE
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Permission Denied: Unauthorized access to inventory data.';
    END IF;
END //

DELIMITER ;


-- =========================
-- Views/inventory_views.sql
-- =========================
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


-- =========================
-- Views/View_Depot_Safety_Comparison.sql
-- =========================
CREATE VIEW vw_Depot_Safety_Comparison AS
SELECT
    dep.Depot_ID,
    dep.Location_Name,
    COUNT(DISTINCT d.Driver_ID) AS Total_Drivers,
    COUNT(se.Event_ID) AS Total_Incidents,
    ROUND(AVG(ds.Score),2) AS Average_Safety_Score
FROM Depot dep
LEFT JOIN Driver d
    ON dep.Depot_ID=d.Depot_ID
LEFT JOIN Driver_Safety_Score ds
    ON d.Driver_ID=ds.Driver_ID
LEFT JOIN Safety_Event se
    ON d.Driver_ID=se.Driver_ID
GROUP BY
    dep.Depot_ID,
    dep.Location_Name;
    
SELECT * FROM vw_Depot_Safety_Comparison;

-- =========================
-- Views/View_High_Risk_Drivers.sql
-- =========================
CREATE VIEW vw_High_Risk_Drivers AS
SELECT
    d.Driver_ID,
    d.Full_Name,
    dep.Location_Name,
    ds.Month,
    ds.Year,
    ds.Score
FROM Driver_Safety_Score ds
JOIN Driver d
    ON ds.Driver_ID=d.Driver_ID
JOIN Depot dep
    ON d.Depot_ID=dep.Depot_ID
WHERE ds.Score<=50;

SELECT * FROM vw_High_Risk_Drivers;
