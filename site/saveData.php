<?php
include("navBar.php");
include("SearchBar.php");
include("SqlConnection.php");

$vars = array(
  'name' => '',
  'period' => '',
  'age_interval' => '',
  'province' => '',
  'type_locality' => '',
  'lithology' => '',
  'lower_contact' => '',
  'upper_contact' => '',
  'regional_extent' => '',
  'fossils' => '',
  'age' => '',
  'depositional' => '',
  'additional_info' => '',
  'compiler' => '',
);


foreach ($vars as $vname => $vval) {
  $vars[$vname] = mysqli_real_escape_string($conn,$_POST[$vname]);
}

$sql = "UPDATE formation SET ";

$firstone = true;
foreach ($vars as $vname => $vval) {
  if (!$firstone) {
    $sql .= ", \n"; // only put the commas on before the stuff that isn't the first one
  }
  $firstone = false;
  $sql .= "$vname = '$vval'";
}
$sql .= " \nWHERE name = '".$vars["name"]."';";

if ($conn->query($sql) === TRUE) {
  // worked
  header("location: displayInfo.php?formation=".$vars["name"]);
} else {
  // failed
  echo "Error, could not update data, error was: " . $conn->error;
}
?>
