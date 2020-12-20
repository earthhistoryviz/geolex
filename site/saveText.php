<?php
include_once("SqlConnection.php");

$arr = array();
$count = -1;
//Collect
//Within the single quotation marks is the name of the first field within the form
echo $_POST;
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

<style type="text/css">
    .formation-container {
        margin: auto;
	text-align: center;
        width: 65%;
	column-count:3;        
        padding: 5px;
	
    }
a{font-size: 20px;}

</style>

<title>Search for Formation</title>
<?php include("navBar.php"); include("SearchBar.php"); ?>

<div class="formation-container">
<?php 
	//print_r(array_values($arr));
	//print_r($count);
	if($count == -1){
		
	}
	else if($count == 0){
		$output = '<h4>'.'Formation not found'.'</h4>';
		print($output);
	}
        else if ($count == 1) {
          header("Location: displayInfo.php?formation=".$arr[0]); 
          exit(0);
        }
	else{
	}
	foreach ($arr as $formation)
	{
		//$output = '<h4>'.$formation.'</h4>';
		//print($output);
		?><b><br><a href="displayInfo.php?formation=<?=$formation?>"><?=$formation?></a></br></b><?php
	}?>
</div>

</body>
</html>


