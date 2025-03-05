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
$directory = "./db";
$zipFiles = glob($directory . "/*.zip");
$files = scandir($directory);
if (!empty($zipFiles)) {
    $zip = new ZipArchive();
    $zipFilePath = $zipFiles[0];

    if ($zip->open($zipFilePath) === true) {
        $tempDirectory = "./db/tempdir";
        mkdir($tempDirectory, 0777);
        $zip->extractTo($tempDirectory);
        $zip->close();
        echo "\nExtracted zip file.";

        $sqlFiles = glob($tempDirectory . "/*.sql");
        if (!empty($sqlFiles)) {
            $sqlFilePath = $sqlFiles[0];
            $sqlContent = file_get_contents($sqlFilePath);
            if ($conn->multi_query($sqlContent)) {
                do {

                } while ($conn->next_result());
                echo "\nDatabase populated successfully with {$sqlFilePath}.";
                $truncateSql = "TRUNCATE TABLE user_info;";
                if ($conn->query($truncateSql) === true) {
                    echo "\nUser table cleared successfully.";

                    $insertSql = $conn->prepare("INSERT INTO user_info (uname, pasw, admin) VALUES (?, ?, 'True');");
                    $passwordLexDDE = password_hash("Hangzhou", PASSWORD_DEFAULT);
                    $passwordCAGS = password_hash("ChinaStrat", PASSWORD_DEFAULT);

                    $insertSql->bind_param("ss", $userName, $password);
                    $userName = "LexDDE";
                    $password = $passwordLexDDE;
                    $insertSql->execute();
                    echo "\nUser LexDDE inserted successfully.";

                    $userName = "CAGS";
                    $password = $passwordCAGS;
                    $insertSql->execute();
                    echo "\nUser CAGS inserted successfully.";

                    $insertSql->close();
                } else {
                    echo "\nError clearing user table: " . $conn->error;
                }
            } else {
                echo "\nError occurred while populating the database.";
            }
        } else {
            echo "\nNo .sql file found in extracted contents.";
        }
        $cmd = "rm $tempDirectory/*.sql && cp -r $tempDirectory/* ../app && rm -rf $tempDirectory";
        exec($cmd, $output, $retval);
        if ($retval == 0) {
            echo "\nSuccesfully moved from zip file.";
        } else {
            echo "\nFailed to move files with status $retval and output:";
            print_r($output);
        }
        echo "\nDone.";
        echo "\n";
    } else {
        echo "Failed to open zip file.";
    }
} else {
    echo "\nNo zip file found in directory.";
    $allTablesExist = true;
    $tables = ["user_info", "formation"];
    foreach ($tables as $table) {
        $sql = "SELECT COUNT(*) AS count FROM information_schema.tables WHERE table_schema = '$dbname' AND table_name = '$table'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        if ($row['count'] == 0) {
            $allTablesExist = false;
            echo "\nTable $table does not exist.";
            break;
        } else {
            echo "\nTable $table does exist.";
        }
    }
    if ($allTablesExist) {
        echo "\nThe database is already formatted. Done.";
        echo "\n";
    } else {
        echo "\nReformating database.";
        $conn->query("USE myDB"); // Switch to the database
        // Drop tables if they exist
        foreach ($tables as $table) {
            $sql = "DROP TABLE IF EXISTS $table";
            if ($conn->query($sql) === true) {
                echo "\nTable $table dropped successfully";
            } else {
                echo "\nError dropping table $table: " . $conn->error;
            }
        }

        // Drop and recreate the database
        $sql = "DROP DATABASE IF EXISTS myDB";
        if ($conn->query($sql) === true) {
            echo "\nDatabase dropped successfully";
        } else {
            echo "\nError dropping database: " . $conn->error;
        }

        $sql = "CREATE DATABASE myDB";
        if ($conn->query($sql) === true) {
            echo "\nDatabase created successfully";
        } else {
            echo "\nError creating database: " . $conn->error;
        }

        // Reconnect to the new database
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("\nConnection failed: " . $conn->connect_error);
        }

        // Create tables
        $createTables = [
            "CREATE TABLE user_info (
                ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
                uname Varchar(255),
                pasw Varchar(255),
                admin Varchar(255)
            )",
            "CREATE TABLE formation (
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
            )"
        ];

        foreach ($createTables as $sql) {
            if ($conn->query($sql) === true) {
                echo "\nTable created successfully";
            } else {
                echo "\nError creating table: " . $conn->error;
            }
        }

        // Insert default user
        $rootpasw = password_hash("TSCreator", PASSWORD_DEFAULT);
        $sql = "INSERT INTO user_info (uname, pasw, admin) VALUES ('root', '$rootpasw', 'True')";
        if ($conn->query($sql) === true) {
            echo "\nDefault user created successfully";
        } else {
            echo "\nError inserting default user: " . $conn->error;
        }

        // Create directories and set permissions
        $cmd = "mkdir -p app/timescales && mkdir -p app/uploads && chown www-data:www-data app/timescales && chown www-data:www-data app/uploads";
        exec($cmd, $output, $retval);
        if ($retval == 0) {
            echo "\nSuccessfully created timescales directory.";
        } else {
            echo "\nFailed to create timescales directory with status $retval and output:" . print_r($output, true);
        }
        echo "\nDone.";
        echo "\n";
    }
}

$conn->close();
