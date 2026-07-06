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
