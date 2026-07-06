create table part (
	Part_ID int not null auto_increment,
	Part_Name varchar(100) not null,
	Part_Category varchar(50) default null,
	Brand varchar(50) default null,
	Unit_Price decimal(10,2) default null,
	Reorder_Level int default null,

	primary key (Part_ID),

	constraint chk_unit_price
		check (Unit_Price >= 0),

	constraint chk_reorder_level
		check (Reorder_Level >= 0)
);