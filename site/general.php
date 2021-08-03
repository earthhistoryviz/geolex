<?php
include("constants.php");
include_once("TimescaleLib.php");

/* If we have a filterperiod and filterregion, send off the API requests */
if ($_REQUEST["filterperiod"] && $_REQUEST["filterregion"]) {
  $didsearch = true;

  $regionstosearch = array();
  foreach($regions as $r) {
    if ($r["name"] == $_REQUEST["filterregion"] || $_REQUEST["filterregion"] == "All") {
      array_push($regionstosearch, $r);
    }
  }

  $retgeoJSON = array();
  $results = array();
  $header = '{
    "type": "FeatureCollection",
    "name": "Triassic strata_10Feb2021",
    "crs": { "type": "name", "properties": { "name": "urn:ogc:def:crs:OGC:1.3:CRS84" } },
    "features": [';
  $recongeojson = $header;
  $firstRun = 1; 
  foreach($regionstosearch as $r) {
    $url = $r["searchurl"] . "?searchquery=".$_REQUEST["search"]."&periodfilter=".$_REQUEST["filterperiod"]."&agefilterstart=".$_REQUEST["agefilterstart"]."&agefilterend=".$_REQUEST["agefilterend"];
    if ($_REQUEST["generateImage"]) {
      $url .= "&generateImage=1";
    }
    $raw = file_get_contents($url);
    $response = json_decode($raw);

    $results[$r["name"]] = array(
      "linkurl" => $r["linkurl"],
      "formations" => $response,
      "groupbyprovince" => array(),
    );
    foreach($response as $fname => $finfo) {
      // Compiling all geoJSON strings from the returned formations into recon.geojson
      $p = $finfo->province;
      $geo = $finfo->geojson;
      $name = $finfo->name;
      $begAge = $finfo->begAge;
      $endAge = $finfo->endAge;
      if ($geo && $geo != "null") {
        //geoJSONmid below filters out all formations that end by the middle of a date range/period
        $geoJSONmid[$name] = array("name" => $name, "begAge" => $begAge, "endAge" => $endAge, "geojson" => $geo);      
  if(!$firstRun) {
          $recongeojson .= ",\n";
        }
        $recongeojson .= $geo;
        $firstRun = 0;
      }

      if(!$p || strlen(trim($p)) < 1) $p = "Unknown Province";
      //$results[$r["name"]]["groupbyprovince"][$p]["formations"][$fname] = $finfo;
      $newp = explode(", ", $p);
      $overlapCount = 0; // counts number of overlaps
      foreach($newp as $sepp) {
        // $results[$r["name"]]["groupbyprovince"][$sepp]["formations"][$fname] = $finfo;
        $results[$r["name"]]["groupbyprovince"][$sepp]["formations"][$fname] = $finfo; 

        /*
        $results["china"][groupbyprovince][fujian][formations][taoziken Fm]
                                                              [Baratang Fm]
                                                  [groupbyperiod][cretaceous][taoziken fm]
                                                                [jurassic  ][baratan fm]
        */

        /* Figure out which periods overlap this formation */
        foreach($periodsDate as $searchperiod) {
          if(stripos($finfo->period, $searchperiod["period"]) === false) continue;
          $results[$r["name"]]["groupbyprovince"][$sepp]["groupbyperiod"][$searchperiod["period"]][$fname] = $finfo;
        
        }
      }
    }
    ksort($results[$r["name"]]["groupbyprovince"]);
  }

  $recongeojson .= "]}"; 

// recongeojsonmid is what needs to be put into the geojson file when the middle reconstruction button gets pressed 
  $recongeojsonmid = $header;
  $firstform = 1; // don't want a comma before the very first geojson that gets appended

     $midAge = ($_REQUEST["agefilterstart"] +  $_REQUEST["agefilterend"]) / 2;
  if(isset($_REQUEST["agefilterstart"]) && isset($_REQUEST["agefilterend"])){
  foreach($geoJSONmid as $midForm){
           if ($midAge <= $midForm["begAge"] ||  $midAge >= $midForm["endAge"]){
             $recongeojsonmid .= $midForm["geojson"];
             $recongeojsonmid .= ",";
           }
           $firstform = 0;
  }
  }
  $recongeojsonmid = substr($recongeojsonmid, 0, -1);
  $recongeojsonmid .= "]}";


  //echo $recongeojson;
 
  // Only create the output directory if we are generating an image:
  
  if ($_REQUEST["generateImage"]) {
    if($_REQUEST["recondate_description"] == "base" && $_REQUEST["selectModel"] == "Marcilly"){    
    $toBeHashed = $recongeojson.$_REQUEST["agefilterstart"].$_REQUEST["selectModel"];
    } else if($_REQUEST["recondate_description"] == "base" && $_REQUEST["selectModel"] == "Default"){
     $toBeHashed = $recongeojson.$_REQUEST["agefilterstart"];
    } 
    if($_REQUEST["recondate_description"] == "middle"  && $_REQUEST["selectModel"] == "Marcilly"){
      $toBeHashed = $recongeojsonmid.(($_REQUEST["agefilterstart"] + $_REQUEST["agefilterend"])/2).$REQUEST["selectModel"];
    } else if($_REQUEST["recon_description"] == "middle"  && $_REQUEST["selectModel"] == "Default"){
      $toBeHashed = $recongeojsonmid.($_REQUEST["agefilterstart"] + $_REQUEST["agefilterend"])/2;
    } 
    
    //$outdirhash = md5($toBeHashed);
    $outdirhash = md5($toBeHashed);    
    // outdirname is what pygplates should see
    if($_REQUEST["selectModel"] == "Default"){
      $outdirname = "livedata/$outdirhash";
    }
    else {
      $outdirname .= $_REQUEST["selectModel"].'/';      
      $outdirname .= $outdirhash;
    }
    // and php is running one level up:
    $outdirname_php = "pygplates/$outdirname";
    //echo $outdirname_php;
    $initial_creation_outdir = false; // did we have to make the output hash directory name?
    if (!file_exists($outdirname_php)) {
      $initial_creation_outdir = true;
      //echo "Creating a new folder!!!";
      mkdir($outdirname_php, 0777, true);
    }
    $reconfilename = "$outdirname_php/recon.geojson";
    if (!file_exists($reconfilename)) {
      file_put_contents($reconfilename, $recongeojson);
    }
  }
}
   
/* This is necessary to get generalSearchBar to send things back to us */
$formaction = "general.php"; ?>
<link rel="stylesheet" href="generalStyling.css">

<?php include("navBar.php"); /* navBar will set $period for us */?>

<h2 align="center" style="color: blue;">Welcome to the International Geology Website and Database!<br>Please enter a formation name or group to retrieve more information.</h2>
<?php include("generalSearchBar.php");?>

<div style="display: flex; flex-direction: column;">
<?php
$sorted = array();

// get all of the associated stage data 
$info = parseDefaultTimescale();
$stageConversion = array();
$storedStage = "none";
$count = 0; // used for indexing through the stageConversion array
foreach($info as $element) {
  foreach($element as $key => $val) {
    if($key == "stage"){
      array_push($stageConversion, array($val => "none"));
      $storedStage = $val;
    }
    if($key == "color") {
      $stageConversion[0][$storedStage] = str_replace('/', ', ',  $val);
      $count = $count + 1;
    }
  }
}
$stageArray = $stageConversion[0]; // stores the stages as well as the lookup in RGB 
if ($didsearch) {
  if (count($results) < 0) {
    echo "No results found.";
  } else {
    /*
      Provide option for showing reconstruction image when using:
        1. Date
        2. Date Range, with either:
          a. no End Date
          b. Start Date == End Date
     */
    if ($_REQUEST[agefilterstart] != "" /*&& $_REQUEST[agefilterstart] == $_REQUEST[agefilterend] */
      || $_REQUEST[agefilterstart] != "" && $_REQUEST[agefilterend] == "") { ?>
      <div class="reconstruction">
        <?php if ($_REQUEST["generateImage"] == "1") { ?>
          <?php 
           $timedout = false;
           if (!$initial_creation_outdir) { // we already had the folder up above, so just wait for image...
             $count=0;
             while (!file_exists("$outdirname_php/final_image.png")) { // assume another thing is making this image
               usleep(500);
               $count++;
               if ($count > 30) { // we've tried for 20 seconds, just fail it
                 $timedout = true;
                 break;
               }
             }
             // If we get here, image should exist, or we gave up waiting
           }
      
           if ($_REQUEST["selectModel"] == "Default" && ($initial_creation_outdir || $timedout)) { // if this is the first time, or we timed out waiting for image, create it:
             // Otherwise, hash doesn't exist, so we need to spawn a pygplates to make it:
             exec("cd pygplates && ./master_run_pygplates_pygmt.py ".$_REQUEST['recondate']." $outdirname", $ending);
	   } else if($_REQUEST["selectModel"] == "Marcilly" && ($initial_creation_outdir || $timedout)) {
             exec("cd pygplates && ./MarcillyModel.py ".$_REQUEST['recondate']." $outdirname", $ending);
        }?>

     <div id="reconImg" align="center">
       <?php
         if($_REQUEST["searchtype"] == "Period" && $_REQUEST["filterstage"] != "All"){?>
           <figcaption style="text-align: center; font-size: 45px;"> Reconstruction for <?=$_REQUEST[recondate_description]?> of <?= $_REQUEST["filterstage"] ?> </figcaption>
         <?php } else if($_REQUEST["searchtype"] == "Period") { ?>
           <figcaption style="text-align: center; font-size: 45px;"> Reconstruction for <?=$_REQUEST[recondate_description]?> of <?= $_REQUEST["filterperiod"] ?> </figcaption>
         <?php }
       ?>
       <img src="<?=$outdirname_php?>/final_image.png" style="text-align:center" width ="80%">
       <br/><br/>
       A very special thanks to the excellent <a href="https://gplates.org">GPlates</a> and their
       <a href="https://www.gplates.org/docs/pygplates/pygplates_getting_started.html">pyGPlates</a> software as well as
       <a href="https://www.pygmt.org/latest/">pyGMT</a> which work together to create these images.
     </div> <?php
        } else if($_REQUEST["generateImage"] != "2") {
          // User selection of reconstruction model
?>
         <?php
            $baseraw = $_REQUEST["agefilterstart"];
            $basepretty = number_format($baseraw, 2);
            $topraw = $_REQUEST["agefilterend"];
            $toppretty = number_format($topraw, 2);
      //$useperiod = false;
            if ($_REQUEST["searchtype"] == "Period") {
              $middleraw = ($_REQUEST["agefilterstart"] + $_REQUEST["agefilterend"])/2.0;
              $middlepretty = number_format($middleraw, 2);
              //$useperiod = true;
              if ($_REQUEST["filterstage"] && $_REQUEST["filterstage"] != "All") {
                $name = $_REQUEST["filterstage"];
              } else {
                $name = $_REQUEST["filterperiod"];
              }
            }
          ?>
          <style>
            .reconbutton {
              width:250px; 
              display: flex; 
              flex-grow: 0; 
              flex-direction: row; 
              justify-content: center; 
              align-items: center; 
              border: 3px solid #E67603; 
              border-radius: 8px; 
              padding: 10px; 
              cursor: hand; 
              margin-left: 10px;
              box-shadow: 3px 3px 5px grey;
            }
          </style>
        <?php
          function reconbutton($text, $id, $recondate, $recondate_desc) { ?>
            <div class="reconbutton"  id="<?=$id?>"
              onclick="submitForm('<?=$recondate?>', '<?=$recondate_desc?>')"> <!-- Rather than both buttons submitting form, each button will go to submitForm function and approratie instructions happen then --> 
              <div style="flex-grow: 0">
                <img src="noun_Earth_2199992.svg" width="50px" height="50px"/>
              </div>
              <div style="margin-left: 5px; flex-grow: 0; color: #E67603; font-family: arial">
                <?=$text?>
              </div>
            </div>
          <?php } ?>
          <form id="reconstruction_form" method="GET" action="<?=$_SERVER["REQUEST_URI"]?>&generateImage=1">
            <div style="display: flex; flex-direction: column; align-items: center">
              <div id="reconbutton-message" style="padding-bottom: 5px">
                Click to display on map of the Ancient World at:
              </div>
              <div style="display: flex; flex-direction: row; align-items: center; padding-bottom: 10px;">
                <?php
                  if ($_REQUEST["searchtype"] == "Date") {
                    reconbutton("$basepretty Ma", "reconbutton-base", $baseraw, 'on date');
                  } else if ($_REQUEST["searchtype"] == "Period") {
                    reconbutton("<b>Base</b> of $name<br/>($basepretty Ma)",  "reconbutton-base", $baseraw, 'base');
                    reconbutton("<b>Middle</b> of $name<br/>($middlepretty Ma)",  "reconbutton-middle", $middleraw, 'middle');
                  } else {
                    reconbutton("$basepretty Ma", "reconbutton-base", $baseraw, 'base');
                    reconbutton("$middlepretty Ma", "reconbutton-middle", $middleraw, 'middle');
                  }
                ?>
                <!-- <input type="submit" style="float:left;display:inline-block;" value="Press to Display on a Plate Reconstruction (<?=$_REQUEST["agefilterstart"]?> Ma)" /> -->
              </div>
              <div>
                <select id="selectModel"  name="selectModel" size="2" style="overflow: auto">
                  <option value="Default" <?php if ($_REQUEST["selectModel"] == "Default" || !$_REQUEST["selectModel"]) echo "SELECTED"; ?>>
                    Reconstruction Model: GPlates Default (Meredith, Williams, et al. 2021)
                  </option>
                  <!--      <option value="Chris" <?php if ($_REQUEST["selectModel"] == "Chris") echo "SELECTED"; ?>>Chris' Model</option> !-->
                  <option value="Marcilly" <?php if ($_REQUEST["selectModel"] == "Macilly") echo "SELECTED"; ?>>
                    Reconstruction Model: Continental flooding model (Marcilly, Torsvik et al., 2021)
                  </option> 
                </select> 
              </div>
   
              <?php /* Create placeholders for the buttons to fill in when they are clicked for middle/base */ ?>
              <input type="hidden" name="recondate" id="recondate" value="<?=$_REQUEST["recondate"]?>" />
              <input type="hidden" name="recondate_description" id="recondate_description" value="<?=$_REQUEST["recondate_description"]?>" />
              <?php foreach($_REQUEST as $k => $v) {?>
                <input type="hidden" name="<?=$k?>" id="<?=$k?>" value="<?=$v?>" />
              <?php } ?>
              <input type="hidden" name="generateImage" value="1" />
            </div>
          </form>
          <?php } ?>
        </div> <?php
      }

      ?><script type="text/javascript">
        // javascript function should control reconstruction that gets displayed 
        function submitForm(recondate, recondate_description) {
          document.getElementById('recondate').value = recondate;
          document.getElementById('recondate_description').value = recondate_description;
          document.getElementById('reconstruction_form').submit();
        }
      </script><?php

    ?> </div> <?php
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
    
    foreach($results as $regionname => $regioninfo) {?>
      <div class="formation-container" id="<?=$regionname?>">
        <h3 class="region-title"><?=$regionname?></h3>
        <hr>
  <div> <?php
   $sortByPeriod = array();
          foreach($regioninfo["groupbyprovince"] as $province => $provinceinfo) { ?>
            <hr> 
            <h3><?=$province?></h3>
            <div class="province-container"> <?php
              foreach($periodsDate as $p) {
          //echo $p["period"];
        foreach($provinceinfo["groupbyperiod"] as $pname => $formations) {
          if( $pname !== $p["period"]) continue; ?> 
                  <h5><?=$pname?></h5>
                  <div class="period-container"> <?php
                    foreach($formations as $fname => $finfo){
                      $finfoArr = json_decode(json_encode($finfo), true); ?>
                      <div style="background-color:rgb(<?=$stageArray[$finfoArr["stage"]]?>, 0.8);" class = "button">
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
  } 
} ?>

<?php
  if ($timedout) {
    ?>NOTE: Timed out awaiting external image creation, had to re-start<?php
  }
?>
</div>

