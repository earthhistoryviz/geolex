<?php

global $maps;
$dir = dirname(__FILE__) . "/Mapinfo";
$maps  = glob("$dir/*_Country_Map.php");


function mapPeriodFromFilename($name) {
  global $dir;
  // Order_Period_Color_whatever_Country_Map.php
  $parts = explode("_", str_replace("$dir/", "", $name));
  return array(
    "order" => $parts[0],
    "period" => $parts[1],
    "color" => $parts[2],
    "filename" => $name,
  );
}
global $mapperiods;

$unsorted_periods = array_map('mapPeriodFromFilename', $maps);
$mapperiods = array(count($unsorted_periods));
foreach ($unsorted_periods as $up) {
  $order = (int)($up["order"]);
  $mapperiods[$order] = $up;
}
ksort($mapperiods); // they are still in insertion order until you re-sort by numeric keys

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
