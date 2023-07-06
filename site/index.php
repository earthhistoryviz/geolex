<?php
global $conn;
include_once("SqlConnection.php");
include_once("TimescaleLib.php");

if ($_REQUEST["filterperiod"]) {
  $did_search = true;

  $url = "http://localhost/searchAPI.php"
    ."?searchquery=".urlencode($_REQUEST["search"])
    ."&periodfilter=".$_REQUEST["filterperiod"]
    ."&provincefilter=".urlencode($_REQUEST["filterprovince"])
    ."&agefilterstart=".$_REQUEST["agefilterstart"]
    ."&agefilterend=".$_REQUEST["agefilterend"]
    ."&lithoSearch=".urlencode($_REQUEST["lithoSearch"]);

  $raw = file_get_contents($url);
  $response = json_decode($raw);
  $all_formations = array();

  $isSynonym = false;
  foreach ($response as $fname => $finfo) {
    if ($finfo->isSynonym) {
      $isSynonym = true;
    }

    array_push($all_formations, array(
      "name" => $fname,
      "stage" => $finfo->stage,
      "begAge" => $finfo->begAge,
      "geoJSON" => $finfo->geojson,
    ));
  }

  // Either sort by age or by alphabet depending on user selection (default is by alphabet)
  $isSortedByAge = false;
  if (isset($_REQUEST["byAgeButton"])) {
    uasort($all_formations, "sortByAge");
    $isSortedByAge = true;
  } else {
    sort($all_formations);
    $isSortedByAge = false;
  }

  // Get all of the associated stage data
  $info = parseDefaultTimescale();
  $stageConversion = array();
  $storedStage = "none";
  foreach ($info as $element) {
    foreach ($element as $key => $val) {
      if ($key == "stage") {
        array_push($stageConversion, array($val => "none"));
        $storedStage = $val;
      }
      if ($key == "color") {
        $stageConversion[0][$storedStage] = str_replace('/', ', ',  $val);
      }
    }
  }
  $stageArray = $stageConversion[0]; // stores the stages as well as the lookup in RGB
}

function sortByAge($a, $b) {
  $a1 = $a["begAge"];
  $a1 = str_replace(",", "", $a1);
  $b1 = $b["begAge"];
  $b1 = str_replace(",", "", $b1);

  if ($a1 == $b1) {
    return 0;
  }
  return $a1 < $b1 ? -1 : 1;
} ?>

<!DOCTYPE html>
<html>

<link rel="stylesheet" href="style.css"/>

<title>Home</title>

<?php
include_once("allowCustomOverride.php");
if (allowCustomOverride(__FILE__)) {
  return; // "true" means we had an override, do not continue execution of this script (i.e. top-level return)
}

// Default welcome page:

/* navBar will set $period, $maps, and $mapperiods for us
  * and create mapForPeriod function for us (in getmaps.php)
  */
include("navBar.php"); ?>

<h2 style="text-align: center; color: blue;">
  Welcome to the International Geology Website and Database!<br>
  Please enter a formation name or group to retrieve more information.
</h2> <?php

$formaction = "index.php";
$isFixedRegion = true; // For generalSearchBar.php to determine if we should display a region filter
include("generalSearchBar.php");

if ($did_search) {
  if (count($all_formations) == 0) { ?>
    <div class="no-results-message" style="text-align: center; padding-bottom: 20px; font-size: 20px">
      <h3>No formations found.</h3>
    </div> <?php
  } else {
    if ($isSynonym) { ?>
      <div class="synon-only-message" style="padding-bottom: 20px; font-size: 20px">
        <h3>
          No formation with name "<?=$_REQUEST["search"] ?>" was found in this Region.<br>
          However, "<?=$_REQUEST["search"] ?>" was found in Synonyms field and other occurences of Type Locality and Naming Field.
        </h3>
      </div> <?php
    } ?>

    <div class="toggle-order-button" style="padding-bottom: 20px; width: 100%">
      <form method="post">
        <input
          type="submit"
          style="color: #b75f02; border: 1px solid #b75f02; border-radius: 3px; font-size: 1em; box-shadow: 5px 5px 8px #888888; background-color: #FFFFFF;"
          name="<?php echo $isSortedByAge ? 'byAlphabetButton' : 'byAgeButton'; ?>"
          value="<?php echo $isSortedByAge ? 'Change to Alphabetical Listing' : 'Change to By-Age Listing'; ?>"
        />
      </form>
    </div>
    
    <div class="formation-container"> <?php
      foreach ($all_formations as $formation) { ?>
        <div class="formation-item" style="background-color: rgb(<?=$stageArray[$formation["stage"]] ?>, 0.8);"> <?php
          if ($formation["geoJSON"] !== "") { ?>
            <div style="padding-right: 10px; font-size: 13px;">&#127758</div> <?php
          } ?>
          <a href="displayInfo.php?formation=<?=$formation["name"]?>" target="_blank"><?=$formation["name"] ?></a>
        </div> <?php
      } ?>
    </div> <?php
  }
} else {
  global $period;
  if ($period) { ?>
    <p>Map is clickable</p>
    <p>Click on any provinces to view detailed information</p> <?php
    include mapForPeriod($period);
  }
} ?>

</html>
