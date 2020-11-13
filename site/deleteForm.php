<?php
include("SqlConnection.php");
include("navBar.php");
include("SearchBar.php");
$title = $_POST["name"];echo $title;
$sql = "DELETE FROM formation WHERE name = '$title';";
echo "<pre> $sql <\pre>";

if($conn->query($sql)=== TRUE){
	//echo "data deleted";
}else {
	//echo "error during deletion";
}
header("Locaton:displayInfo.php");
?>
