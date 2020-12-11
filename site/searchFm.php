<?php
include("SqlConnection.php");

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
    while ($row = mysqli_fetch_array($result)){
        $name = $row['name'];
        if (strlen($name) < 1) continue;
        array_push($arr, $name);
        $output = '<h4>'.$name.'</h4>';
    }
    //}

    if ($count == 1) {
      header("Location: displayInfo.php?formation=".$arr[0]);
    }

    sort($arr);    
}

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
      foreach ($arr as $formation) { ?>
        <div class="formationitem">
        <a href="displayInfo.php?formation=<?=$formation?>"><?=$formation?></a>
        </div><?php
      }
    } ?>
</div>

</body>
</html>
