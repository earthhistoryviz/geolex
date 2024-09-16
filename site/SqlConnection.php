<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "myDB";
$output = '';

global $conn;
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    //echo '<pre>'.'successfully linked to Database'.'</pre>';
}

function getAllFormations()
{
    global $conn;
    $sql = "SELECT * FROM formation";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        throw new RuntimeException("Failed to execute query: $sql; Error was: ".mysqli_error($conn));
    }
    $formations = array();
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($formations, $row);
    }
    return $formations;
}


function removeUnknownFormations()
{
    global $conn;

    $sql = "DELETE FROM formation WHERE name IN ('?', 'Unknown', 'No Evidence', 'Uncertain', 'unknown', 'no evidence', 'uncertain', 'No evidence')";


    if (mysqli_query($conn, $sql)  !== true) {
        echo "-------------------------------------------------\n\n<br><br>";
        echo "<br><br>";
        echo "Error removing the unknown formations" . "<br>";
        echo "-------------------------------------------------\n\n<br><br>";

    }

}

function updateFormationAges($formationname, $newtop, $newbase)
{
    global $conn;
    $sql = "UPDATE formation SET beg_date=\"$newbase\", end_date=\"$newtop\" WHERE name=\"$formationname\"";
    return mysqli_query($conn, $sql);
}

//function to insert the formations in the TSC dataPack into the database
function timeScaleMapPackUpload($formation_set)
{
    global $conn;

    $error = false; //did an error occur?

    foreach($formation_set as $formation_tobe_inserted) {


        $f_name = str_replace("\"", "", $formation_tobe_inserted->name);
        $f_name = mysqli_real_escape_string($conn, $f_name);
        $f_geojson = mysqli_real_escape_string($conn, $formation_tobe_inserted->GeoJSON);
        $f_long_lat =  mysqli_real_escape_string($conn, $formation_tobe_inserted->long_lat);



        //if formation is not in the sql database already
        $sqlChecker = "SELECT * FROM formation WHERE name=\"$f_name\"";
        // echo '<pre>'."HERES THE SQL QUERY".'</pre>';
        // echo '<pre>'.$sqlChecker.'</pre>';

        $result = mysqli_query($conn, $sqlChecker);
        $count = mysqli_num_rows($result);

        if($count == 0) { //if formation is not in the sql database already
            $f_beg_date =  mysqli_real_escape_string($conn, $formation_tobe_inserted->begin_date);
            $f_end_date =  mysqli_real_escape_string($conn, $formation_tobe_inserted->end_date);
            $f_lithology_pattern =  mysqli_real_escape_string($conn, $formation_tobe_inserted->lithology_pattern);
            $f_lithology =  mysqli_real_escape_string($conn, $formation_tobe_inserted->formation_description);
            $f_province =  mysqli_real_escape_string($conn, $formation_tobe_inserted->province);

            $f_frac_upB =  mysqli_real_escape_string($conn, $formation_tobe_inserted->frac_upB);
            $f_frac_upE =  mysqli_real_escape_string($conn, $formation_tobe_inserted->frac_upE);
            $f_beginning_stage =  mysqli_real_escape_string($conn, $formation_tobe_inserted->beginning_stage);
            $f_end_stage =  mysqli_real_escape_string($conn, $formation_tobe_inserted->end_stage);

            $f_period = mysqli_real_escape_string($conn, $formation_tobe_inserted->period);
            $f_type_locality = mysqli_real_escape_string($conn, $formation_tobe_inserted->type_locality);

            //all the blanks are so that sql does not insert a null value into the database, or else our queries will not work because
            //null != empty string
            $sql = "INSERT INTO formation VALUES  ('$f_name', '$f_period', '','$f_province','$f_type_locality','$f_lithology','$f_lithology_pattern','','','','','','','$f_long_lat',
        '','$f_geojson', '', '$f_beginning_stage' , '$f_frac_upB', '$f_beg_date', '$f_end_stage', '$f_frac_upE', '$f_end_date','')";


            //UNCOMMENT HERE
            if (mysqli_query($conn, $sql)  === true) {
                echo "<br><br>";
                echo "-------------------------------------------------\n\n<br><br>";
                echo "Parsed $f_name into database..." . "<br>";

            } else {
                echo "-------------------------------------------------\n\n<br><br>";
                echo "<br><br>";
                echo "Error for inserting $f_name into database..." . "<br>";
                $error = true;
            }

        } else {
            //if formation is in the sql database already, need to check geoJSON
            $sqlChecker = "SELECT * FROM formation WHERE name=\"$f_name\" AND geojson IS NULL";


            $result = mysqli_query($conn, $sqlChecker);
            $count = mysqli_num_rows($result);
            if($count != 0) {
                $sql = "UPDATE formation SET geojson=\"$f_geojson\", additional_info=\"$f_long_lat\" WHERE name=\"$f_name\"";

                //UNCOMMENT HERE
                if (mysqli_query($conn, $sql)  === true) {
                    echo "Formation $f_name's geoJSON was originally NULL, it has now been updated to be a diamond, additional info has now been included to have lat and long" ;
                } else {
                    echo "-------------------------------------------------\n\n<br><br>";
                    echo "<br><br>";
                    echo "Error for updating $f_name's geojson..." . "<br>";
                    $error = true;
                }
            }
        }

    }

    removeUnknownFormations();

    if ($error) {
        echo "-------------------------------------------------\n\n<br><br>";
        echo "An error occured";
    } else {
        echo "-------------------------------------------------\n\n<br><br>";
        echo "Every Formation was parsed";
    }

    return ;

}
