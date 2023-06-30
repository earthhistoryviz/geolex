<?php
  include_once("allowCustomOverride.php");
  if (allowCustomOverride(__FILE__)) {
    return; // had overrid3
  }

  // Default navBar:
  session_start();
  // Gives us $maps and $mapperiods
  global $maps, $mapperiods;
  include("getmaps.php");
?>

<!DOCTYPE html>
<html>
<head> <?php
  if ($_SESSION["loggedIn"]) {
    include("adminDash.php");
  } else { ?>
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
      <a href="general.php">Multi-Country Search</a> 
      <a href="macrostratparse.php">Macrostrat Search</a> <?php
    } else { ?>
      <a href="../index.php">Home</a>
      <a href="../general.php">Multi-Country Search</a> 
      <a href="../macrostratparse.php">Macrostrat Search</a> <?php
    } ?>
    <a style="float: right;" href="login.php">Admin Login</a>
  </div>


  <div style="display: flex; flex-direction: row;">
    <div style="width: 120px; padding: 5px; display: flex; flex-direction: column;"> <?php
      global $period;
      if (isset($_GET["period"])) {
        $period = $_GET["period"];
      } else {
        $period = "Devonian";
      }

      foreach ($mapperiods as $p) { ?>
        <div style="background-color: #<?php echo $p["color"] ?>; padding: 5px;">
          <a href="/index.php?period=<?php echo $p["period"] ?>" style="text-decoration: none; font-family: Arial;"><?php echo $p["period"] ?></a>
        </div> <?php
      } ?>
    </div>

    <div class="mainBody" style=" width: 100%"> <?php
  }
?>
