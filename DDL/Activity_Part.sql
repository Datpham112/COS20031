create table activity_part (
	Activity_ID int not null,
	Part_ID int not null,
	Quantity_Used int default null,
	Unit_Cost decimal(10,2) default null,
	Total_Cost decimal(10,2) default null,

	primary key (Activity_ID, Part_ID),

	key Part_ID (Part_ID),

	constraint fk_activity_part_part
		foreign key (Part_ID)
		references part(Part_ID),

	constraint chk_quantity_used
		check (Quantity_Used > 0),

	constraint chk_unit_cost
		check (Unit_Cost >= 0),

	constraint chk_total_cost
		check (Total_Cost >= 0)
);