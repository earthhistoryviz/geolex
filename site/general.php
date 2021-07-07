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

  $results = array();
  $recongeojson = '{
    "type": "FeatureCollection",
    "name": "Triassic strata_10Feb2021",
    "crs": { "type": "name", "properties": { "name": "urn:ogc:def:crs:OGC:1.3:CRS84" } },
    "features": [
	    ';
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
      if ($geo && $geo != "null") {
	      //echo $fname;
	
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
  
  //echo $recongeojson;
  //echo "<pre>";
  //print_r($periodsDate);
  //echo "</pre>";
  // Only create the output directory if we are generating an image:
  if ($_REQUEST["generateImage"]) {
    $toBeHashed = $recongeojson.$_REQUEST["agefilterstart"];
    //echo $toBeHashed;

    $outdirhash = md5($toBeHashed);
    // outdirname is what pygplates should see
    $outdirname = "livedata/$outdirhash";
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
    date_default_timezone_set("America/New_York");	  
    $store = date("Y_m_d_h:i:sa");
    
    if ($_REQUEST[agefilterstart] != "" && $_REQUEST[agefilterstart] == $_REQUEST[agefilterend]
	    || $_REQUEST[agefilterstart] != "" && $_REQUEST[agefilterend] == "") {
      //$testing = file_get_contents('testing.py', true);
	    //echo $testing;
    //  exec('./data/testing.py', $test);
   //  $filename =  $_REQUEST[agefilterstart]. "_". $_REQUEST[filterregion]. "_". $store; 
    //  echo $filename. "<br>";
   //  echo md5($filename); 
   //  echo "<pre>";
   //  print_r($test);
   //  echo "</pre>";     
     //$image_encode = shell_exec("base64 data/my-figure_2.png"); // TODO: This is for testing purpose. Actual base64 encoding should be done by pyGMT 
     ?>
      <div class="reconstruction">
        <?php if ($_REQUEST["generateImage"] == "1") {?>
          A very special thanks to the excellent <a href="https://gplates.org">GPlates</a> and their 
          <a href="https://www.gplates.org/docs/pygplates/pygplates_getting_started.html">pygplates</a> software as well as
          <a href="https://www.pygmt.org/latest/">PyGMT</a> which work together to create these images.
          <br/><br/>
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
      
      if ($initial_creation_outdir || $timedout) { // if this is the first time, or we timed out waiting for image, create it:
        // Otherwise, hash doesn't exist, so we need to spawn a pygplates to make it:
    	  exec("cd pygplates && ./master_run_pygplates_pygmt.py ".$_REQUEST['agefilterstart']." $outdirname", $ending);
      }
    //     $image_encode = shell_exec("base64 my-figure_2.png"); // TODO: This is for testing purpose. Actual base64 encoding should be done by pyGMT 
	 ?> <img src="<?=$outdirname_php?>/final_image.png" width ="80%" > <?php
      } else {
        // User selection of reconstruction model
        ?>
        Please select reconstruction model
        <select name="selectModel">
          <option value="Default"> Default Model</option>
          <option value="Chris">Chris' Model</option>
        </select>
       
          <form method="GET" action="<?=$_SERVER["REQUEST_URI"]?>&generateImage=1">
            <input type="submit" value="Press to Display on a Plate Reconstruction (<?=$_REQUEST["agefilterstart"]?> Ma)" style="padding: 5px;" />
            <?php foreach($_REQUEST as $k => $v) {?>
              <input type="hidden" name="<?=$k?>" value="<?=$v?>" />
            <?php } ?>
            <input type="hidden" name="generateImage" value="1" />
          </form>
        <?php } ?>
      </div>

    <?php
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
        ----------
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


