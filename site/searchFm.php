<?php
global $conn;
include_once("SqlConnection.php");
include_once("TimescaleLib.php");
$arr = array();
$count = -1;
$sql2 = "SELECT province FROM formation";
$result = mysqli_query($conn, $sql2);
$province_list = array_unique($result);

//Collect
//Within the single quotation marks is the name of the first field within the form
if (isset($_REQUEST['search'])) {
  $searchquery = addslashes($_REQUEST['search']);
  $provincefilter = addslashes($_REQUEST['provincefilter']);
  $periodfilter = addslashes($_REQUEST['periodfilter']);
  $agefilterstart = addslashes($_REQUEST['agefilterstart']);
  $agefilterend = addslashes($_REQUEST['agefilterend']);
  $lithofilter = addslashes($_REQUEST['lithoSearch']);

  if (!$searchquery) {
    $searchquery = "";
  }
  if (!$periodfilter || $periodfilter == "All") {
    $periodfilter = "";
  }
  if (!$provincefilter || $provincefilter == "All") {
    $provincefilter = "";
  }
  if (!isset($_REQUEST['agefilterend']) || $agefilterend == "" || $agefilterstart < $agefilterend) {
    $agefilterend = $agefilterstart;
  }

  //base string
  //original string
  $sql = "SELECT * "
    ."FROM formation "
    ."WHERE (name LIKE '%$searchquery%' ";
  $apostrophes = array(
    "’",  // fancy apostrophe
    "'",  // regular apostrophe
    "’",
    "’",
  );
  $allapostrophes = join($apostrophes, '');
  $regex = "/[$allapostrophes]/";
  if (preg_match($regex, $searchquery)) {
    foreach ($apostrophes as $apos) {
      $sql .= "OR name LIKE '%".preg_replace($regex, $apos, $searchquery)."%' ";
    }
  }
  $sql .= ") ";
  $sql .= "AND period LIKE '%$periodfilter%' "
    ."AND province LIKE '%$provincefilter%' ";
  if (strcmp($lithofilter, "") === 0) {
    $sql .= "AND lithology LIKE '%$lithofilter%' ";
  } else {
    // used if user wants to use boolean logic
    $lithofilter_lower = strtolower($lithofilter); // lowercase the lithofilter

    if (strpos($lithofilter_lower, ' and ') !== false) { // if the user wants to search with 'and'
      $lithofilter_array = explode(" and ", $lithofilter_lower);
      foreach ($lithofilter_array as $value) {
        $sql .= "AND lithology LIKE '%$value%' ";
      }
    } else if (strpos($lithofilter_lower, ' or ') !== false ) { //if the user wants to search with 'or'
      $lithofilter_array = explode(" or ", $lithofilter_lower);
      $index = 0;
      foreach ($lithofilter_array as $value) {
        if ($index === 0) {
          $sql .= "AND lithology LIKE '%$value%' ";
        } else {
          $sql .= "OR lithology LIKE '%$value%' ";
        }
      }
    } else { // user does not want to search and/or
      $sql .= "AND lithology LIKE '%$lithofilter%' ";
    }
  }

  preg_replace("+", "%", $searchquery);

  if ($agefilterstart != "") {
    $sql .= "AND NOT (beg_date < $agefilterend " // the cast make sure a float is compared with a float
      ."OR end_date > $agefilterstart) "
      ."AND beg_date != '' " // with 0 Ma formations without a beginning date and end date get returned (this avoids that)
      ."AND end_date != '' "; // same comment as line above
  }

  $result = mysqli_query($conn, $sql);

  /* ---------- Debugging ---------- */
  // echo '<pre>'."HERE'S THE SQL QUERY".'</pre>';
  // echo '<pre>'.$sql.'</pre>';
  /* ---------- Debugging ---------- */

  $count = mysqli_num_rows($result);
  $noFormation = false;

  if ($count == 0) {
    $synonOutput = '';
    
    // if formation name is not found, search Synonymns
    $sql = "SELECT * "
      ."FROM formation "
      ."WHERE type_locality LIKE '%$searchquery%' "
      ."AND period LIKE '%$periodfilter%' "
      ."AND province LIKE '%$provincefilter%' "
      ."AND NOT (beg_date < $agefilterend "
      ."OR end_date > $agefilterstart) "
      ."AND beg_date != '' "
      ."AND end_date != '' ";
    $result = mysqli_query($conn, $sql);

    $count = mysqli_num_rows($result);
    if ($count != 0) {
      $synonOutput .= '<pre><h5>'.'No formations were found in the main Fm/Gr field...'.'</h5></pre>';
      $synonOutput .= '<pre><h5>'.'However \''.$searchquery. '\' was found in Synonymns field and other occurences of Type Locality and Naming Field'.'</h5></pre>';
      $synonOutput .= '<hr>';
    } else {
      $noFormation = true;
    }
  }

  $formationLookup = array();
  $count = 0;
  while ($row = mysqli_fetch_array($result)) {
    $name = $row['name'];
    $stage = $row['beginning_stage'];
    $begAge = $row['beg_date'];
    $geojson = $row['geojson'];

    if (strlen($name) < 1) {
      continue;
    }

    $nameObj = [
      'name' => $name,
      'beginning age' => $begAge,
      'geojson' => $geojson
    ];
    array_push($arr, $nameObj);
    $formationLookup[$name] = $stage;
    $output = '<h4>'.$name.'</h4>';
    $count++;
  }
}

