<?php 

function prepare() {
  global $retgeoJSON, $results, $header, $recongeojson, $firstRun, $url, $raw, $response,
         $recongeojsonmid, $firstform, $midAge, $geoJSONmid, $outdirname, $outdirname_php, $outdirhash, 
         $initial_creation_outdir, $reconfilename, $timedout;
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
  if(isset($_REQUEST["agefilterstart"]) && isset($_REQUEST["agefilterend"])) {
    foreach($geoJSONmid as $midForm){
      if ($midAge <= $midForm["begAge"] ||  $midAge >= $midForm["endAge"]) {
        $recongeojsonmid .= $midForm["geojson"];
        $recongeojsonmid .= ",";
 
 }
      $firstform = 0;
 
 }
 
 }
  $recongeojsonmid = substr($recongeojsonmid, 0, -1);

  $recongeojsonmid .= "]";
  $recongeojsonmid .= '}';
  // Only create the output directory if we are generating an image:
 
 if ($_REQUEST["generateImage"]) {
 
 if($_REQUEST["recondate_description"] == "on date" || $_REQUEST["recondate_description"] == "base") {
      switch($_REQUEST["selectModel"]){
        case "Default":  $toBeHashed = $recongeojson.$_REQUEST["agefilterstart"];
        default: $toBeHashed = $recongeojson.$_REQUEST["agefilterstart"].$_REQUEST["selectModel"];
  
  }
    } else if($_REQUEST["recondate_description"] == "middle") {
      switch($_REQUEST["selectModel"]){
        case "Default":  $toBeHashed = $recongeojsonmid.$_REQUEST["agefilterstart"];
        default: $toBeHashed = $recongeojsonmid.$_REQUEST["agefilterstart"].$_REQUEST["selectModel"];
  
  }
    } else if($_REQUEST["recondate_description"] == "middle") {
      switch($_REQUEST["selectModel"]){
        case "Default":  $toBeHashed = $recongeojsonmid.$_REQUEST["agefilterstart"];
        default: $toBeHashed = $recongeojsonmid.$_REQUEST["agefilterstart"].$_REQUEST["selectModel"];
  
  }
    } 
    $outdirhash = md5($toBeHashed);    
    // outdirname is what pygplates should see
    switch($_REQUEST["selectModel"]) {
      case  "Default": $outdirname = "livedata/default/$outdirhash"; break;
      case "Marcilly": $outdirname = "livedata/marcilly/$outdirhash"; break;
      case  "Scotese": $outdirname = "livedata/scotese/$outdirhash"; break;
      default:         $outdirname = "livedata/unknown/$outdirhash"; 
  
  }
    // and php is running one level up:
    $outdirname_php = "pygplates/$outdirname";
    $initial_creation_outdir = false; // did we have to make the output hash directory name?

    if (!file_exists($outdirname_php)) {
      $initial_creation_outdir = true;
      mkdir($outdirname_php, 0777, true);
  
  }
    $reconfilename = "$outdirname_php/recon.geojson";

    if (!file_exists($reconfilename)) {
      file_put_contents($reconfilename, $recongeojson);
    }
  }
}


function generateAndShowReconButtonsAndImage() {
  global $retgeoJSON, $results, $header, $recongeojson, $firstRun, $url, $raw, $response,
         $recongeojsonmid, $firstform, $midAge, $geoJSONmid, $outdirname, $outdirname_php, $outdirhash, 
         $initial_creation_outdir, $reconfilename, $timedout;

  

if ($_REQUEST["generateImage"] == "1") {
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

    // Run pygplates if either a) we had to make the hash folder because it didn't exist, or b) we timed out (try again)
  
  if ($initial_creation_outdir || $timedout) {
  

switch($_REQUEST["selectModel"]) {
        case "Default":
          exec("cd pygplates && ./master_run_pygplates_pygmt.py ".$_REQUEST['recondate']." $outdirname", $ending);
        break;
        case "Marcilly": 
          exec("cd pygplates && ./MarcillyModel.py ".$_REQUEST['recondate']." $outdirname", $ending);
        break;
        case "Scotese":
          exec("cd pygplates/ScoteseDocs && ./ScoteseModel.py ".$_REQUEST['recondate']." $outdirname", $ending);
        break;
      }
    }?>
 
    <div id="reconImg" align="center"><?php
  
  if($_REQUEST["searchtype"] == "Period" && $_REQUEST["filterstage"] != "All"){?>
        <figcaption style="text-align: center; font-size: 45px;"> Reconstruction for <?=$_REQUEST[recondate_description]?> of <?= $_REQUEST["filterstage"] ?> </figcaption><?php
      } else if($_REQUEST["searchtype"] == "Period") { ?>
        <figcaption style="text-align: center; font-size: 45px;"> Reconstruction for <?=$_REQUEST[recondate_description]?> of <?= $_REQUEST["filterperiod"] ?> </figcaption><?php
      } 


  
  if(file_exists($outdirname_php."/final_image.png")){ ?>
        <a href="<?=$outdirname_php?>/final_image.png">
          <img src="<?=$outdirname_php?>/final_image.png" style="text-align:center" width ="80%" />
        </a>
        <br/><br/>
        A very special thanks to the excellent <a href="https://gplates.org">GPlates</a> and their
        <a href="https://www.gplates.org/docs/pygplates/pygplates_getting_started.html">pyGPlates</a> software as well as
        <a href="https://www.pygmt.org/latest/">pyGMT</a> which work together to create these images.
   <?php   
   } else {
        echo "No available reconstruction image";

} 
?>
    </div> <?php
    // ENDED HERE: finish fixing the indentation and brackets
    } else if($_REQUEST["generateImage"] != "2") {
            // User selection of reconstruction model
            $baseraw = $_REQUEST["agefilterstart"];
            $basepretty = number_format($baseraw, 2);
            $topraw = $_REQUEST["agefilterend"];
            $toppretty = number_format($topraw, 2);


if ($_REQUEST["searchtype"] == "Period") {
              $middleraw = ($_REQUEST["agefilterstart"] + $_REQUEST["agefilterend"])/2.0;
              $middlepretty = number_format($middleraw, 2);
              //$useperiod = true;

if ($_REQUEST["filterstage"] && $_REQUEST["filterstage"] != "All") {
                $name = $_REQUEST["filterstage"];
              } else {
                $name = $_REQUEST["filterperiod"];




}

}?>

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
            </style><?php


function reconbutton($text, $id, $recondate, $recondate_desc) { ?>
              <div class="reconbutton"  id="<?=$id?>"
                onclick="submitForm('<?=$recondate?>', '<?=$recondate_desc?>')"> <!-- Rather than both buttons submitting form, each button will go to submitForm function and approratie instructions happen then --> 
                <div style="flex-grow: 0">
                  <img src="noun_Earth_2199992.svg" width="50px" height="50px"/>
                </div>
                <div style="margin-left: 5px; flex-grow: 0; color: #E67603; font-family: arial">
                  <?=$text ?>
                </div>
              </div>
<?php 
} ?>
