<!DOCTYPE html>
<html>
<?php 	
include("SearchBar.php");
include("SqlConnection.php");
$formationName = $_REQUEST;
if($formationName[formation] == "") {
	header("Location: displayEmpty.php");
	exit(0)
}
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
        $age_interval = trim($row['age_interval']);

	/*
	if(preg_match("/^([A-Za-z])([0-9]{1-2})$/", $age_interval)) {
		$output = $age_interval[0];
		$output .= "<sub>$age_interval[1]<\sub>";
		if(preg_match("/^[0-9]$/", $age_interval[2])) { //<-------
			$output .= "<sup>$age_interval[2]<\sub>";
		}
		$age_interval = $output;
	}
	*/

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
/*
if($name == "") { 
	header("Location: displayNoMatch?foramtion=".$formationName[formation]);
	exit(0)
*/
}

// display information below
?>
    <div>
	<h1><b><?=$name?></b></h1>
    <hr></div>

<!--
	<h4><?=$period?></h4>
	<h4><?=$age_interval?></h4>
	<h4><?=$province?> <br></h4>
-->
	
    <div id="period">
	<h3><b>Period</b></h3>
    <hr>
	<p><?=$period?><br></p>
    </div>

    <div id="age_interval">
	<h3><b>Age Interval</b></h3>
    <hr>
	<p><?=$age_interval?><br></p>
    </div>

    <div id="province">
	<h3><b>Province</b></h3>
    <hr>
	<p><?=$province?><br></p>
    </div>

    <div id="type_locality">
	<h3><b>Type Locality and Naming</b></h3>
    <hr>
	<p><?=$type_locality?><br></p>
    </div>

    <div id="lithology">
	<h3><b>Lithology and Thickness</b></h3>
    <hr>
	<p><?=$lithology?><br></p>
    </div>

    <div id="relationships_distribution">
	<h3><b>Relationships and Distribution</b></h3>
    <hr>
	<div id="lower_contact">
		<h4><i>Lower contact</i></h4>
		<p><?=$lower_contact?></p>
	</div>
	<div id="upper_contact">
		<h4><i>Upper contact</i></h4>
		<p><?=$upper_contact?></p>
	</div>
	<div id="regional_extent">
		<h4><i>Regional extent</i></h4>
		<p><?=$regional_extent?><br></p>
	</div>
    </div>

    <div id="fossils">
	<h3><b>Fossils</b></h3>
    <hr>
	<p><?=$fossils?><br></p>
    </div>

    <div id="age">
	<h3><b>Age</b></h3>
    <hr>
	<p><?=$age?><br></p>
    </div>

    <div id="depositional">
	<h3><b>Depositional setting</b></h3>
    <hr>
	<p><?=$depositional?><br></p>
    </div>

    <div id="additional_info">
	<h3><b>Additional Information</b></h3>
    <hr>
	<p><?=$additional_info?><br></p>
    </div>

    <div id="compiler">
	<h3><b>Compiler</b></h3>
    <hr>
	<p><?=$compiler?><br></p>
    </div>
</body>
</html>

