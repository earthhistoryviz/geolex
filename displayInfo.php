<!DOCTYPE html>
<html>
<?php 	include("SearchBar.php");
	include("SqlConnection.php");
?>
<head>
	<title>Amunik Fm</title>
</head>
<body>
<?php echo "_GET[formation]";
print_r($_REQUEST);
$formationName = $_REQUEST;
//echo $formationName[formation];
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
?>








	<h1><b><?=$name?></b></h1>
    <hr>
    <br>

	<h3><b>Period</b></h3>
    <hr>
	<p><?=$period?></p>
    <br>
	<h3><b>Age Interval (Map column)</b></h3>
    <hr>
	<p><?=$age_interval?></p>
    <br>
	<h3><b>Province</b></h3>
    <hr>
	<p><?=$province?></p>
    <br>
	<h3><b>Type Locality and Naming</b></h3>
    <hr>
	<p><?=$type_locality?> </p>
    <br>
	<h3><b>Lithology and Thickness</b></h3>
    <hr>
	<p><?=$lithology?></p>
    <br>
	<h3><b>Relationships and Distribution</b></h3>
    <hr>
	<h4><i>Lower contact</i></h4>
	<p><?=$lower_contact?></p>
	<h4><i>Upper contact</i></h4>
	<p><?=$upper_contact?></p>
	<h4><i>Regional extent</i></h4>
	<p><?=$regional_extent?></p>
    <br>
	<h3><b>Fossils</b></h3>
    <hr>
	<p><?=$fossils?>
    <br>
	<h3><b>Age</b></h3>
    <hr>
	<p><?=$age?></p>
    <br>
	<h3><b>Depositional setting</b></h3>
    <hr>
	<p><?=$depositional?></p>
    <br>
	<h3><b>Additional Information</b></h3>
    <hr>
	<p><?=$additional_info?></p>
    <br>
	<h3><b>Compiler</b></h3>
    <hr>
	<p><?=$compiler?></p>
    <br>

</body>
</html>

