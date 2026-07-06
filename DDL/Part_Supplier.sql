create table part_supplier (
	Part_ID int not null,
	Supplier_ID int not null,
	Supplier_Type varchar(50) default null,
	Unit_Cost decimal(10,2) default null,
	Lead_Time_Days int default null,

	primary key (Part_ID, Supplier_ID),

	key Supplier_ID (Supplier_ID),

	constraint fk_part_supplier_part
		foreign key (Part_ID)
		references part(Part_ID),

	constraint fk_part_supplier_supplier
		foreign key (Supplier_ID)
		references supplier(Supplier_ID),

	constraint chk_unit_cost
		check (Unit_Cost >= 0),

	constraint chk_lead_time_days
		check (Lead_Time_Days >= 0)
);