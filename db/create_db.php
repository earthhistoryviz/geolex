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
    echo "Database Already Exists...Dropping Tables and Database to rebuild them.";
} else {
    echo "Database does not exist, rebuilding from scratch, ignore errors about dropping database " . $conn->error;
}
$sql5 = "DROP TABLE IF EXISTS user_info";
if ($conn->query($sql5) === TRUE) {
    echo "Table user_info dropped successfully";
} else {
    echo "Error dropping table user_info: " . $conn->error;
}
$sql6 = "DROP TABLE IF EXISTS formation";
if ($conn->query($sql6) === TRUE) {
    echo "Table formation dropped successfully";
} else {
    echo "Error dropping table formation: " . $conn->error;
}
$sql7 = "DROP TABLE IF EXISTS timeperiod";
if ($conn->query($sql7) === TRUE) {
    echo "Table timeperiod dropped successfully";
} else {
    echo "Error dropping table formation: " . $conn->error;
}
$sql8 = "DROP TABLE IF EXISTS images";
if ($conn->query($sql7) === TRUE) {
    echo "Table images dropped successfully";
} else {
    echo "Error dropping table formation: " . $conn->error;
}
// drop database
$sql = "DROP DATABASE IF EXISTS myDB";
if ($conn->query($sql) === TRUE) {
    echo "Database dropped successfully";
} else {
    echo "Error dropping database: " . $conn->error;
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
    echo "Database created successfully";
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
if ($conn->query($sql)===TRUE) {
    echo "Timeperiod Table created successfully";
} else {
    echo "Error creating timeperiod table: " . $conn->error;
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
    echo "Users Table created successfully";
} else {
    echo "Error creating user_info table: " . $conn->error;
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
    echo "Formation Table created successfully";
} else {
    echo " Error creating formation table: " . $conn->error;
}

$directory = "./code";
$zipFiles = glob($directory . "/*.zip");
$files = scandir($directory);
if (!empty($zipFiles)) {
    $zip = new ZipArchive();
    $zipFilePath = $zipFiles[0];

    if ($zip->open($zipFilePath) === TRUE) {
        $tempDirectory = "./code/tempdir";
        mkdir($tempDirectory, 0777);
        $zip->extractTo($tempDirectory);
        $zip->close();
        echo "Extracted zip file.";

        $sqlFiles = glob($tempDirectory . "/*.sql");
        if (!empty($sqlFiles)) {
            $sqlFilePath = $sqlFiles[0];
            $sqlContent = file_get_contents($sqlFilePath);
            if ($conn->multi_query($sqlContent)) {
                do {

                } while ($conn->next_result());
                echo "Database populated successfully with {$sqlFilePath}.";
            } else {
                echo "Error occurred while populating the database.";
            }
        } else {
            echo "No .sql file found in extracted contents.";
        }
        $cmd = "rm $tempDirectory/*.sql && cp -r $tempDirectory/* app && rm -rf $tempDirectory";
        exec($cmd, $output, $retval);
        if ($retval == 0) {
            echo "Succesfully moved files.";
        } else {
            echo "Failed to move files with status $retval and output:";
            print_r($output);
        }
    } else {
        echo "Failed to open zip file.";
    }
} else {
    $cmd = "mkdir app/timescales && chown www-data timescales";
    exec($cmd, $output, $retval);
    echo "No zip file found in directory.";
}

$conn->close();