create table mechanic_cert_history (
	Cert_ID int not null,
	Mechanic_ID int not null,
	Certificate_Name varchar(255) default null,
	issue_Date date default null,
	Expiry_Date date default null,

	primary key (Cert_ID),

	constraint fk_mechanic_cert_history_mechanic
		foreign key (Mechanic_ID)
		references Mechanic(Mechanic_ID)
);