<?php

include_once("SqlConnection.php");
include_once("adminDash.php");
$title = $_POST["name"];
echo $title;
//$sql = "DELETE FROM formation WHERE name = '$title';";
$sql = sprintf("DELETE FROM formation WHERE name='%s'", mysqli_real_escape_string($conn, $title));
echo "<pre> $sql";

if($conn->query($sql) === true) {
    //echo "data deleted";
} else {
    //echo "error during deletion";
}
