<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "myDB";
$output = '';

$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
else{
    echo '<pre>'.'successfully linked to Datbase'.'</pre>';
}
$arr = array();
//Collect
//Within the single quotation marks is the name of the first field within the form
if (isset($_POST['search'])) {
    $searchquery = $_POST['search'];

    $sql = "SELECT * FROM formation WHERE name LIKE '%$searchquery%'";
    
    $result = mysqli_query($conn, $sql);
    echo '<pre>'."HERES THE SQL QUERY".'</pre>';
    echo '<pre>'.$sql.'</pre>';
    $count = mysqli_num_rows($result);
    echo $count;
    
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

< ?php header("Location: http://www.google.com ? formation=$name"); ?>

<!DOCTYPE html>
<html>
<title>Search for Formation</title>

<style>
    #searchbar {
        border: 3px solid #CC99FF;
        height: 40px;
        width: 300px;
    }
    #submitbtn {
        height: 40px;
        border: 3px solid #000000;
    }
    
</style>

<body>
<h3><b>Enter Formation Name Below: </b></h3>
<form action="searchFm.php" method="post">
    <input id="searchbar" type="text" name="search" placeholder="Search Formation Name...">
    <input id="submitbtn" type="submit" value="Submit">
</form>
<div>
	<hr>
<?php 
	//print_r(array_values($arr));
	if($count == 0){
		$output = '<h4>'.'Formation not found'.'</h4>';
		print($output);
	}
	foreach ($arr as $formation)
	{
		$output = '<h4>'.$formation.'</h4>';
		print($output);
	}
//print($arr[0]);
//print("$output");?>
</hr>
</div>

</body>
</html>


