<?php
global $conn;

include_once("SqlConnection.php");
include_once("TimescaleLib.php");
// Sets up the reconstruction variables: $recongeojson, etc.:
include_once("./makeReconstruction.php");
//include_once("./searchAPI.php"); 
if (isset($_REQUEST["filterperiod"])) {
  $did_search = true;

  $url = "http://localhost/searchAPI.php";
  $queryParams = [];
  $queryParams[] = "searchquery=" . urlencode($_REQUEST["search"]);
  $queryParams[] = "filterperiod=" . urlencode($_REQUEST["filterperiod"]);
  $queryParams[] = "agefilterstart=" . urlencode($_REQUEST["agefilterstart"]);
  $queryParams[] = "agefilterend=" . urlencode($_REQUEST["agefilterend"]);
  $queryParams[] = "lithoSearch=" . urlencode($_REQUEST["lithoSearch"]);
  $queryParams[] = "fossilSearch=" . urlencode($_REQUEST["fossilSearch"]);
  if (isset($_REQUEST["filterprovince"]) && is_array($_REQUEST["filterprovince"])) {
    foreach ($_REQUEST["filterprovince"] as $period) {
      $queryParams[] = "filterprovince[]=" . urlencode($period);
    }
  } else if (isset($_REQUEST["filterprovince"])) {
    $queryParams[] = "filterprovince[]=" . urlencode($_REQUEST["filterprovince"]);
  }

  $url .= "?" . implode("&", $queryParams);
  //echo $url;
  if (isset($_REQUEST["generateImage"])) {
    $url .= "&generateImage=1";
  }
  $raw = file_get_contents($url);
  //var_dump($raw);
  $response = json_decode($raw);
  $allFormations = array();

  $isSynonym = false;
  foreach ($response as $fname => $finfo) {
    if ($finfo->isSynonym) {
      $isSynonym = true;
    }

    $allFormations = array_merge($allFormations, array($finfo));
  }

  // Either sort by age or by alphabet depending on user selection (default is by alphabet)
  $isSortedByAge = true;
  if (isset($_REQUEST["byAlphabetButton"])) {
    sort($allFormations);
    $isSortedByAge = false;
  } else {
    uasort($allFormations, "sortByAge");
    $isSortedByAge = true;
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
  // Filter any formations that should not exist (i.e. if we're searching by the middle instead of the base age)
  $midAge = ((float)$_REQUEST["agefilterstart"] + (float)$_REQUEST["agefilterend"]) / 2;
  if (isset($_REQUEST["agefilterstart"]) && isset($_REQUEST["agefilterend"])) {
    $filteredformations = array();
    foreach ($allFormations as $f) {
      if ($midAge <= $f->begAge || $midAge >= $f->endAge) {
        array_push($filteredformations, $f);
      }
    }
    $allFormations = $filteredformations;
  }

  // Generate the merged geojson:
  $recongeojson = createGeoJSONForFormations($allFormations);
  // Only create the output directory if we are generating an image:
  if (isset($_REQUEST["generateImage"])) {
    $model = $_REQUEST["selectModel"] || "Default";
    if ($_REQUEST["recondate_description"] == "middle") {
      $toBeHashed = $recongeojson.$_REQUEST["agefilterstart"].$midAge.$_REQUEST["selectModel"];
    } else {
      $toBeHashed = $_REQUEST["agefilterstart"]. $recongeojson . "null";
    }

    $outdirhash = md5($toBeHashed);
    // outdirname is what pygplates should see
    switch ($_REQUEST["selectModel"]) {
      case  "Default": $outdirname = "livedata/default/$outdirhash";  break;
      case "Marcilly": $outdirname = "livedata/marcilly/$outdirhash"; break;
      case  "Scotese": $outdirname = "livedata/scotese/$outdirhash";  break;
      default:         $outdirname = "livedata/unknown/$outdirhash";  break;
    }

    // and php is running one level up:
    $outdirname_php = "pygplates/$outdirname";
    $initial_creation_outdir = false; // did we have to make the output hash directory name?
    if ($_REQUEST["debug"]) {
      $initial_creation_outdir = true;
    }

    if (!file_exists($outdirname_php)) {
      $initial_creation_outdir = true;
      mkdir($outdirname_php, 0777, true);
    }
    $reconfilename = "$outdirname_php/recon.geojson";

    if ($_REQUEST["debug"]) {
      echo "The directory path for this reconstruction is: $outdirname";
    }

    if (!file_exists($reconfilename) || $_REQUEST["debug"]) {
      if ($_REQUEST["debug"]) {
        echo "Debugging mode, writing geojson file to $reconfilename";
      }
      file_put_contents($reconfilename, $recongeojson);
    }
  }
}

function sortByAge($a, $b) {
  $a1 = $a->begAge;
  $a1 = str_replace(",", "", $a1);
  $b1 = $b->begAge;
  $b1 = str_replace(",", "", $b1);

  if ($a1 == $b1) {
    return 0;
  }
  return $a1 < $b1 ? -1 : 1;
} ?>

<!DOCTYPE html>
<html>
<head>
  <?php
  include_once("constants.php");
  ?>
  <title>Home - <?=$regionName ?> Lexicon</title>
<?php
include_once("allowCustomOverride.php");
$override_fullpath = allowCustomOverride(__FILE__);
if (empty($override_fullpath)) {
  $titleMessage = "Welcome to the International Geology Website and Database!";
  $mapMessage = "Click on any provinces below to view detailed information";
  $aboutMessage = "Information provided by the China Stratigraphic Commission --see About";
} else {
  include_once($override_fullpath); // Override file will set the title message and map Message.
}

// Default welcome page:

/* navBar will set $period, $maps, and $mapperiods for us
  * and create mapForPeriod function for us (in getmaps.php)
  */
include("navBar.php"); ?>

<h2 style="text-align: center; color: blue;">
  <?= $titleMessage ?><br>
  Please enter a formation name or group to retrieve more information.
</h2> 
<h3 style="text-align: center;"><?= $aboutMessage ?></h3><br>
<?php

$formaction = "index.php";
$isFixedRegion = true; // For generalSearchBar.php to determine if we should display a region filter
include("generalSearchBar.php");
//var_dump($allFormations);
if ($did_search) {
  if (count($allFormations) == 0) { ?>
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
    }
    //validGeoJSONFound comes from makeReconstruction.php
    if ($validGeoJSONFound && !empty($_REQUEST['agefilterstart'])) { ?>
      <div class="reconstruction"> <?php
        include_once("./makeButtons.php"); ?>
      </div> 
      <div class="buttonContainer">
        <button id="generateAllImagesBtn">Generate All Models</button>
      </div>
      <script>
        document.addEventListener('DOMContentLoaded', function () {
          let generateAllImagesBtn = document.getElementById('generateAllImagesBtn');
          generateAllImagesBtn.addEventListener('click', function() {
            let data = {
              geojson: <?= json_encode($recongeojson) ?>,
              beg_date: <?= json_encode($_GET["agefilterstart"]) ?>,
              formation: "Multiple"
            };
            var xhr = new XMLHttpRequest();
            xhr.open("POST", '/addGeojsonToFiles.php', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            let outdirhash = "";
            xhr.onreadystatechange = function() {
              if (this.readyState === XMLHttpRequest.DONE) {
                if (this.status === 200) {
                  outdirhash = this.responseText;
                  const begDateDisplay = data["beg_date"];
                  const url = '/generateAllImages.php?beg_date=' 
                  + encodeURIComponent(begDateDisplay) 
                  + '&outdirhash=' + encodeURIComponent(outdirhash);
                  window.open(url, '_blank');
                } else {
                  console.error('Error writing file:', this.responseText);
                }
              }
            };
            xhr.send(JSON.stringify(data));
          });
        });
      </script> <?php
    } ?>
    <style>
      .buttonContainer {
        display: flex;
        justify-content: center;
        align-items: center;
      }
      #generateAllImagesBtn {
        padding: 10px 20px;
        background-color: #e67603;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
      }
    </style>
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
    <link rel="stylesheet" href="style.css"/>
    <div class="formation-container"> <?php
      foreach ($allFormations as $formation) { ?>
        <div class="formation-item" style="background-color: rgb(<?=$stageArray[$formation->stage] ?>, 0.8);"> <?php
          if ($formation->geojson) { ?>
            <div style="padding-right: 10px; font-size: 13px;">&#127758</div> <?php
          } 
          $link = "";
          if ($auth) {
            $link = "adminDisplayInfo.php?formation=" . $formation->name; 
          } else {
            $link = "formations/$formation->name";
          }
          ?>
          <a href="<?= $link ?>" target="_blank"><?=$formation->name ?></a>
        </div> <?php
      } ?>
    </div> <?php
  }
} else {
  global $period; 
  if ($auth) { ?>
    <div style="display: flex; flex-direction: row;">
    <div style="width: 120px; padding: 5px; display: flex; flex-direction: column;"> <?php
      global $period;
      if (isset($_GET["period"])) {
        $period = $_GET["period"];
      } else {
        $period = "Cenozoic";
      }
      foreach ($mapperiods as $p) { ?>
        <div style="background-color: #<?php echo $p["color"] ?>; padding: 5px;">
          <a href="/adminIndex.php?period=<?php echo $p["period"] ?>" style="text-decoration: none; font-family: Arial;"><?php echo $p["period"] ?></a>
        </div> <?php
      } ?>
    </div> <?php
  }
  if ($period) { ?>
    <br>
    <div class="map-container" style="display: flex; justify-content: center; flex-direction: column; align-items: center;">
      <p style="font-size: 20px; font-weight: bold;"><?=$mapMessage ?></p> <?php
      $filePath = mapForPeriod($period);
      include $filePath; ?>
    </div> <?php
  }

}
include_once("footer.php"); ?>
</body>
</html>
