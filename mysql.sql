DROP DATABASE IF EXISTS myDB;
CREATE DATABASE myDB;
USE  myDB;

CREATE TABLE timeperiod(
	ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name Varchar(255),
	color Varchar(255)

);

CREATE TABLE formation(
	ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name Varchar(255),
	period Varchar(255),
	age_interval Varchar(255),
	province Varchar(255),
 	type_locality Text,
	lithology Text,
	lower_contact Text,
	upper_contact Text,
	regional_extent Text,
	fossils Text,
	age Text,
	depositional Text,
	additional_info Text,
	compiler Varchar(255)
);	
