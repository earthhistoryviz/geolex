<html>

<head>
<title>DevonianTest</title>
</head>
	<?php include("navBar.php");?>
<body>

<form>
    Select Period :
<select name="period" id="period" onchange="this.form.submit()">
<option value="0" selected="selected">Select Period</option>
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
<!-- 

<br><b><a href="login.php">Admin Login</a></b></br> -->
</p>Select Region: </p>
<?php
   if(isset($_GET["period"])){
       $period=$_GET["period"];
       echo "select period is ".$period;
       include 'Mapinfo/' . $period.'_China_Map.php';
   }
?>




</body>

</html>
