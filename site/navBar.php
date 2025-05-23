<?php
include_once("allowCustomOverride.php");
include_once("OVERRIDE_index.php");
$override_fullpath = allowCustomOverride(__FILE__);
if (!empty($override_fullpath)) {
    include($override_fullpath); // had override
    return;
}

// Gives us $maps and $mapperiods
global $maps, $mapperiods;
include("getmaps.php");
?>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/style.css"/>
 <?php
  if ($auth) {
      include("adminDash.php");
  } else { ?>
  
    <style>
      .topnav {
        overflow: hidden;
        width: 100%;
        max-height: 50px;
        min-height: 45px;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: flex-start;
        gap: 14px;
        background-color: #333333;
        font-family: 'Montserrat', sans-serif;
      }
      .topnav > * {
        color: black;
        text-align: center;
        text-decoration: none;
        font-size: 17px;
        padding: 1px;
        height: 100%;
        color: #ffffff;
      }

      h1, h2, h3, h4{
        margin-bottom: 0px;
      }

      .region-name {
        margin: 0px;
      }

      p {
        margin-top: 0px;
      }

      .country-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        height: 100%;
        padding-left: 10px;
        color: orange;
        padding-right: 10px;
      }

      .country-logo > img {
        height: auto;
        max-height: 45px;
        margin-top: 0px;
      }

      .topnav > a:hover {
        color: orange;
        font-weight: bold;
      }
    </style>
</head>

<body>
  <div class="topnav">
    <?php /* if currently in /site folder, use index.php directly;
             if currently in a lower level folder, use ../index.php */
    include_once("constants.php");
      if (strcmp(getcwd(), "/app") == 0) { ?>
      <div class="country-logo">
        <img src="/noun_Earth_2199992.svg" alt="Logo">
        <h3 class="region-name"><?= $regionName ?></h3>
      </div>
      <a href="/index.php">Home</a>
      <a href="https://geolex.org/">Multi-Country Search</a> 
      <a href="/aboutPage.php">About</a> <?php
      } else { ?>
      <div class="country-logo">
        <img src="/noun_Earth_2199992.svg" alt="Logo">
        <h3><?= $regionName ?></h3>
      </div>
      <a href="../index.php">Home</a>
      <a href="https://geolex.org/">Multi-Country Search</a>
      <a href="../aboutPage.php">About</a><?php
      } ?>
    <a style="margin-left: auto; padding-right: 10px;" href="/login.php">Admin Login</a>
  </div>

  <?php
  if ($_SERVER['PHP_SELF'] != '/aboutPage.php' && $_SERVER['PHP_SELF'] != '/bibliography.php' && $_SERVER['PHP_SELF'] != '/generateAllImages.php') { ?>
    <div style="display: flex; flex-direction: row; flex: 1;">
      <div style="padding: 5px; display: flex; flex-direction: column; width: 120px;">
        <?php
          global $period;
      if (isset($_GET["period"])) {
          $period = $_GET["period"];
      } elseif ($mapperiods[0] > 0) {
          $reindexed = array_values($mapperiods);
          $period = $reindexed[1]["period"];
      }

      foreach ($mapperiods as $p) {
          if (is_array($p)) {?>
          <div style="background-color: #<?php echo $p["color"] ?>; padding: 5px;">
            <a href="/index.php?period=<?php echo $p["period"] ?>" style="text-decoration: none; font-family: Arial;"><?php echo $p["period"] ?></a>
          </div> <?php
          }
      } ?>
        <p><?=$providerMessage?></p> 
        <!-- provider message might not exist, this is set in a file you need to make called OVERRIDE_index.php -->
      </div>

      <div class="mainBody" style=" width: 100%;"> <?php
      //someone thought that having divs across multiple files was a good idea. they didn't even leave a comment on where it gets closed (footer.php)
  }
  }
?>
