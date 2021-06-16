<?php
include_once("SqlConnection.php");
include_once("navBar.php");
include_once("SearchBar.php");
$title = $_POST["name"];echo $title;
//$sql = "DELETE FROM formation WHERE name = '$title';";
$sql = sprintf("DELETE FROM formation WHERE name='%s'", mysqli_real_escape_string($conn, $title));
echo "<pre> $sql <\pre>";

if($conn->query($sql)=== TRUE){
	//echo "data deleted";
}else {
	//echo "error during deletion";
}
header("Locaton:displayInfo.php");
?>
