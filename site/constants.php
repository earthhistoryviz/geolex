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
        } elseif ($periodsDate[$stage["period"]]["endDate"] > $stage["top"]) {
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
        } elseif ($epochDate[$stage["series"]]["endDate"] > $stage["top"]) {
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
  array("name" => "OneStrat", "searchurl" => "https://geolex.org/searchAPI.php", "linkurl" => "https://geolex.org/displayInfo.php"),
  array("name" => "Atlas", "searchurl" => "https://atlaslex.geolex.org/searchAPI.php", "linkurl" => "https://atlaslex.geolex.org/displayInfo.php"),
  array("name" => "Africa", "searchurl" => "https://africalex.geolex.org/searchAPI.php", "linkurl" => "https://africalex.geolex.org/displayInfo.php"),
  array("name" => "Belgium", "searchurl" => "https://belgiumlex.geolex.org/searchAPI.php", "linkurl" => "https://belgiumlex.geolex.org/displayInfo.php"),
  array("name" => "Central Africa", "searchurl" => "https://centralafricalex.geolex.org/searchAPI.php", "linkurl" => "https://centralafricalex.geolex.org/displayInfo.php"),
  array("name" => "China", "searchurl" => "https://chinalex.geolex.org/searchAPI.php", "linkurl" => "https://chinalex.geolex.org/displayInfo.php"),
  array("name" => "Congo", "searchurl" => "https://congolex.geolex.org/searchAPI.php", "linkurl" => "https://congolex.geolex.org/displayInfo.php"),
  array("name" => "Egypt", "searchurl" => "https://egyptlex.geolex.org/searchAPI.php", "linkurl" => "https://egyptlex.geolex.org/displayInfo.php"),
  array("name" => "Horn", "searchurl" => "https://hornlex.geolex.org/searchAPI.php", "linkurl" => "https://hornlex.geolex.org/displayInfo.php"),
  array("name" => "Indian Plate", "searchurl" => "https://indplex.geolex.org/searchAPI.php", "linkurl" => "https://indplex.geolex.org/displayInfo.php"),
  array("name" => "Japan", "searchurl" => "https://japanlex.geolex.org/searchAPI.php", "linkurl" => "https://japanlex.geolex.org/displayInfo.php"),
  array("name" => "Kazakhstan", "searchurl" => "http://kazakhstanlex.geolex.org/searchAPI.php", "linkurl" => "http://kazakhstanlex.geolex.org/displayInfo.php"),
  array("name" => "Malaysia", "searchurl" => "https://malaylex.geolex.org/searchAPI.php", "linkurl" => "https://malaylex.geolex.org/displayInfo.php"),
  array("name" => "Middle East", "searchurl" => "https://mideastlex.geolex.org/searchAPI.php", "linkurl" => "https://mideastlex.geolex.org/displayInfo.php"),
  array("name" => "Mozambique", "searchurl" => "https://mozambiquelex.geolex.org/searchAPI.php", "linkurl" => "https://mozambiquelex.geolex.org/displayInfo.php"),
  array("name" => "Niger", "searchurl" => "https://nigerlex.geolex.org/searchAPI.php", "linkurl" => "https://nigerlex.geolex.org/displayInfo.php"),
  array("name" => "Nigeria", "searchurl" => "https://nigerialex.geolex.org/searchAPI.php", "linkurl" => "https://nigerialex.geolex.org/displayInfo.php"),
  array("name" => "North Africa", "searchurl" => "https:northafricacalex.geolex.org/searchAPI.php", "linkurl" => "httpnorthafricaricalex.geolex.org/displayInfo.php"),
  array("name" => "Northwest Africa", "searchurl" => "https://northwestafricalex.geolex.org/searchAPI.php", "linkurl" => "https://northwestafricalex.geolex.org/displayInfo.php"),
  array("name" => "Panama", "searchurl" => "https://panamalex.geolex.org/searchAPI.php", "linkurl" => "https://panamalex.geolex.org/displayInfo.php"),
  array("name" => "Qatar", "searchurl" => "https://qatarlex.geolex.org/searchAPI.php", "linkurl" => "https://qatarlex.geolex.org/displayInfo.php"),
  array("name" => "South Africa", "searchurl" => "https://southafricalex.geolex.org/searchAPI.php", "linkurl" => "https://southafricalex.geolex.org/displayInfo.php"),
  array("name" => "South America", "searchurl" => "https://southamerlex.geolex.org/searchAPI.php", "linkurl" => "https://southamerlex.geolex.org/displayInfo.php"),
  array("name" => "Tajikistan", "searchurl" => "https://tajikistanlex.geolex.org/searchAPI.php", "linkurl" => "https://tajikistanlex.geolex.org/displayInfo.php"),
  array("name" => "Thailand", "searchurl" => "https://thailex.geolex.org/searchAPI.php", "linkurl" => "https://thailex.geolex.org/displayInfo.php"),
  array("name" => "Vietnam", "searchurl" => "https://vietlex.geolex.org/searchAPI.php", "linkurl" => "https://vietlex.geolex.org/displayInfo.php"),
);

// The below sets what ____lex gets set on the navigator bar next to the orange globe
$linksToRegions = array(
  "dev" => "Dev",
  "chinalex" => "China",
  "thailex" => "Thailand",
  "vietlex" => "Vietnam",
  "egyptlex" => "Egypt",
  "nigerlex" => "Niger",
  "nigerialex" => "Nigeria",
  "malaylex" => "Malaysia",
  "africalex" => "Africa",
  "belgiumlex" => "Belgium",
  "mideastlex" => "Middle East",
  "kazakhstanlex" => "Kazakhstan",
  "panamalex" => "Panama",
  "qatarlex" => "Qatar",
  "southamerlex" => "South America",
  "indplex" => "Indian Plate",
  "japanlex" => "Japan",
  "geolex" => "Geolex",
  "congolex" => "Congo",
  "hornlex" => "Horn",
  "northafricacalex" => "North Africa",
  "northwestafricalex" => "Northwest Africa",
  "panamalex" => "Panama",
  "qatarlex" => "Qatar",
  "southafricalex" => "South Africa",
  "southamerlex" => "South America",
  "tajikistanlex" => "Tajikistan",
  "thailex" => "Thailand",
  "vietlex" => "Vietnam",
  "mozambiquelex" => "Mozambique",
);

$regionName = isset($linksToRegions[$_SERVER['HTTP_HOST']]) ? $linksToRegions[$_SERVER['HTTP_HOST']] : '';

if ($_SERVER["SERVER_NAME"] == "dev") {
    array_push(
        $regions,
        array(
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
