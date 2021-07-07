<?php
// This code is needed and commented out only because it is causing a bug.

include_once("TimescaleLib.php");

$timescale = parseDefaultTimescale();
$periodsDate = array(); // TODO Need to know why: if use variable name '$periods', the entire algorithm breaks - the selection list will only have 0-12 as numbers instead of the actual period names.

foreach($timescale as $stage) {
  if (!array_key_exists($stage["period"], $periodsDate)) {
    $periodsDate[$stage["period"]] = array(
      "begDate" => $stage["base"],
      "endDate" => $stage["top"],
      "period" => $stage["period"]
    );
  } else {
    if ($periodsDate[$stage["period"]]["begDate"] < $stage["base"]) {
      $periodsDate[$stage["period"]]["begDate"] = $stage["base"];
    } else if ($periodsDate[$stage["period"]]["endDate"] > $stage["top"]) {  
      $periodsDate[$stage["period"]]["endDate"] = $stage["top"];
    }
  }
}

/*

$periods = array(
    "QUATERNARY",
    "NEOGENE",
    "PALEOGENE",
    "CRETACEOUS",
    "JURASSIC",
    "TRIASSIC",
    "PERMIAN",
    "CARBONIFEROUS",
    "DEVONIAN",
    "SILURIAN",
    "ORDOVICIAN",
    "CAMBRIAN",
    "EDIACARAN",
);
 */
 
$periodsOrdered = array(0 => "QUATERNARY", 1 => "NEOGENE", 2 => "PALEOGENE", 3 => "CRETACEOUS", 4 => "JURASSIC",
	5 => "TRIASSIC", 6 => "PERMIAN", 7 => "CARBONIFEROUS", 8 => "DEVONIAN", 9 => "SILURIAN", 10 => "ORDOVICIAN",
	11 => "CAMBRIAN", 12 => "EDIACARAN");	

$regions = array (
    array( "name" => "China", "searchurl" => "http://chinalex.geolex.org/searchAPI.php", "linkurl" => "http://chinalex.geolex.org/displayInfo.php"),
    array( "name" => "Indian Plate", "searchurl" => "http://indplex.geolex.org/searchAPI.php", "linkurl" => "http://indplex.geolex.org/displayInfo.php"),
    array( "name" => "Thailand", "searchurl" => "http://thailex.geolex.org/searchAPI.php", "linkurl" => "http://thailex.geolex.org/displayInfo.php"),
  );

?>
