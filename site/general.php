<?php
include_once("constants.php");
include_once("TimescaleLib.php");
// Sets up the reconstruction variables: $recongeojson, etc.:
include_once("./makeReconstruction.php");

/* If we have a filterperiod and filterregion, send off the API requests */
if ($_REQUEST["filterperiod"] && $_REQUEST["filterregion"]) {
  $didsearch = true;

  $regionstosearch = array();

  foreach ($regions as $r) {
    if (in_array($r["name"], $_REQUEST["filterregion"])) {
      array_push($regionstosearch, $r);
    }
  }

  $retgeoJSON = array();
  $results = array();
  $allformations = array();

  foreach ($regionstosearch as $r) {
    // Get the info about the matched formations from the external API:
    $url = $r["searchurl"]."?searchquery="
      .$_REQUEST["search"]."&periodfilter="
      .$_REQUEST["filterperiod"]."&agefilterstart="
      .$_REQUEST["agefilterstart"]."&agefilterend="
      .$_REQUEST["agefilterend"]."&lithoSearch="
      .urlencode($_REQUEST["lithoSearch"]);

    if ($_REQUEST["generateImage"]) {
      $url .= "&generateImage=1";
    }
    $raw = file_get_contents($url);
    $response = json_decode($raw);

    // Loop over all the returned formations to figure out the geojson
    $results[$r["name"]] = array(
      "linkurl" => $r["linkurl"],
      "formations" => $response,
      "groupbyprovince" => array(),
      "isSynonym" => false,
    );

    // Look through all the formations to find any overlapping provice names so the grouping will work
    foreach ($response as $fname => $finfo) {
      if ($finfo->isSynonym) {
        $results[$r["name"]]["isSynonym"] = true;
      }
      // Keep all the formations in a flat array so we can create the geojson from them
      $allformations = array_merge($allformations, array($finfo));
      $p = $finfo->province;
      if (!$p || strlen(trim($p)) < 1) {
        $p = "Unknown Province";
      }
      $multiprovinces = explode(", ", $p);
      $overlapCount = 0; // counts number of overlaps
      foreach ($multiprovinces as $oneprovince) {
        $results[$r["name"]]["groupbyprovince"][$oneprovince]["formations"][$fname] = $finfo;

        // Figure out which periods overlap this formation
        // periodsDate comes from constants.php
        foreach ($periodsDate as $searchperiod) {
          if (stripos($finfo->period, $searchperiod["period"]) === false) {
            continue;
          }
          $results[$r["name"]]["groupbyprovince"][$oneprovince]["groupbyperiod"][$searchperiod["period"]][$fname] = $finfo;
        }
      }
    }
    ksort($results[$r["name"]]["groupbyprovince"]);
  }

  //--------------------------------
  // Filter any formations that should not exist (i.e. if we're searching by the middle instead of the base age)
  $midAge = ($_REQUEST["agefilterstart"] + $_REQUEST["agefilterend"]) / 2;
  if (isset($_REQUEST["agefilterstart"]) && isset($_REQUEST["agefilterend"])) {
    $filteredformations = array();
    foreach ($allformations as $f) {
      if ($midAge <= $f->begAge || $midAge >= $f->endAge) {
        array_push($filteredformations, $f);
      }
    }
    $allformations = $filteredformations;
  }

  //----------------------------------------------
  // Generate the merged geojson:
  $recongeojson = createGeoJSONForFormations($allformations);

  //----------------------------------------------
  // Only create the output directory if we are generating an image:
  if ($_REQUEST["generateImage"]) {
    $model = $_REQUEST["selectModel"] || "Default";
    if ($_REQUEST["recondate_description"] == "middle") {
      $toBeHashed = $recongeojson.$_REQUEST["agefilterstart"].$midAge.$_REQUEST["selectModel"];
    } else {
      $toBeHashed = $recongeojson.$_REQUEST["agefilterstart"].$_REQUEST["selectModel"];
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

/* This is necessary to get generalSearchBar to send things back to us */
$formaction = "general.php"; ?>
<link rel="stylesheet" href="generalStyling.css">

<?php include("navBar.php"); /* navBar will set $period for us */ ?>

<h2 style="text-align: center; color: blue;">Welcome to the International Geology Website and Database!<br>Please enter a formation name or group to retrieve more information.</h2>
<?php include("generalSearchBar.php"); ?>

<div style="display: flex; flex-direction: column;"> <?php
  $sorted = array();

  // get all of the associated stage data
  $info = parseDefaultTimescale();
  $stageConversion = array();
  $storedStage = "none";
  $count = 0; // used for indexing through the stageConversion array

  foreach ($info as $element) {
    foreach ($element as $key => $val) {
      if ($key == "stage") {
        array_push($stageConversion, array($val => "none"));
        $storedStage = $val;
      }
      if ($key == "color") {
        $stageConversion[0][$storedStage] = str_replace('/', ', ',  $val);
        $count = $count + 1;
      }
    }
  }

  $stageArray = $stageConversion[0]; // stores the stages as well as the lookup in RGB
  if ($didsearch) {
    if (count($allformations) == 0) { ?>
      <div class="no-results-message">
        <h3 style="text-align: center;">No formations found.</h3>
      </div> <?php
    } else {
      /*
        Provide option for showing reconstruction image when using:
          1. Date
          2. Date Range, with either:
            a. no End Date
            b. Start Date == End Date
       */
      if ($_REQUEST['agefilterstart'] != "" || $_REQUEST['agefilterstart'] != "" && $_REQUEST['agefilterend'] == "") { ?>
        <div class="reconstruction"> <?php
          include_once("./makeButtons.php"); ?>
        </div> <?php
      }

      /*
        Show all returned formations in following format:
          Region
          ----------
          Province
          PERIOD
          Formation_1 Formation_2 ...
          PERIOD
          Formation_3 Formation_4 ...
          ------ ----
          Province
          PERIOD
          Formation_5 Formation_6 ...
          ----------
          .
          .
          .

          Region
          ----------
          .
          .
          .
      */
      foreach ($results as $regionname => $regioninfo) { ?>
        <div class="formation-container">
          <h3 class="region-title"><?=$regionname?></h3> <?php
          if ($regioninfo["isSynonym"]) { ?>
            <div class="synonym-message">
              <h3>
                No formation with name "<?=$_REQUEST["search"]?>" was found in this Region.
                <br>
                However, "<?=$_REQUEST["search"]?>" was found in Synonyms field and other occurences of Type Locality and Naming Field.
              </h3>
            </div><?php
          } ?>
          <br>
          <div> <?php
            foreach ($regioninfo["groupbyprovince"] as $province => $provinceinfo) { ?>
              <hr>
              <h3><?=$province?></h3>
              <div class="province-container"> <?php
                foreach ($periodsDate as $p) {
                  foreach ($provinceinfo["groupbyperiod"] as $pname => $formations) {
                    if ($pname !== $p["period"]) {
                      continue;
                    } ?>
                    <h5><?=$pname?></h5>
                    <div class="period-container"> <?php
                      $geojsonIndex = 0;
                      foreach ($formations as $fname => $finfo) {
                        $finfoArr = json_decode(json_encode($finfo), true); ?>
                        <div style="background-color: rgb(<?=$stageArray[$finfoArr["stage"]]?>, 0.8);" class="button"> <?php
                          if ($finfoArr['geojson']) { // if geoJSON exists ?>
                            <div style="padding-right: 10px; font-size: 13px;">&#127758</div> <?php
                          } ?>
                          <a href="<?=$regioninfo["linkurl"]?>?formation=<?=$fname?>" target="_blank"><?=$fname?></a>
                        </div> <?php
                      } ?>
                    </div> <?php
                  }
                } ?>
              </div> <?php
            } ?>
          </div>
        </div> <?php
      }
    } /* end else */

    if ($timedout) { ?>
      NOTE: Timed out awaiting external image creation, had to re-start <?php
    }
  } /* end did search if */ ?>
</div>
