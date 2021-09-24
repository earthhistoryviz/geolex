<?php

global $maps;
$maps  = glob("./Mapinfo/*_Country_Map.php");
function mapPeriodFromFilename($name) {
  $parts = explode("_",$name);
  return array(
    "period" => str_replace("./Mapinfo/", "", $parts[0]),
    "filename" => $name,
  );
}
global $mapperiods;

$mapperiods = array_map('mapPeriodFromFilename', $maps);

function mapForPeriod($period) {
  global $mapperiods;
  foreach($mapperiods as $mp) {
    if (strtoupper(trim($period)) == strtoupper(trim($mp["period"]))) {
      return $mp["filename"];
    }
  }
  return '';
}

?>
