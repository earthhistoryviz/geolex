<?php
include_once("SqlConnection.php");

$searchquery = addslashes($_REQUEST['searchquery']);
$periodfilter = addslashes($_REQUEST['periodfilter']);
$provincefilter = addslashes($_REQUEST['provincefilter']);
$agefilterstart = addslashes($_REQUEST['agefilterstart']);
$agefilterend =  addslashes($_REQUEST['agefilterend']);
$lithofilter = ($_REQUEST['lithoSearch']); 

if (!$searchquery) $searchquery = "";
if (!$periodfilter || $periodfilter == "All") $periodfilter = "";
if (!$provincefilter || $provincefilter == "All") $provincefilter = "";
if (!isset($_REQUEST['agefilterend']) || $agefilterend == "" || $agefilterstart < $agefilterend) {
  $agefilterend = $agefilterstart;
}

header("Content-Type: application/json");

//base string
$sql = "SELECT * "
  ."  FROM formation "
  ." WHERE name LIKE '%$searchquery%' "
  ."       AND period LIKE '%$periodfilter%' "
  ."       AND province LIKE '%$provincefilter%' ";

if(strcmp($lithofilter, "") === 0) {
  $sql .= "       AND lithology LIKE '%$lithofilter%' ";

} else {
        
  $lithofilter_lower = strtolower($lithofilter);

  //if the user wants to search with 'and'
  
  if(strpos($lithofilter_lower, ' and ') !== false ) {
    $lithofilter_array = (explode(" and ", $lithofilter_lower));
  
    foreach($lithofilter_array as $value) {
      $sql .= "       AND lithology LIKE '%$value%' ";
    }
  
    
  } elseif (strpos($lithofilter_lower, ' or ') !== false ) { //if the user wants to search with 'or'
    # code...
    $lithofilter_array = (explode(" or ", $lithofilter_lower));
  
    $index = 0;
    foreach($lithofilter_array as $value) {
      if ($index === 0) {
        $sql .= "       AND lithology LIKE '%$value%' ";
      } else {
        $sql .= "       OR lithology LIKE '%$value%' ";
      }
      $index++;
      
    }
  
  } else { //user does not want to search and/or
    $sql .= "       AND lithology LIKE '%$lithofilter%' ";
  }
}

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
if($count == 0) {
  $noFormation = true;
}
$whileIter = 0; // checks if on the first run of the while loop for output file purposes
$arr = array();
$firstRun = 1; 
while ($row = mysqli_fetch_array($result)) {
  $name = $row["name"];
  $province = removeHTML($row['province']);
  $period = removeHTML($row['period']);
  $stage = removeHTML($row['beginning_stage']);
  $begAge = removeHTML($row['beg_date']);
  $endAge = removeHTML($row['end_date']);
  $lithoPattern = removeHTML($row['lithology_pattern']);
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
  if (strlen($name) < 1) continue;
  $arr[$name] = array( 
    "name" => $name, 
    "endAge" => $endAge, 
    "begAge" => $begAge, 
    "province" => $province, 
    "geojson" => json_decode($output), 
    "period" => $period, 
    "stage" => $stage, 
    "ageFilterStart" => gettype($agefilterstart), 
    "lithology_pattern" => $lithoPattern
  );

  // If long form requested, add all the other returned fields from the database:
  if ($_REQUEST["response"] === "long") {
    foreach($row as $key => $val) {
      if ($arr[$name][$key]) continue; // already have this in processed form
      if (preg_match("/^[0-9]+$/", $key)) continue; // the row response contains both string keys and numeric keys which duplicate the string key values.  
      $arr[$name][$key] = removeHTML($val);
    }
  }
}
uasort($arr, 'sortByProvince');
echo json_encode($arr);
?>
