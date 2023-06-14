<?php
include_once("SqlConnection.php");
include("searchUtils.php");

header("Content-Type: application/json");

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

  if (array_key_exists("features", $output) && !(array_key_exists("properties", $output["features"][0]) || array_key_exists("properties", $output)) && $output) {
    // condition 1 of geojson processing
    $properties = array("NAME" => $name, "FROMAGE" => null, "TOAGE" => null); // creating properties array
    $appendProp["properties"] = $properties;
    array_splice($output["features"]["0"], 1, 0, $appendProp); // adding the properties array in with the geojson
    $output["features"]["0"]["properties"] = $output["features"]["0"][0]; // properties array in json is indexed with number rather than phrase "properties"
    unset($output["features"]["0"][0]); // renaming the key 0 to be properties instead
    krsort($output["features"]["0"]); // reverse sorting so that properties is in right place and pygplates can partition correctly
    $output = json_encode($output["features"]["0"], JSON_PRETTY_PRINT); // altering displayed geojson
  } else if (!(array_key_exists("features", $output)) && !(array_key_exists("properties", $output["features"][0]) ||array_key_exists("properties", $output)) && $output) {
    // condition 2 of geojson processing
    $properties = array("NAME" => $name, "FROMAGE" => null, "TOAGE" => null);
    $appendProp["properties"] = $properties;
    array_splice($output, 1, 0, $appendProp); // adding the properties array in with the geojson
    $output["properties"] = $output[0]; // properties array in json is indexed with number rather than phrase "properties"
    unset($output[0]); // renaming the key 0 to be properties instead
    krsort($output); // reverse sorting so that properties is in right place and pygplates can partition correctly
    $output = json_encode($output, JSON_PRETTY_PRINT);
  } else if ($output["type"] == "FeatureCollection") {
    // condition 3 of geojson processing
    // format with properties tag but each formation is feature collection
    $output["features"][0]["properties"]["NAME"] = $name;
    $output["features"][0]["properties"]["FROMAGE"] = null;
    $output["features"][0]["properties"]["TOAGE"] = null;
    $output = json_encode($output["features"][0], JSON_PRETTY_PRINT);
  } else {
    // condition 4 of geojson processing
    $output = json_encode($output, JSON_PRETTY_PRINT);
  }

  if (strlen($name) < 1) {
    continue;
  }

  $arr[$name] = array(
    "name" => $name,
    "endAge" => $endAge,
    "begAge" => $begAge,
    "province" => $province,
    "geojson" => json_decode($output),
    "period" => $period,
    "stage" => $stage,
    "ageFilterStart" => gettype($agefilterstart),
    "lithology_pattern" => $lithoPattern,
    "isSynonym" => $isSynonym,
  );

  // If long form requested, add all the other returned fields from the database:
  if ($_REQUEST["response"] === "long") {
    foreach ($row as $key => $val) {
      if ($arr[$name][$key]) {
        continue; // already have this in processed form
      }
      if (preg_match("/^[0-9]+$/", $key)) {
        continue; // the row response contains both string keys and numeric keys which duplicate the string key values.
      }
      $arr[$name][$key] = removeHTML($val);
    }
  }
}

uasort($arr, 'sortByProvince');
echo json_encode($arr);

?>
