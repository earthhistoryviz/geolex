<?php

include_once("SqlConnection.php");
$searchquery = addslashes($_REQUEST['searchquery']);
$periodfilter = addslashes($_REQUEST['periodfilter']);
$filterprovince = $_REQUEST['filterprovince'];
$agefilterstart = addslashes($_REQUEST['agefilterstart']);
$agefilterend = addslashes($_REQUEST['agefilterend']);
$lithofilter = addslashes($_REQUEST['lithoSearch']);
$fossilfilter = addslashes($_REQUEST['fossilSearch']);

if (!$searchquery) {
    $searchquery = "";
}
if (!$periodfilter || $periodfilter == "All") {
    $periodfilter = "";
}

$apostrophes = array(
  "’",  // fancy apostrophe
  "'",  // regular apostrophe
  "’",
  "’",
);
$allapostrophes = join('', $apostrophes);
$regex = "/[$allapostrophes]/";

$sql = "SELECT * "
  ."FROM formation "
  ."WHERE (name LIKE '%$searchquery%' ";
if (preg_match($regex, $searchquery)) {
    foreach ($apostrophes as $apos) {
        $sql .= "OR name LIKE '%".preg_replace($regex, $apos, $searchquery)."%' ";
    }
}
$sql .= ") ";
$sql .= "AND period LIKE '%$periodfilter%' ";
if ($filterprovince && !in_array("All", $filterprovince)) {
    $provinces = array_map(function ($province) use ($conn) {
        return "province LIKE '%" . mysqli_real_escape_string($conn, $province) . "%'";
    }, $filterprovince);

    if (count($provinces) > 0) {
        $sql .= "AND (" . implode(" OR ", $provinces) . ") ";
    }
} else {
    // this is all
}

$litho = "";
if (strcmp($lithofilter, "") === 0) {
    $litho .= "AND lithology LIKE '%$lithofilter%' ";
} else {
    $lithofilter_lower = strtolower($lithofilter);

    if (strpos($lithofilter_lower, ' and ') !== false) { // if the user wants to search with 'and'
        $lithofilter_array = explode(' and ', $lithofilter_lower);
        foreach ($lithofilter_array as $value) {
            $litho .= "AND lithology LIKE '%$value%' ";
        }
    } elseif (strpos($lithofilter_lower, ' or ') !== false) { // if the user wants to search with 'or'
        $lithofilter_array = explode(' or ', $lithofilter_lower);
        $index = 0;
        foreach ($lithofilter_array as $value) {
            if ($index === 0) {
                $litho .= "AND lithology LIKE '%$value%' ";
            } else {
                $litho .= "OR lithology LIKE '%$value%' ";
            }
            $index++;
        }
    } else { // user does not want to search and/or
        $litho .= "AND lithology LIKE '%$lithofilter%' ";
    }
}
$sql .= $litho;

$fossil = "";
if (strcmp($fossilfilter, "") === 0) {
    $fossil .= "AND fossils LIKE '%$fossilfilter%' ";
} else {
    $fossilfilter_lower = strtolower($fossilfilter);

    if (strpos($fossilfilter_lower, ' and ') !== false) { // if the user wants to search with 'and'
        $fossilfilter_array = explode(' and ', $fossilfilter_lower);
        foreach ($fossilfilter_array as $value) {
            $fossil .= "AND fossils LIKE '%$value%' ";
        }
    } elseif (strpos($fossilfilter_lower, ' or ') !== false) { // if the user wants to search with 'or'
        $fossilfilter_array = explode(' or ', $fossilfilter_lower);
        $index = 0;
        foreach ($fossilfilter_array as $value) {
            if ($index === 0) {
                $fossil .= "AND fossils LIKE '%$value%' ";
            } else {
                $fossil .= "OR fossils LIKE '%$value%' ";
            }
            $index++;
        }
    } else { // user does not want to search and/or
        $fossil .= "AND fossils LIKE '%$fossilfilter%' ";
    }
}
$sql .= $fossil;
//echo $sql;

preg_replace("/\+/", "%", $searchquery);

$age_query = "";
if ($agefilterstart != "") {
    if ($agefilterend == "") {
        $age_query .= "AND ($agefilterstart BETWEEN " 
            ."CAST(REPLACE(end_date, ',', '') AS DECIMAL(10,2)) AND CAST(REPLACE(beg_date, ',', '') AS DECIMAL(10,2))) "
            ."AND beg_date != '' "
            ."AND end_date != '' ";
    } else {
        $age_query .= "AND NOT (CAST(REPLACE(beg_date, ',', '') AS DECIMAL(10,2)) < $agefilterend "
            ."OR CAST(REPLACE(end_date, ',', '') AS DECIMAL(10,2)) > $agefilterstart) "
            ."AND beg_date != '' "
            ."AND end_date != '' ";
    }
}

$sql .= $age_query;

$result = mysqli_query($conn, $sql);
/* ---------- Debugging ---------- */
// echo '<pre>'."HERE'S THE SQL QUERY".'</pre>';
// echo '<pre>'.$sql.'</pre>';
/* ---------- Debugging ---------- */

$isSynonym = false;

if ($result == false || mysqli_num_rows($result) == 0) {
    $isSynonym = true; // all things found here will be isSynonym = true
    // if formation name is not found, search Synonymns
    $sql = "SELECT * "
      ."FROM formation "
      ."WHERE type_locality LIKE '%$searchquery%' "
      ."AND period LIKE '%$periodfilter%' "
      ."AND province LIKE '%$provincefilter%' "
      .$litho
      .$fossil
      .$age_query;
    $result = mysqli_query($conn, $sql);
    /* ---------- Debugging ---------- */
    // echo '<pre>'."HERE'S THE SQL QUERY".'</pre>';
    // echo '<pre>'.$sql.'</pre>';
    /* ---------- Debugging ---------- */
}

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
    $geojsonData = json_decode(strip_tags($row["geojson"]), true);
    // Initialize properties array
    $properties = array(
      "NAME" => $name,
      "FROMAGE" => $begAge,
      "TOAGE" => $endAge
    );

    // Check the type of GeoJSON and handle accordingly
    if (isset($geojsonData['type']) && $geojsonData['type'] === 'Feature') {
        // It's a single Feature
        $geojsonData['properties'] = $properties;
    } elseif (isset($geojsonData['type']) && $geojsonData['type'] === 'FeatureCollection') {
        // It's a collection of Features
        foreach ($geojsonData['features'] as &$feature) {
            $feature['properties'] = $properties;
        }
    } else {
        //no geojson, do nothing
    }

    if (strlen($name) < 1) {
        continue;
    }

    $arr[$name] = array(
      "name" => $name,
      "endAge" => $endAge,
      "begAge" => $begAge,
      "province" => $province,
      "geojson" => $geojsonData,
      "period" => $period,
      "stage" => $stage,
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

function removeHTML($str)
{
    $str = trim(preg_replace("/<\/?[^>]+>/", "", $str));
    return $str;
}

function sortByProvince($a, $b)
{
    $a1 = $a["begAge"];
    $b1 = $b["begAge"];
    if ($a1 == "" && $b1 != "") {
        return 1;
    }
    if ($a1 != "" && $b1 == "") {
        return -1;
    }
    if ($a1 == $b1) {
        return 0;
    }
    return $a1 < $b1 ? -1 : 1;
}
