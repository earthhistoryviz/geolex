<html>

<head>
<title>Time Scale Creator</title>
</head>
	<?php include("navBar.php");?>
	<h2 align="center" style="color:blue;">Welcome to the International Geology Website and Database! <br>Please enter a formation name or group to retrieve more information.</h2>
	<?php include("SearchBar.php"); ?>
<body>

<form>
    Select Period :
<select name="period" id="period" onchange="this.form.submit()">
<option style = "backgroud-color:green" value="0" selected="selected">Select Period</option>
<option value="Quaternary">Quaternary</option>
<option value="Neogene">Neogene</option>
<option value="Paleogene">Paleogene</option>
<option value="Cretaceous">Cretaceous</option>
<option value="Jurassic">Jurassic</option>
<option value="Triassic">Triassic</option>
<option value="Permian">Permian</option>
<option value="Carboniferous">Carboniferous</option>
<option value="Devonian">Devonian</option>
<option value="Silurian">Silurian</option>
<option value="Ordovician">Ordovician</option>
<option value="Cambrian">Cambrian</option>
<option value="Ediacaran">Ediacaran</option>
</select>
</form>
<?php
echo "select period is ".$period;
?>
<!-- 

<br><b><a href="login.php">Admin Login</a></b></br> -->
</p>Select Region: </p>
<?php
   if(isset($_GET["period"])){
       $period=$_GET["period"];
       include 'Mapinfo/' . $period.'_China_Map.php';
   }
?>




</body>

</html>
