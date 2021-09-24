<?php include("navBar.php");?>
<?php 
  /* navBar will set $period, $maps, and $mapperiods for us
   * and create mapForPeriod function for us (in getmaps.php)
   */
?>

  <h2 align="center" style="color:blue;">Welcome to the International Geology Website and Database! <br>Please enter a formation name or group to retrieve more information.</h2>
  <?php include("SearchBar.php"); ?>

<?php
//phpinfo();
       if($period) {?>
         <p>Map is clickable </p>
         <p>Click on any provinces to view detailed information</p><?php
         include mapForPeriod($period);
       }
    ?>
