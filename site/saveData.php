<?php

include_once("SqlConnection.php");

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
  'geojson' => '',
  'fossils' => '',
  'age' => '',
  'age_span' => '',
  'beginning_stage' => '',
  'frac_upB' => '',
  'beg_date' => '',
  'end_stage' => '',
  'frac_upE' => '',
  'end_date' => '',
  'fossils' => '',
  'age' => '',
  'depositional' => '',
  'additional_info' => '',
  'compiler' => '',
);




foreach ($vars as $vname => $vval) {
    $vars[$vname] = mysqli_real_escape_string($conn, $_POST[$vname]);
}

$sql = "UPDATE formation SET ";

$firstone = true;
foreach ($vars as $vname => $vval) {
    if (!$firstone) {
        $sql .= ", \n"; // only put the commas on before the stuff that isn't the first one
    }
    $firstone = false;
    if ($vname == 'geojson') {
        $position = strpos($vval, '{');
        $lastPosition = strrpos($vval, '}');
        if ($position !== false && $lastPosition !== false) {
            $length = $lastPosition - $position + 1;
            $vval = substr($vval, $position, $length);
        } else {
            // '{' not found, handle accordingly
            $vval = $vval;
        }
    }
    $sql .= "$vname = '$vval'";
}
$sql .= " \nWHERE name = '".$vars["name"]."';";

if ($conn->query($sql) === true) {
    // worked
    header("location: adminDisplayInfo.php?formation=".$vars["name"]);
} else {
    // failed
    echo "Error, could not update data, error was: " . $conn->error;
}
