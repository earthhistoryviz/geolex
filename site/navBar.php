<?php
  session_start();
  // Gives us $maps and $mapperiods
  include(dirname(__FILE__) . "/getmaps.php");
?>
<!DOCTYPE html>
<html>
  <head>
<?php
if ($_SESSION["loggedIn"]) {
  include("./adminDash.php");
} else {
?>
    <style>
      .topnav {
        overflow: hidden;
        background-color: #e9e9ee;
      }
      .topnav a {
        float: left;
        display: block;
        color: black;
        text-align: center;
        text-decoration: none;
        padding: 14px 16px;
        font-size: 17px;
      }

      .topnav a:hover {
        background-color: #ddd;
        color: blue;
      }
      h1 {
        margin-bottom: 0px;
      }
      h2 {
        margin-bottom: 0px;
      }
      h3 {
        margin-bottom: 0px;
      }
      h4 {
        margin-bottom: 0px;
      }
      p {
        margin-top: 0px;
      }
    </style>
  </head>
  <body>

  <div class="topnav">
    <?php /* if currently in /site folder, use index.php directly;
             if currently in a lower level folder, use ../index.php */
    if (strcmp(getcwd(), "/app") == 0) { ?>
      <a href="index.php">Home</a>
      <a href="general.php">Multi-Country Search</a> <?php
    } else { ?>
      <a href="../index.php">Home</a>
      <a href="../displayInfo.php">Search Formation</a>
      <a href="../general.php">General Search</a> <?php
    } ?>
    <a style="float: right;" href="login.php">Admin Login</a>
  </div>


  <div style="display: flex; flex-direction: row;">
    <div style="width: 120px; padding: 5px; display: flex; flex-direction: column;">
      <?php
       if (isset($_GET["period"])) {
         $period = $_GET["period"];
       } else {
         $period = "Devonian";
       }

      /* 
      $periods = array(
        array( "name" => "Quaternary", "color" => "#F9F97F"),
        array( "name" => "Neogene", "color" => "#FFE619"),
        array( "name" => "Paleogene", "color" => "#FD9A52"),
        array( "name" => "Cretaceous", "color" => "#7FC64E"),
        array( "name" => "Jurassic", "color" => "#34B2C9"),
        array( "name" => "Triassic", "color" => "#812B92"),
        array( "name" => "Permian", "color" => "#F04028"),
        array( "name" => "Carboniferous", "color" => "#67A599"),
        array( "name" => "Devonian", "color" => "#CB8C37"),
        array( "name" => "Silurian", "color" => "#B3E1B6"),
        array( "name" => "Ordovician", "color" => "#009270"),
        array( "name" => "Cambrian", "color" => "#7FA056"),
        array( "name" => "Ediacaran", "color" => "#FED96A"),
      );
      */

      foreach($mapperiods as $p) {?>
        <div style="background-color: #<?php echo $p["color"]?>; padding: 5px; ">
          <a href="/index.php?period=<?php echo $p["period"]?>"><?php echo $p["period"]?></a>
        </div>
      <?php } ?>
    </div>

    <div class="mainBody" style=" width: 100%">

<?php
}
?>
