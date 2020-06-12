<?php
include("SqlConnection.php");
$title = $_POST["title"];
echo $title;
$sql = "DELETE FROM formation WHERE name = '$title';";
echo "<pre> $sql <\pre>";

if($conn->query($sql)=== TRUE){
	echo "data deleted";
}else {
	echo "error during deletion";
}
header("Locaton:displayInfo.php");
?>
