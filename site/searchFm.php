<?php
include("SqlConnection.php");

$arr = array();
$count = -1;
//Collect
//Within the single quotation marks is the name of the first field within the form
if (isset($_REQUEST['search'])) {
    $searchquery = $_REQUEST['search'];

    $sql = "SELECT * FROM formation WHERE name LIKE '%$searchquery%'";

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
        array_push($arr, $name);
        $output = '<h4>'.$name.'</h4>';
    }
    //}
}

?>


<!DOCTYPE html>
<html>

<link rel="stylesheet" href="style.css"/>


<title>Search for Formation</title>
<?php include("navBar.php"); include("SearchBar.php"); ?>

<div class="formation-container">
<?php
    if($count == -1) {
    } else if($count == 0) {
      $output = '<h4>'.'Formation not found'.'</h4>';
      print($output);
    } else if ($count == 1) {
      header("Location: displayInfo.php?formation=".$arr[0]);
      exit(0);
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
