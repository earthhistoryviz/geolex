<?php
include("SqlConnection.php");

$arr = array();
//Collect
//Within the single quotation marks is the name of the first field within the form
if (isset($_POST['search'])) {
    $searchquery = $_POST['search'];

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
            //$period = $row['period'];
            //$age_interval = $row['age_interval'];
            //$province = $row['province'];
            //$type_locality = $row['type_locality'];
            //$lithology = $row['lithology'];
            //$lower_contact = $row['lower_contact'];
            array_push($arr, $name);
            $output = '<h4>'.$name.'</h4>';

        }
    //}
}

?>


<!DOCTYPE html>
<html>
<title>Search for Formation</title>

<?php include("SearchBar.php"); ?>

<div>
<?php 
	//print_r(array_values($arr));
	if($count == 0){
		$output = '<h4>'.'Formation not found'.'</h4>';
		print($output);
	}
        if ($count == 1) {
          header("Location: displayInfo.php?formation=".$formation); 
          exit(0);
        }
	foreach ($arr as $formation)
	{
		//$output = '<h4>'.$formation.'</h4>';
		//print($output);
		?><b><hr><a href="displayInfo.php?formation=<?=$formation?>">Formation: <?=$formation?></a></hr></b><?php
</div>

</body>
</html>


