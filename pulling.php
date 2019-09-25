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
/*$sql = "DROP DATABASE IF EXISTS myDB";
if ($conn->query($sql) === TRUE) {
    echo "Database dropped successfully<br>";
} else {
    echo "Error droping database: " . $conn->error;
}

$conn->close();
*/
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
$sql2 = "CREATE TABLE formation(
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
)";

if ($conn->query($sql)&&$conn->query($sql2) === TRUE) {
    echo "table create successfully<br>";
} else {
    echo "Error creating table: " . $conn->error;
}


$sql = "INSERT INTO timeperiod(name,color)
VALUES
('Devonian',
	'203/140/55'
),
('Quaternary',
	'249/249/127'
),
(	'Neogene',
	'255/230/25'
)
";
//$test = "shdsabdjasjdsjajdja";
//$sql2 = "INSERT INTO formation(name) VALUES ('$test')";
$sql2 = "INSERT INTO formation(name,period,age_interval,province,type_locality,lithology,lower_contact,
upper_contact,regional_extent,fossils,age,depositional,additional_info,compiler)
VALUES
(
	'A’ertaxi Gr',
	'Devonian',
	'D22 (15); Givetian (late Middle Devonian)',
	'Xinjiang',
	'The type section is located at south of A’ertaxi Village, north side of Kunlun Mts. in the Xinjiang Uygur Autonomous Region. . It was named by No 13 Geological Team of Xinjiang in 1957 and was published by Editorial Board of Xinjiang Regional Stratigraphical Scale (1980).',
	'Limestone, shale. The lower part of the Group is dominated by light-gray, dark-gray limestone and clayey shale, containing coral fossils. The upper part is characterized by green and black shale and dark-gray limestone with breccia limestone on its top. The thickness is 870 m. In the high mountain area between Longle-Agar River valley and Genlishalihe River, the group is characterized by gray, light greenish-gray quartzose sandstone, 260 to 900 m thick',
	'Unknown: The contact relationships to the underlying strata are not yet clear.',
	'Unknown: The contact relationships to the overlying strata are not yet clear.',
	' ',
	'Coral fossils: Eudophyllum sp., Brariphyllum sp., Syringopora sp., Temnophyllum sp.',
	'Givetian (late Middle Devonian)',
	' ',
	' ',
	'Wang Shitao'
),
(
	'A’ertaxi Gr',
	'Devonian',
	'D22 (15); Givetian (late Middle Devonian)',
	'Xinjiang',
	'The type section is located at south of A’ertaxi Village, north side of Kunlun Mts. in the Xinjiang Uygur Autonomous Region. . It was named by No 13 Geological Team of Xinjiang in 1957 and was published by Editorial Board of Xinjiang Regional Stratigraphical Scale (1980).',
	'Limestone, shale. The lower part of the Group is dominated by light-gray, dark-gray limestone and clayey shale, containing coral fossils. The upper part is characterized by green and black shale and dark-gray limestone with breccia limestone on its top. The thickness is 870 m. In the high mountain area between Longle-Agar River valley and Genlishalihe River, the group is characterized by gray, light greenish-gray quartzose sandstone, 260 to 900 m thick',
	'Unknown: The contact relationships to the underlying strata are not yet clear.',
	'Unknown: The contact relationships to the overlying strata are not yet clear.',
	' ',
	'Coral fossils: Eudophyllum sp., Brariphyllum sp., Syringopora sp., Temnophyllum sp.',
	'Givetian (late Middle Devonian)',
	' ',
	' ',
	'Wang Shitao2'
)";

if ($conn->query($sql)&&$conn->query($sql2) === TRUE) {
    echo "Insert successfully<br>";
} else {
    echo "Error insert: " . $conn->error;
}


/*$sql = "SELECT * FROM timeperiod";
$result = mysqli_query($conn,$sql);
 while ($row = mysqli_fetch_array($result))
            {
                $name  = $row['name'];
                echo "name = $name<br>";
                $color = $row['color'];
                echo "color = $color<br>";
            }*/
$sql = "SELECT * FROM formation";
$result = mysqli_query($conn,$sql);
 while ($row = mysqli_fetch_array($result))
            {
                $name  = $row['name'];
                echo "name = $name<br>";
                $period = $row['period'];
                echo "period = $period<br>";
                $age_interval = $row['age_interval'];
                echo "age_interval = $age_interval<br>";
                $province = $row['province'];
                echo "province = $province<br>";
                $type_locality = $row['type_locality'];
                echo "type_locality = $type_locality<br>";
                $lithology = $row['lithology'];
                echo "lithology = $lithology<br>";
                $lower_contact = $row['lower_contact'];
                echo "lower_contact = $lower_contact<br>";
                $upper_contact = $row['upper_contact'];
                echo "upper_contact = $upper_contact<br>";
                $regional_extent = $row['regional_extent'];
                echo "regional_extent = $regional_extent<br>";
                $fossils = $row['fossils'];
                echo "fossils = $fossils<br>";
                $depositional = $row['depositional'];
                echo "depositional = $depositional<br>";
                $additional_info = $row['additional_info'];
                echo "additional_info = $additional_info<br>";
                $compiler = $row['compiler'];
                echo "compiler = $compiler<br><br><br>";
            }

$conn->close();
?>
</body>

</html>
