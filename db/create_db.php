<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "myDB";


// create connection
$conn = new mysqli($servername, $username, $password);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$sql8 = "USE myDB";
if ($conn->query($sql8) === TRUE) {
    echo "\n Database Already Exists...Dropping Tables and Database to rebuild them.<br>";
} else {
    echo "\n Database does not exist, rebuilding from scratch, ignore errors about dropping database " . $conn->error;
}
$sql5 = "DROP TABLE IF EXISTS user_info";
if ($conn->query($sql5) === TRUE) {
    echo "\n Table user_info dropped successfully<br>";
} else {
    echo "\n Error dropping table user_info: " . $conn->error;
}
$sql6 = "DROP TABLE IF EXISTS formation";
if ($conn->query($sql6) === TRUE) {
    echo "Table formation dropped successfully<br>";
} else {
    echo "\n Error dropping table formation: " . $conn->error;
}
$sql7 = "DROP TABLE IF EXISTS timeperiod";
if ($conn->query($sql7) === TRUE) {
    echo "Table timeperiod dropped successfully<br>";
} else {
    echo "\n Error dropping table formation: " . $conn->error;
}
$sql8 = "DROP TABLE IF EXISTS images";
if ($conn->query($sql7) === TRUE) {
    echo "Table images dropped successfully<br>";
} else {
    echo "\n Error dropping table formation: " . $conn->error;
}
// drop database
$sql = "DROP DATABASE IF EXISTS myDB";
if ($conn->query($sql) === TRUE) {
    echo " \n Database dropped successfully<br>";
} else {
    echo "\n Error dropping database: " . $conn->error;
}

$conn->close();

// Create connection
$conn = new mysqli($servername, $username, $password);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE myDB";
if ($conn->query($sql) === TRUE) {
    echo "\n Database created successfully<br>";
} else {
    echo "\n Error creating database: " . $conn->error;
}

$conn->close();

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);

}
//$sql = "DROP DATABASE IF EXISTS myDB";
//$sql = "CREATE DATABASE myDB";
//$sql = "USE  myDB";

$sql = "CREATE TABLE timeperiod(
	ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name Varchar(255),
	color Varchar(255)

)";
if ($conn->query($sql)===TRUE) {
    echo "table create successfully<br>";
} else {
    echo "\n Error creating timeperiod table: " . $conn->error;
}
$sql4 = "CREATE TABLE user_info(
    ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    uname Varchar(255),
    pasw Varchar(255),
    admin Varchar(255)
)";
$rootpasw = password_hash("TSCreator",PASSWORD_DEFAULT);
$sql3 = "INSERT INTO user_info(uname,pasw,admin)
VALUES
('root', '$rootpasw','True')";
if ($conn->query($sql4)==TRUE && $conn->query($sql3)===TRUE) {
    echo "table create successfully<br>";
} else {
    echo "\n Error creating user_info table: " . $conn->error;
}
$sql2 = "CREATE TABLE formation (
	name Varchar(255) PRIMARY KEY NOT NULL,
	period Varchar(255),
	age_interval text,
	province Varchar(255),
 	type_locality Text,
	lithology Text,
        lithology_pattern Varchar(255),
	lower_contact Text,
	upper_contact Text,
	regional_extent Text,
	fossils Text,
	age Text,
	depositional Text,
	additional_info Text,
	compiler text,
	geojson longtext,
        age_span varchar(255),
        beginning_stage varchar(255),
        frac_upB varchar(255),
        beg_date varchar(255),
        end_stage varchar(255),
        frac_upE varchar(255),
	end_date varchar(255),
	depositional_pattern varchar(255)
)";

if ($conn->query($sql2)===TRUE) {
    echo "table formation created successfully<br>";
} else {
    echo "\n Error creating formation table: " . $conn->error;
}
?>

