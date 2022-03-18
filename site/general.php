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

  // Sets up the reconstruction variables: $recongeojson, etc.:
 // include("./makeRecosntruction.php");

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

        //$results["china"][groupbyprovince][fujian][formations][taoziken Fm]
        //                                                      [Baratang Fm]
        //                                          [groupbyperiod][cretaceous][taoziken fm]
        //                                                        [jurassic  ][baratan fm]

        // Figure out which periods overlap this formation
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


if($_REQUEST["recondate_description"] == "on date" || $_REQUEST["recondate_description"] == "base"){




switch($_REQUEST["selectModel"]){
          case "Default":  $toBeHashed = $recongeojson.$_REQUEST["agefilterstart"];
          default: $toBeHashed = $recongeojson.$_REQUEST["agefilterstart"].$_REQUEST["selectModel"];

}
      

}
 


else if($_REQUEST["recondate_description"] == "middle"){


switch($_REQUEST["selectModel"]){
          case "Default":  $toBeHashed = $recongeojsonmid.$_REQUEST["agefilterstart"];
          default: $toBeHashed = $recongeojsonmid.$_REQUEST["agefilterstart"].$_REQUEST["selectModel"];



}



} 
 

    

if($_REQUEST["recondate_description"] == "base" && $_REQUEST["selectModel"] == "Marcilly") {    
      $toBeHashed = $recongeojson.$_REQUEST["agefilterstart"].$_REQUEST["selectModel"];
    } else if($_REQUEST["recondate_description"] == "base" && $_REQUEST["selectModel"] == "Default") {
      $toBeHashed = $recongeojson.$_REQUEST["agefilterstart"];




} 



if($_REQUEST["recondate_description"] == "middle"  && $_REQUEST["selectModel"] == "Marcilly"){
      $toBeHashed = $recongeojsonmid.(($_REQUEST["agefilterstart"] + $_REQUEST["agefilterend"])/2).$REQUEST["selectModel"];
    } else if($_REQUEST["recon_description"] == "middle"  && $_REQUEST["selectModel"] == "Default"){
      $toBeHashed = $recongeojsonmid.($_REQUEST["agefilterstart"] + $_REQUEST["agefilterend"])/2;




} 
      
  
    //$outdirhash = md5($toBeHashed);
    $outdirhash = md5($toBeHashed)."newestTest";   
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
  // End of old code from before reconstructionlib.php
// echo  prepare();

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


