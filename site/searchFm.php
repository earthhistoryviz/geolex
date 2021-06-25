<?php
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

    $sql = "SELECT * FROM formation WHERE name LIKE '%$searchquery%' AND period LIKE '%$periodfilter%' AND province LIKE '%$provincefilter%'";
    $result = mysqli_query($conn, $sql);
    //echo '<pre>'."HERES THE SQL QUERY".'</pre>';
    //echo '<pre>'.$sql.'</pre>';
    $count = mysqli_num_rows($result);


    //if($count == 0){
      //  $output = '<h4>'.'Formation not found'.'</h4>';
    //}
    //else{
    $formationLookup = array();
    $count = 0;
    while ($row = mysqli_fetch_array($result)){
	$name = $row['name'];
	$stage = $row['beginning_stage'];
	$begAge = $row['beg_date'];
	//echo $begAge;
	//echo $stage;
	if (strlen($name) < 1) continue;
	//array_push($arr, $name);
	
	$nameObj =  [
	  'name' => $name,
	  'beginning age' => $begAge
  ];    
	array_push($arr, $nameObj);
	$formationLookup[$name] = $stage;
	$output = '<h4>'.$name.'</h4>';
        $count++;
    }
    //}
    /* 
    if ($count == 1) {
      header("Location: displayInfo.php?formation=".$arr[0]);
    }*/

}

function sortByAge($a, $b){
        $a1 = $a['beginning age'];
        $b1 = $b['beginning age'];

        if($a1 == $b1) return 0;
        return ($a1 < $b1) ? -1: 1;

}

uasort($arr, "sortByAge");

$newArr = array();
foreach($arr as $arrayNum => $finfo){
   array_push($newArr, $finfo["name"]);
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
/*
echo "<pre>";
print_r($stageArray);
echo "</pre>";
*/ 
?>


<!DOCTYPE html>
<html>

<link rel="stylesheet" href="style.css"/>


<title>Search for Formation</title>
<?php include("navBar.php"); include("SearchBar.php"); ?>

<div class="formation-container">
<?php
    if($count < 1) {
      $output = '<h4>'.'Formation not found'.'</h4>';
      print($output);
    } else {
	foreach ($newArr as $formation) { ?>
        <div style="background-color:rgb(<?=$stageArray[$formationLookup[$formation]]?>, 0.8);" class="formationitem">
        <a href="displayInfo.php?formation=<?=$formation?>"><?=$formation?></a>
	</div><?php	
      }
    } ?>
</div>

</body>
</html>
