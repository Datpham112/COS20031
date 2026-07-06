create table warranty_claims (
	Claim_ID int not null auto_increment,
	Activity_ID int default null,
	Part_ID int default null,
	Claim_Status varchar(50) default null,
	Claim_Date date not null,
	Claim_Type varchar(50) default null,

	primary key (Claim_ID),

	key Part_ID (Part_ID),

	constraint fk_warranty_claims_part
		foreign key (Part_ID)
		references part(Part_ID),

	constraint chk_claim_status
		check (Claim_Status in ('Pending', 'Approved', 'Rejected'))
);