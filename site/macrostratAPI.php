<?php
// ini_set('memory_limit', '256M'); // or a higher value

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ERROR);
include_once("constants.php");

$searchquery = addslashes($_REQUEST['searchquery']);
$agefilterstart = addslashes($_REQUEST['agefilterstart']);
$agefilterend = addslashes($_REQUEST['agefilterend']);

if (!$searchquery) {
  $searchquery = "";
}

if (!isset($_REQUEST['agefilterend']) || $agefilterstart < $agefilterend) {
  $agefilterend = "";
}

$url = "https://macrostrat.org/api/units?";

if ($searchquery != "") {
  $url .= "strat_name=".$searchquery."&";
} else {
  $url .= "strat_name=%&";
}

if ($agefilterstart != "") {
  if ($agefilterend == "") {
    $url .= "age=".$agefilterstart."&";
  } else {
    $url .= "age_top=".$agefilterend."&"
      ."age_bottom=".$agefilterstart."&";
  }
}

$url .= "response=long";

$response = json_decode(file_get_contents($url), true);
$result = array();

header("Content-Type: application/json");

if (!isset($response["success"])) {
  echo json_encode($result);
  exit();
}

foreach ($response["success"]["data"] as $f) {
  if ($f["unit_name"] == "unnamed" || $f["unit_name"] == "Unnamed") {
    continue;
  }

  $name = $f["unit_name"];
  $begAge = $f["b_age"];
  $endAge = $f["t_age"];
  $geojson = extractGeoJSON($f["col_id"]);
  $lithology_pattern = getLithPattern($f["lith"], $f["environ"]);

  $result[$name] = array(
    "name" => $name,
    "begAge" => $begAge,
    "endAge" => $endAge,
    "geojson" => $geojson,
    "lithology_pattern" => $lithology_pattern,
  );
}

echo json_encode($result);

function getLithPattern($liths, $environ) {
  global $macrostratLithoNames;

  $output = "";
  foreach ($liths as $lith) {
    if ($lith["name"] == "sandstone") {
      if ($environs[0]["type"] == "dune" || $environs[0]["type"] == "marine" || $environs[0]["type"] == "shore") {
        return "Sandstone";
      }
      if ($environs[0]["type"] == "loess") {
        return "Siltstone";
      }
    }

    if ($lith["name"] == "congolomerate" && $environs[0]["type"] == "fluvial") {
      return "Aus conglomerate";
    }

    if ($lith["name"] == "tuff") { // corresponds to code 711
      return "Volcanic_ash";
    }

    // If not one of the special cases then just use the lookup table in constants.php
    $output = $macrostratLithoNames[$lith["name"]];

    if (isset($output) && $output != "") {
      return $output;
    }

    if ($lith["type"] == "volcanic") {
      return "Volcanics";
    }
  }

  return isset($output) && $output != "" ? $output : "Unknown";
}

function extractGeoJSON($col_id) {
  $url = "https://macrostrat.org/api/columns?col_id=".$col_id."&format=geojson";
  $geojson = json_decode(file_get_contents($url), true)["success"]["data"]["features"];

  $output = array();
  $output["type"] = "Feature";

  $geometry = array();
  $geometry["type"] = "MultiPolygon";
  $geometry["coordinates"] = array();

  $coords = array();
  foreach ($geojson as $g) {
    $coordinates = $g["geometry"]["coordinates"];
    foreach ($coordinates as $c) {
      array_push($coords, $c);
    }
  }
  array_push($geometry["coordinates"], $coords);
  $output["geometry"] = $geometry;

  return $output;
}

?>