foreach($element as $key =>
$val) {
  



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

<?php // where old comment was


/*
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
                  //echo "<pre> Result: "; print_r($ending); echo "</pre>";
                break;



}




}?>
 
            <div id="reconImg" align="center"><?php



if($_REQUEST["searchtype"] == "Period" && $_REQUEST["filterstage"] != "All"){?>
                <figcaption style="text-align: center; font-size: 45px;"> Reconstruction for <?=$_REQUEST[recondate_description]?> of <?= $_REQUEST["filterstage"] ?> </figcaption><?php
              } else if($_REQUEST["searchtype"] == "Period") { ?>
                <figcaption style="text-align: center; font-size: 45px;"> Reconstruction for <?=$_REQUEST[recondate_description]?> of <?= $_REQUEST["filterperiod"] ?> </figcaption><?php


} 


if(file_exists($outdirname_php."/final_image.png")){?>
              <a href="<?=$outdirname_php?>/final_image.png">
                <img src="<?=$outdirname_php?>/final_image.png" style="text-align:center" width ="80%" />
              </a>
              <br/><br/>
              A very special thanks to the excellent <a href="https://gplates.org">GPlates</a> and their
              <a href="https://www.gplates.org/docs/pygplates/pygplates_getting_started.html">pyGPlates</a> software as well as
              <a href="https://www.pygmt.org/latest/">pyGMT</a> which work together to create these images.
            </div> <?php
            } else {
              echo "No available reconstruction image";

}
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


} */ ?>
            <style>




/*
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
              } */
            </style><?php
          //include_once("./generateRecon.php");
          include_once("./makeButtons.php");


/* // responsible for generating reconstruction image after button has been clicked 
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


<?php } */ ?>

            <!-- changes the link when clicked and adds the Click to display map phrase 
            <form id="reconstruction_form" method="GET" action="<?=$_SERVER["REQUEST_URI"]?>&generateImage=1">
              <div style="display: flex; flex-direction: column; align-items: center">
                <div id="reconbutton-message" style="padding-bottom: 5px">
                  Click to display on map of the Ancient World at:
                </div>
                <div style="display: flex; flex-direction: row; align-items: center; padding-bottom: 10px;"> !--><?php

/*
if ($_REQUEST["searchtype"] == "Date") {
                    reconbutton("$basepretty Ma", "reconbutton-base", $baseraw, 'on date');
                  } else if ($_REQUEST["searchtype"] == "Period") {
                    reconbutton("<b>Base</b> of $name<br/>($basepretty Ma)",  "reconbutton-base", $baseraw, 'base');
                    reconbutton("<b>Middle</b> of $name<br/>($middlepretty Ma)",  "reconbutton-middle", $middleraw, 'middle');
                  } else {
                    reconbutton("$basepretty Ma", "reconbutton-base", $baseraw, 'base');
                    reconbutton("$middlepretty Ma", "reconbutton-middle", $middleraw, 'middle');


} */?>
                <!--</div> --> <!-- DIV TAG NEEDS TO BE COMMENTED OUT OR WILL MESS WITH FORMATTING -->
                <!--<div> // gives options for the three different reconstruction models to choose from 
                  <select id="selectModel"  name="selectModel" size="3" style="overflow: auto">
                    <option value="Default" <?php if ($_REQUEST["selectModel"] == "Default" || !$_REQUEST["selectModel"]) echo "SELECTED"; ?>>
                      Reconstruction Model: GPlates Default (Merdith, Williams, et al., 2021)
                    </option> -->
                    <!--      <option value="Chris" <?php if ($_REQUEST["selectModel"] == "Chris") echo "SELECTED"; ?>>Chris' Model</option> !-->
                    <!--<option value="Marcilly" <?php if ($_REQUEST["selectModel"] == "Marcilly") echo "SELECTED"; ?>>
                      Reconstruction Model: Continental flooding model (Marcilly, Torsvik et al., 2021)
                    </option> 
                    <option value="Scotese" <?php if ($_REQUEST["selectModel"] == "Scotese") echo "SELECTED"; ?>>
                      Reconstruction Model: Paleo-topography (Chris Scotese, 2020)
                    </option> 
                  </select> 
                </div> -->
   
                <?php /* Create placeholders for the buttons to fill in when they are clicked for middle/base */ ?>
                <!--<input type="hidden" name="recondate" id="recondate" value="<?=$_REQUEST["recondate"]?>"  /> -->
                <!--<input type="hidden" name="recondate_description" id="recondate_description" value="<?=$_REQUEST["recondate_description"]?>" />  -->


<?php 

//foreach($_REQUEST as $k => $v) {?>
                 <!-- <input type="hidden" name="<?=$k?>" id="<?=$k?>" value="<?=$v?>" /> -->

 

<?php //} ?>
                <!--<input type="hidden" name="generateImage" value="1" /> -->
              <!--</div> -->
            <!--</form> -->






<?php } 
?>
        <!--</div> --> <?php




}

      ?><script type="text/javascript">
        // javascript function should control reconstruction that gets displayed 

/*
function submitForm(recondate, recondate_description) {
          document.getElementById('recondate').value = recondate;
          document.getElementById('recondate_description').value = recondate_description;
          document.getElementById('reconstruction_form').submit();
*/
}

      </script><?php

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
      <div class="formation-container">
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

foreach($formations as $fname => $finfo) {
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



//} 



} ?>

<?php
  if ($timedout) {
    ?>NOTE: Timed out awaiting external image creation, had to re-start<?php
  }
?>
</div>

