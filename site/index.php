<?php
   if (isset($_GET["period"])) {
     $period=$_GET["period"];
   } else {
     $period="Devonian";
   }

   $periods = array(
      "Quaternary",
      "Neogene",
      "Paleogene",
      "Cretaceous",
      "Jurassic",
      "Triassic",
      "Permian",
      "Carboniferous",
      "Devonian",
      "Silurian",
      "Ordovician",
      "Cambrian",
      "Ediacaran",
   );
?>
<html>
  <head>
    <title>Time Scale Creator</title>
  </head>
  <?php include("navBar.php");?>
  <h2 align="center" style="color:blue;">Welcome to the International Geology Website and Database! <br>Please enter a formation name or group to retrieve more information.</h2>
  <?php include("SearchBar.php"); ?>

  <body>

    <form action="index.php" method="GET">
      Select Period :
      <select name="period" id="period" onchange="this.form.submit()">
        <?php foreach($periods as $p) {
          ?><option value="<?=$p?>" <?php if ($period == $p) echo "SELECTED"?>><?=$p?></option><?php
        }?>
      </select>
    </form>

    <?php
       if($period) {
         ?><p>Map is clickable </p>
           <p>Click on any provinces to view detailed information</p><?php
         include 'Mapinfo/' . $period.'_China_Map.php';
       }
    ?>


  </body>
</html>
