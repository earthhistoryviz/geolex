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
    $searchquery = ($_REQUEST['search']);

    $provincefilter = $_REQUEST['provincefilter'];
    // This is a quick fix to help where whitespace gets surrounded by parsed HTML tags.
    $provincefilter = preg_replace('/ /', '%', $provincefilter);
    $periodfilter = ($_REQUEST['periodfilter']);
    $agefilterstart = ($_REQUEST['agefilterstart']);
    $agefilterend =  ($_REQUEST['agefilterend']);
    $lithofilter = ($_REQUEST['lithoSearch']);

    if (!isset($_REQUEST['agefilterend']) || $agefilterend == "" || $agefilterstart < $agefilterend) {
      $agefilterend = $agefilterstart;
    }
 

    //base string 
    //original string
    $sql = "SELECT * FROM formation WHERE (name LIKE '%$searchquery%'";
    if (preg_match("/’/", $searchquery)) {
      $sql .= " OR name LIKE \"%".preg_replace("/’/", "'", $searchquery)."%\")";
    } else {
      $sql .= ")";
    }
    $sql .= " AND period LIKE '%$periodfilter%' AND province LIKE '%$provincefilter%'";

    if(strcmp($lithofilter, "") === 0) {
      $sql .= " AND lithology LIKE '%$lithofilter%'";

    } else {
      //used if user wants to use boolean logic
      
      $lithofilter_lower = strtolower($lithofilter); //lowercase the lithofilter

      //if the user wants to search with 'and'
      if(strpos($lithofilter_lower, ' and ') !== false ) {
        $lithofilter_array = (explode(" and ", $lithofilter_lower));

        foreach($lithofilter_array as $value) {
          $sql .= " AND lithology LIKE '%$value%'";
        }
        
      } elseif (strpos($lithofilter_lower, ' or ') !== false ) { //if the user wants to search with 'or'
        # code...
        $lithofilter_array = (explode(" or ", $lithofilter_lower));
      
        foreach($lithofilter_array as $value) {
          $sql .= " OR lithology LIKE '%$value%'";
        }
            
      } else {
        $sql .= " AND lithology LIKE '%$lithofilter%'";
      }
  }

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
    $noFormation = false;

    if($count == 0){

      $synonOutput = '';
        
      #if formation name is not found, search Synonymns 
      $sql = "SELECT * FROM formation WHERE type_locality LIKE '%$searchquery%' AND period LIKE '%$periodfilter%' AND province LIKE '%$provincefilter%'";
      $result = mysqli_query($conn, $sql);
      $count = mysqli_num_rows($result);

      if($count != 0) {
        $synonOutput .= '<pre><h5>'.'No formations found in the main Fm/Gr field...' . '</h5></pre>';
        $synonOutput .= '<pre><h5>'. 'However \''.$searchquery. '\' was found in Synonymns field and other occurences of Type Locality and Naming Field'.'</h5></pre>';
        $synonOutput .= '<hr>';
      } else {
        $noFormation = true;
      }
    
    }
    
    $formationLookup = array();
    $count = 0;
    while ($row = mysqli_fetch_array($result)){
      $name = $row['name'];
      $stage = $row['beginning_stage'];
      $begAge = $row['beg_date'];
      $geojson = $row['geojson'];

      if (strlen($name) < 1) continue;
      //array_push($arr, $name);
      
      $nameObj =  [
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

function sortByAge($a, $b){
  $a1 = $a['beginning age'];
  $a1 = str_replace(",", "", $a1);
  $b1 = $b['beginning age'];
  $b1 = str_replace(",", "", $b1); 

  if($a1 == $b1) return 0;
  return ($a1 < $b1) ? -1: 1;

}

uasort($arr, "sortByAge");
$displayAlphabetButton = true;



if (isset($_REQUEST['alphabetButton'])) {
  sort($arr);
  $displayAlphabetButton = false;
}


$newArr = array();
foreach($arr as $arrayNum => $finfo){
   array_push($newArr, $finfo["name"]);
}

$newGeoArr = array();
foreach($arr as $arrayNum => $finfo){
  array_push($newGeoArr, $finfo["geojson"]);
}



if (isset($_REQUEST['timeButton'])) {
  $displayAlphabetButton = true;
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
 
?>


<!DOCTYPE html>
<html>

<link rel="stylesheet" href="style.css"/>


<title>Search for Formation</title>
<?php include("navBar.php"); include("SearchBar.php"); ?>
<?php if($displayAlphabetButton) { ?>
    <form method="post">
        <input type="submit" style="color:#b75f02; border: 1px solid #b75f02;border-radius: 3px; font-size: 1em; box-shadow: 5px 5px 8px #888888; background-color: #FFFFFF;" name="alphabetButton"
                value="Change to Alphabetical Listing"/>
    </form>
  <?php	} else { ?>
    <form method="post">
        <input type="submit" style="color:#b75f02; border: 1px solid #b75f02;border-radius: 3px; font-size: 1em; box-shadow: 5px 5px 8px #888888; background-color: #FFFFFF" name="timeButton"
                value="Change to By-Age Listing"/>
    </form>

  <?php	}?>
  <br>

<div class="formation-container">  
<?php  
  
    if($count < 1) {
      $output = '<h4>'.'Formation not found'.'</h4>';
      print($output);
    } else if ($noFormation && $lithofilter !== "") {
      $output = '<h4>'. 'There are no formation found with \''.$lithofilter. '\''.'</h4>';
      print($output);
    }else {     
      if ($synonOutput != '') {
        $synonOutput .= '</br> ';
        $synonOutput .= '</br> ';
        print($synonOutput);
      }  
      
      $geojsonIndex = 0;
      foreach ($newArr as $formation) {           

        ?> 
        
        <div style="background-color:rgb(<?=$stageArray[$formationLookup[$formation]]?>, 0.8);" class="formationitem">
        <?php

        if($newGeoArr[$geojsonIndex] !== "null") {?>
    
          <div style="padding-right: 10px; font-size:13px;">&#127758</div>    
          <?php } ?>    
          <a href="displayInfo.php?formation=<?=$formation?>"><?=$formation?></a>
	        </div><?php	
        
        $geojsonIndex = $geojsonIndex + 1; }
    } ?>
</div>

</body>
</html>
