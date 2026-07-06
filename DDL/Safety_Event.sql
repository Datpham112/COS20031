create table safety_event (
	Event_ID int not null,
	Driver_ID int default null,
	VIN varchar(17) default null,
	Depot_ID int default null,
	Timestamp datetime default null,
	Event_Type varchar(50) default null,
	Severity_Level varchar(20) default null,
	Odometer_At_Event decimal(10,2) default null,
	Review_Comments text,

	primary key (Event_ID)
);