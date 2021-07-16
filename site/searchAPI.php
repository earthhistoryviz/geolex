<?php
include_once("SqlConnection.php");
//file_put_contents("recon.json", '{"type": "FeatureCollection",');
/*
date_default_timezone_set("America/New_York");      
$store = date("Y_m_d_h:i:sa");
$filename =   $_REQUEST[agefilterstart]. "_". $_REQUEST[provincefilter]. "_". $store. ".geojson";
$encFilename = md5($filename). ".geojson";
 

$filename = "data/recon.geojson";
// *** PLACE 1 TO CHANGE THE FILE NAME *** 
file_put_contents($filename, '{
"type": "FeatureCollection",
"name": "Triassic strata_10Feb2021",
"crs": { "type": "name", "properties": { "name": "urn:ogc:def:crs:OGC:1.3:CRS84" } },
"features": [
	');
 */

$searchquery = addslashes($_REQUEST['searchquery']);
$periodfilter = addslashes($_REQUEST['periodfilter']);
$provincefilter = addslashes($_REQUEST['provincefilter']);
$agefilterstart = addslashes($_REQUEST['agefilterstart']);
$agefilterend =  addslashes($_REQUEST['agefilterend']);

if (!$searchquery) $searchquery = "";
if (!$periodfilter || $periodfilter == "All") $periodfilter = "";
if (!$provincefilter || $provincefilter == "All") $provincefilter = "";
if (!isset($_REQUEST['agefilterend']) || $agefilterend == "" || $agefilterstart < $agefilterend) {
  $agefilterend = $agefilterstart;
}

header("Content-Type: application/json");

function removeHTML($str) {
  $str = trim(preg_replace("/<\/?[^>]+>/","", $str));
  return $str;
}

function sortByProvince($a, $b){
	$a1 = $a['Beginning age'];
	$b1 = $b['Beginning age'];
        if($a1 == "" && $b1 != ""){
	   return 1;
	}
	if($a1 != "" && $b1 == ""){
           return -1;
	}
	if($a1 == $b1) return 0;
	return ($a1 < $b1) ? -1: 1;
}

preg_replace("+", "%", $searchquery);
$sql = "SELECT * "
      ."  FROM formation "
      ." WHERE name LIKE '%$searchquery%' "
      ."       AND period LIKE '%$periodfilter%' "
      ."       AND province LIKE '%$provincefilter%'";

if ($agefilterstart != "") {
  $sql .= "       AND NOT (beg_date < $agefilterend" // the cast make sure a float is compared with a float
	  ."                OR end_date > $agefilterstart)"
	  ."      AND beg_date != ''" // with 0 ma formatins without a beginning date and end date get returned (this avoids that)
          ."      AND end_date != ''"; // same comment as line above
}


$result = mysqli_query($conn, $sql);
//echo '<pre>'."HERES THE SQL QUERY".'</pre>';
//echo '<pre>'.$sql.'</pre>';
$count = mysqli_num_rows($result);
$whileIter = 0; // checks if on the first run of the while loop for output file purposes
$arr = array();
$firstRun = 1; 
while ($row = mysqli_fetch_array($result)) {
  /*	
  if($whileIter != 0 && $row["geojson"] != ""){
         file_put_contents($filename, ", 
", FILE_APPEND);
  } */
  $name = $row["name"];
  $province = removeHTML($row['province']);
  $period = removeHTML($row['period']);
  $stage = removeHTML($row['beginning_stage']);
  $begAge = removeHTML($row['beg_date']);
  $endAge = removeHTML($row['end_date']);
  // geojson processing before writing to output file
  // format without properties tag 
  $output = json_decode(strip_tags($row["geojson"]), true);

  // condition 1 of geojson processing 
  if(array_key_exists("features", $output) && !(array_key_exists("properties", $output["features"][0]) ||array_key_exists("properties", $output)) && $output) {
  $properties = array("NAME" => $name, "FROMAGE" => null, "TOAGE" => null); // creating properties array
  $appendProp["properties"] = $properties;
  array_splice($output["features"]["0"], 1, 0, $appendProp); // adding the properties array in with the geojson
  $output["features"]["0"]["properties"] = $output["features"]["0"][0]; // properties array in json is indexed with number rather than phrase "properties"
  unset($output["features"]["0"][0]); // renaming the key 0 to be properties instead
  krsort($output["features"]["0"]); // reverse sorting so that properties is in right place and pygplates can partition correctly
  $output = json_encode($output["features"]["0"], JSON_PRETTY_PRINT); // altering displayed geojson
  }
  
  
  else if(!(array_key_exists("features", $output)) && !(array_key_exists("properties", $output["features"][0]) ||array_key_exists("properties", $output)) && $output){
  $properties = array("NAME" => $name, "FROMAGE" => null, "TOAGE" => null); 	  
  $appendProp["properties"] = $properties;
  array_splice($output, 1, 0, $appendProp); // adding the properties array in with the geojson
  $output["properties"] = $output[0]; // properties array in json is indexed with number rather than phrase "properties"
  unset($output[0]); // renaming the key 0 to be properties instead
  krsort($output); // reverse sorting so that properties is in right place and pygplates can partition correctly
  //echo "<pre>";
  //print_r($output);
  //echo "</pre>";
  $output = json_encode($output, JSON_PRETTY_PRINT);
  }
  
  
  // condition 3 of geojson processing 
  // format with properties tag but each formation is feature collection 
else if($output["type"] == "FeatureCollection"){
    $output["features"][0]["properties"]["NAME"] = $name;  
    $output["features"][0]["properties"]["FROMAGE"] = null;
    $output["features"][0]["properties"]["TOAGE"] = null;
    $output =  json_encode($output["features"][0], JSON_PRETTY_PRINT);
    
}
 // condition four of geojson format 
  else{
    $output = json_encode($output, JSON_PRETTY_PRINT);
  }
  /*
  if($row["geojson"] && $firstRun == 1){
	  file_put_contents($filename, 
		  $output, FILE_APPEND);
  $whileIter = 1; 
  $firstRun = 0;
  }
  else if($row["geojson"]){
  file_put_contents($filename, $output, FILE_APPEND);
  $whileIter = 1; 
  }
   */
  if (strlen($name) < 1) continue;
  $arr[$name] = array( "name" => $name, "endAge" => $endAge, "begAge" => $begAge, "province" => $province, "geojson" => $output, "period" => $period, "stage" => $stage, "ageFilterStart" => gettype($agefilterstart));
}
/*
file_put_contents($filename, "
]
}", FILE_APPEND);
//fclose($recongeoJSON);

if ($_REQUEST["generateImage"] == "1") {
  exec("./data/pygplates-pygmt_WenDu\'s\ playground.py ".$_REQUEST['agefilterstart'], $ending);
}
$last = "testing Fm";
$arr[$last] = $ending;
 */
uasort($arr, 'sortByProvince');
/*
while($count < count($arr)){
	$currentElement = $arr[$count];
	$name = $currentElement["name"];
	$arr[$name] = $arr[$count];
	unset($arr[$count]);
	$count = $count + 1;
}
 */
echo json_encode($arr);
?>
