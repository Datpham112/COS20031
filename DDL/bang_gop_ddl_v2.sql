SET FOREIGN_KEY_CHECKS = 0;
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
create table Depot (
	Depot_ID int auto_increment primary key,
	Location_Name varchar(255) not null
);

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
);CREATE TABLE Event_Penalty (
    Event_Type VARCHAR(50) PRIMARY KEY,
    Penalty_Points INT NOT NULL,

    CONSTRAINT chk_penalty_points
        CHECK (Penalty_Points BETWEEN 0 AND 100)
);CREATE TABLE Maintenance_Activity (
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
CREATE TABLE Mechanic (
    Mechanic_ID INT AUTO_INCREMENT PRIMARY KEY,
    Workshop_ID INT NOT NULL,
    Full_Name VARCHAR(100) NOT NULL,

    CONSTRAINT fk_mechanic_workshop
        FOREIGN KEY (Workshop_ID)
        REFERENCES Workshop(Workshop_ID)
);
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
CREATE TABLE Mechanic_Cert_History (
    Cert_ID INT PRIMARY KEY,
    Mechanic_ID INT NOT NULL,
    Certificate_Name VARCHAR(255),
    issue_Date DATE,
    Expiry_Date DATE,

    CONSTRAINT fk_mechanic_cert_history_mechanic
        FOREIGN KEY (Mechanic_ID)
        REFERENCES Mechanic(Mechanic_ID)
);CREATE TABLE Part (
    Part_ID INT PRIMARY KEY AUTO_INCREMENT,
    Part_Name VARCHAR(100) NOT NULL,
    Part_Category VARCHAR(50) NULL,
    Brand VARCHAR(50) NULL,
    Unit_Price DECIMAL(10,2) CHECK (Unit_Price >= 0),
    Reorder_Level INT CHECK (Reorder_Level >= 0)
);CREATE TABLE Part_Supplier (
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
);CREATE TABLE Predictive_Alert (
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
);CREATE TABLE Supplier (
    Supplier_ID INT PRIMARY KEY AUTO_INCREMENT,
    Supplier_Name VARCHAR(150) NOT NULL,
    Contact_Name VARCHAR(100) NULL,
    Phone_Number VARCHAR(15) UNIQUE NOT NULL,
    Email_Address VARCHAR(100) NULL,
    Address VARCHAR(255) NULL,
    Delivery_Time DATETIME NULL
);create table Vehicle (
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
);CREATE TABLE Workshop (
    Workshop_ID INT AUTO_INCREMENT PRIMARY KEY,
    Depot_ID INT NOT NULL UNIQUE,

    CONSTRAINT fk_workshop_depot
        FOREIGN KEY (Depot_ID)
        REFERENCES Depot(Depot_ID)
);

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
SET FOREIGN_KEY_CHECKS = 1;