create table supplier (
	Supplier_ID int not null auto_increment,
	Supplier_Name varchar(150) not null,
	Contact_Name varchar(100) default null,
	Phone_Number varchar(15) not null,
	Email_Address varchar(100) default null,
	Address varchar(255) default null,
	Delivery_Time datetime default null,

	primary key (Supplier_ID),

	unique key Phone_Number (Phone_Number)
);