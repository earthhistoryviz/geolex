<?php
// This code is needed and commented out only because it is causing a bug.

include_once("TimescaleLib.php");
$timescaleExists = true;
try {
  $timescale = parseDefaultTimescale();
} catch (RuntimeException $e) {
  $timescaleExists = false;
  if ($_SERVER["PHP_SELF"] != "/adminDash.php" && $_SERVER["PHP_SELF"] != "/uploadTimescale.php") {
    include_once("navBar.php");
    echo "A runtime exception has occurred: " . $e->getMessage();
    echo "<br>Do you have a default_timescale.xlsx inside the timescales folder? If not sign into admin, go to Timescale, and upload a timescale called default_timescale.xlsx";
    exit();
  }
}

$periodsDate = array();
$epochDate = array();

foreach ($timescale as $stage) {
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

  // get Epoch information
  if (!array_key_exists($stage["series"], $epochDate)) {
    $epochDate[$stage["series"]] = array(
      "begDate" => $stage["base"],
      "endDate" => $stage["top"],
    );
  } else {
    if ($epochDate[$stage["series"]]["begDate"] < $stage["base"]) {
      $epochDate[$stage["series"]]["begDate"] = $stage["base"];
    } else if ($epochDate[$stage["series"]]["endDate"] > $stage["top"]) {
      $epochDate[$stage["series"]]["endDate"] = $stage["top"];
    }
  }
}
 
$periodsOrdered = array(
  0 => "QUATERNARY",
  1 => "NEOGENE",
  2 => "PALEOGENE",
  3 => "CRETACEOUS",
  4 => "JURASSIC",
	5 => "TRIASSIC",
  6 => "PERMIAN",
  7 => "CARBONIFEROUS",
  8 => "DEVONIAN",
  9 => "SILURIAN",
  10 => "ORDOVICIAN",
  11 => "CAMBRIAN",
  12 => "EDIACARAN"
);

$regions = array(
  array("name" => "Macrostrat", "searchurl" => "http://localhost/macrostratAPI.php", "linkurl" => null),
  array("name" => "China", "searchurl" => "http://chinalex.geolex.org/searchAPI.php", "linkurl" => "http://chinalex.geolex.org/displayInfo.php"),
  array("name" => "Indian Plate", "searchurl" => "http://indplex.geolex.org/searchAPI.php", "linkurl" => "http://indplex.geolex.org/displayInfo.php"),
  array("name" => "Thailand", "searchurl" => "http://thailex.geolex.org/searchAPI.php", "linkurl" => "http://thailex.geolex.org/displayInfo.php"),
  array("name" => "Vietnam", "searchurl" => "http://vietlex.geolex.org/searchAPI.php", "linkurl" => "http://vietlex.geolex.org/displayInfo.php"),
  array("name" => "Niger", "searchurl" => "http://nigerlex.geolex.org/searchAPI.php", "linkurl" => "http://nigerlex.geolex.org/displayInfo.php"),
  array("name" => "Malaysia", "searchurl" => "http://malaylex.geolex.org/searchAPI.php", "linkurl" => "http://malaylex.geolex.org/displayInfo.php"),
  array("name" => "Africa", "searchurl" => "http://africalex.geolex.org/searchAPI.php", "linkurl" => "http://africalex.geolex.org/displayInfo.php"),
  array("name" => "Belgium", "searchurl" => "http://belgiumlex.geolex.org/searchAPI.php", "linkurl" => "http://belgiumlex.geolex.org/displayInfo.php"),
  array("name" => "Middle East", "searchurl" => "http://mideastlex.geolex.org/searchAPI.php", "linkurl" => "http://mideastlex.geolex.org/displayInfo.php"),
  array("name" => "Panama", "searchurl" => "http://panamalex.geolex.org/searchAPI.php", "linkurl" => "http://panamalex.geolex.org/displayInfo.php"),
  array("name" => "Qatar", "searchurl" => "http://qatarlex.geolex.org/searchAPI.php", "linkurl" => "http://qatarlex.geolex.org/displayInfo.php"),
  array("name" => "South America", "searchurl" => "http://southamerlex.geolex.org/searchAPI.php", "linkurl" => "http://southamerlex.geolex.org/displayInfo.php")
);

$linksToRegions = array(
  "dev" => "Dev",
  "chinalex" => "China",
  "thailex" => "Thailand",
  "vietlex" => "Vietnam",
  "nigerlex" => "Niger",
  "malaylex" => "Malaysia",
  "africalex" => "Africa",
  "belgiumlex" => "Belgium",
  "mideastlex" => "Middle East",
  "panamalex" => "Panama",
  "qatarlex" => "Qatar",
  "southamerlex" => "South America",
  "indplex" => "Indian Plate",
  "japanlex" => "Japan"
);
$regionName = isset($linksToRegions[$_SERVER['HTTP_HOST']]) ? $linksToRegions[$_SERVER['HTTP_HOST']] : '';

if ($_SERVER["SERVER_NAME"] == "dev") {
  array_push($regions, array(
    "name" => "Dev",
    "searchurl" => "http://dev.geolex.org/searchAPI.php",
    "linkurl" => "http://dev.geolex.org/displayInfo.php"
  )
  );
}

$macrostratLithoNames = array(
  "dolomite-limestone" => "Dolomitic limestone",
  "lime_mudstone" => "Limestone",
  "sandstone" => "Sandstone",
  "quartz arenite" => "Sandstone",
  "litharenite" => "Coarse-grained sandstone",
  "sand" => "Sandstone",
  "siltstone" => "Siltstone",
  "silt" => "Siltstone",
  "dolomitic siltstone" => "Dolomite",
  "shale" => "Claystone",
  "limestone" => "Limestone",
  "dolomite" => "Dolomite",
  "conglomerate" => "Aus conglomerate",
  "carbonate" => "Limestone",
  "dolomite-mudstone" => "Dolomite",
  "dolostone" => "Dolomite",
  "mudstone" => "Sandy_claystone",
  "sandy-dolomite" => "Sandy limestone",
  "quartzite" => "Sandstone",
  "halite" => "Halite",
  "basalt" => "Lava",
  "rhyolite" => "Lava",
  "andesite" => "Lava",
  // lava
  "till" => "Glacial till",
  "loess" => "Siltstone",
  "calcareous ooze" => "Chalk",
  "chalk" => "Chalk",
  "gravel" => "Aus conglomerate",
  // doesn't have a direct translation in lookup table in dropbox (temporary translation)
  "plutonic" => "Granitic",
  "granite" => "Granitic",
  "clay" => "Claystone",
  "syenite" => "Volcanics",
  "tuff" => "Volcanic_ash",
  // doesn't have a direct translation in lookup table in dropbox (temporary translation)
  "volcanic" => "Volcanics",
  "metamorphic" => "Gneiss",
  "volcaniclastic" => "Volcanic_ash",
  "migmatite" => "Gneiss",
  "gneiss" => "Gneiss",
  "tonalite" => "Granitic",
  "granodiorite" => "Granitic",
  "monzonite" => "Granitic",
  // doesn't have a direct translation in lookup table in dropbox (temporary translation)
  "argillite" => "Claystone"
);