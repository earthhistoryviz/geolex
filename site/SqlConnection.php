<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "myDB";
$output = '';

$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
else{
    //echo '<pre>'.'successfully linked to Database'.'</pre>';
}

function getAllFormations() {
  global $conn;
  $sql = "SELECT * FROM formation";
  $result = mysqli_query($conn, $sql);
  if (!$result) throw new RuntimeException("Failed to execute query: $sql; Error was: ".mysqli_error($conn));
  $formations = array();
  while ($row = mysqli_fetch_assoc($result)) {
    array_push($formations, $row);
  }
  return $formations;
}

function updateFormationAges($formationname, $newtop, $newbase) {
  global $conn;
  $sql = "UPDATE formation SET beg_date=\"$newbase\", end_date=\"$newtop\" WHERE name=\"$formationname\"";
  return mysqli_query($conn, $sql);
}
