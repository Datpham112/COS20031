create database fleet_management;
use fleet_management;

create table Depot (
	Depot_ID int auto_increment primary key,
	Location_Name varchar(255) not null
);

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

CREATE TABLE Driver (
    Driver_ID INT AUTO_INCREMENT PRIMARY KEY,
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
        CHECK (
            Employment_Status IN ('Active','On Leave','Suspended','Terminated'))
);
    
    

