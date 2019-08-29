<html>

<head>
<title>PullFromDB</title>
</head>
	
<body>
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

// drop database
$sql = "DROP DATABASE myDB";
if ($conn->query($sql) === TRUE) {
    echo "Database dropped successfully<br>";
} else {
    echo "Error droping database: " . $conn->error;
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
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error;
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
if ($conn->query($sql) === TRUE) {
    echo "table create successfully<br>";
} else {
    echo "Error creating table: " . $conn->error;
}
$conn->close();
/*$sql = "CREATE TABLE formation(
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
)";*/
//$sql .= "USE myDB";
//$sql .= "TRUNCATE TABLE timeperiod";
//$sql = "TRUNCATE TABLE formation";
//$sql = "TRUNCATE TABLE wells";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$sql = "INSERT INTO timeperiod(name,color)
VALUES
(	'Devonian',
	'203/140/55'
)";
if ($conn->query($sql) === TRUE) {
    echo "Insert successfully<br>";
} else {
    echo "Error insert: " . $conn->error;
}
$conn->close();
/*$sql .= "INSERT INTO timeperiod(name,color)
VALUES
(	'Quaternary',
	'249/249/127'
)";

$sql = "INSERT INTO timeperiod(name,color)
VALUES
(	'Neogene',
	'255/230/25'
)";*/

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
$sql = "SELECT * FROM timeperiod";
$result = mysql_query($sql);
 while ($row = mysql_fetch_array($result))
            {

                $Name  = $row['name'];
                $color = $row['color'];
            }
//if ($result->num_rows > 0) {
    // output data of each row
    //while($row = $result) {
        //echo "$row";
        echo $result['name'];
        echo $result['color'];
    //}
//} else {
    //echo "0 results";
//}
$conn->close();
?>
</body>

</html>