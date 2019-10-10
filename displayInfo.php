<!DOCTYPE html>
<html>
<?php 	
include("SearchBar.php");
include("SqlConnection.php");
$formationName = $_REQUEST;
?>
<head>
	<title><?=$formationName[formation]?></title>
</head>
<body>
<?php
$sql = "SELECT * FROM formation WHERE name LIKE '%$formationName[formation]%'";
$result = mysqli_query($conn, $sql);
while($row = mysqli_fetch_array($result)) {
	$name = $row['name'];
        $period = $row['period'];
        $age_interval = $row['age_interval'];
        $province = $row['province'];
        $type_locality = $row['type_locality'];
        $lithology = $row['lithology'];
	$lower_contact = $row['lower_contact'];
	$upper_contact = $row['upper_contact'];
	$regional_extent = $row['regional_extent'];
	$fossils = $row['fossils'];
	$age = $row['age'];
	$depositional = $row['depositional'];
	$additional_info = $row['additional_info'];
	$compiler = $row['compiler'];
}

// display information below
?>

	<h1><b><?=$name?></b></h1>
    <hr>

	<h4><?=$period?></h4>
	<h4><?=$age_interval?></h4>
	<h4><?=$province?> <br></h4>

	<h3><b>Type Locality and Naming</b></h3>
    <hr>
	<p><?=$type_locality?> <br></p>

	<h3><b>Lithology and Thickness</b></h3>
    <hr>
	<p><?=$lithology?> <br></p>

	<h3><b>Relationships and Distribution</b></h3>
    <hr>
	<h4><i>Lower contact</i></h4>
	<p><?=$lower_contact?></p>
	<h4><i>Upper contact</i></h4>
	<p><?=$upper_contact?></p>
	<h4><i>Regional extent</i></h4>
	<p><?=$regional_extent?> <br></p>

	<h3><b>Fossils</b></h3>
    <hr>
	<p><?=$fossils?> <br></p>

	<h3><b>Age</b></h3>
    <hr>
	<p><?=$age?> <br></p>

	<h3><b>Depositional setting</b></h3>
    <hr>
	<p><?=$depositional?> <br></p>

	<h3><b>Additional Information</b></h3>
    <hr>
	<p><?=$additional_info?> <br></p>

	<h3><b>Compiler</b></h3>
    <hr>
	<p><?=$compiler?> <br></p>

</body>
</html>