uasort($arr, "sortByAge");
$displayAlphabetButton = true;
if (isset($_REQUEST['alphabetButton'])) {
  sort($arr);
  $displayAlphabetButton = false;
}

$newArr = array();
foreach ($arr as $arrayNum => $finfo) {
  array_push($newArr, $finfo["name"]);
}

$newGeoArr = array();
foreach ($arr as $arrayNum => $finfo) {
  array_push($newGeoArr, $finfo["geojson"]);
}

if (isset($_REQUEST['timeButton'])) {
  $displayAlphabetButton = true;
}

function sortByAge($a, $b) {
  $a1 = $a['beginning age'];
  $a1 = str_replace(",", "", $a1);
  $b1 = $b['beginning age'];
  $b1 = str_replace(",", "", $b1);

  if ($a1 == $b1) {
    return 0;
  }
  return ($a1 < $b1) ? -1: 1;
}

/*
 * CODE from general.php: This code uses the excel lookup table that can be found in the admin website,
 * and extracts the stages out of it as well as the RGB and creates an array with keys where the key
 * represents the stage and the value is the RGB color code. This is stored in $stageArray
 */
// get all of the associated stage data
$info = parseDefaultTimescale();
$stageConversion = array();
$storedStage = "none";
$count = 0; // used for indexing through the stageConversion array
foreach ($info as $element) {
  foreach ($element as $key => $val) {
    if($key == "stage"){
      array_push($stageConversion, array($val => "none"));
      $storedStage = $val;
    }
    if ($key == "color") {
      $stageConversion[0][$storedStage] = str_replace('/', ', ',  $val);
      $count++;
    }
  }
}
$stageArray = $stageConversion[0]; // stores the stages as well as the lookup in RGB
?>

<!DOCTYPE html>
<html>

<link rel="stylesheet" href="style.css"/>

<title>Search for Formation</title>
<?php
include("navBar.php");
include("SearchBar.php");
?>

<form method="post" style="padding-bottom: 20px;">
  <input
    type="submit"
    style="color:#b75f02; border: 1px solid #b75f02; border-radius: 3px; font-size: 1em; box-shadow: 5px 5px 8px #888888; background-color: #FFFFFF;"
    name="<?php echo $displayAlphabetButton ? 'alphabetButton' : 'timeButton'; ?>"
    value="<?php echo $displayAlphabetButton ? 'Change to Alphabetical Listing' : 'Change to By-Age Listing'; ?>"
  />
</form>

<div class="formation-container"> <?php
  if ($count < 1) {
    $output = '<h4>'.'Formation not found'.'</h4>';
    print($output);
  } else if ($noFormation && $lithofilter !== "") {
    $output = '<h4>'.'There are no formations found with \''.$lithofilter. '\''.'</h4>';
    print($output);
  } else {
    if ($synonOutput != '') {
      $synonOutput .= '</br> ';
      $synonOutput .= '</br> ';
      print($synonOutput);
    }

    $geojsonIndex = 0;
    foreach ($newArr as $formation) { ?>
      <div class="formationitem" style="background-color: rgb(<?=$stageArray[$formationLookup[$formation]]?>, 0.8);"> <?php
        if ($newGeoArr[$geojsonIndex] !== "null") { ?>
          <div style="padding-right: 10px; font-size: 13px;">&#127758</div> <?php
        } ?>
        <a href="displayInfo.php?formation=<?=$formation?>" target="_blank"><?=$formation?></a>
      </div> <?php
      $geojsonIndex++;
    }
  } ?>
</div>

</body>
</html>
