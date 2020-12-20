<?php
include_once("navBar.php");
include_once("SearchBar.php");
include_once("SqlConnection.php");
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "myDB";
$output = '';
$formationName = $_REQUEST;
$conn = new mysqli($servername, $username, $password, $dbname);

$newText = $_POST['newText'];

if ($newText != ""){
    echo "yes";
    $sql = "UPDATE formation SET name = '".$newText."' WHERE name LIKE '%$formationName[formation]%'";
    $result = mysqli_query($conn,$sql);
}
