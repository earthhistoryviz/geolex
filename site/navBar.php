<?php
  include_once("allowCustomOverride.php");
  $override_fullpath = allowCustomOverride(__FILE__);
  if (!empty($override_fullpath)) {
    include($override_fullpath); // had override
    return;
  }

  // Default navBar:
  session_start();
  // Gives us $maps and $mapperiods
  global $maps, $mapperiods;
  include("getmaps.php");
?>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/style.css"/>
 <?php
  if ($_SESSION["loggedIn"]) {
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

      h1 {
        margin-bottom: 0px;
      }
      h2 {
        margin-bottom: 0px;
      }
      h3 {
        margin: 0px;
      }
      h4 {
        margin-bottom: 0px;
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
        <h3><?= $regionName ?></h3>
      </div>
      <a href="/index.php">Home</a>
      <a href="/general.php">Multi-Country Search</a> 
      <a href="/aboutPage.php">About</a> <?php
      // <a href="macrostratparse.php">Macrostrat Search</a>
    } else { ?>
      <div class="country-logo">
        <img src="/noun_Earth_2199992.svg" alt="Logo">
        <h3><?= $regionName ?></h3>
      </div>
      <a href="../index.php">Home</a>
      <a href="../general.php">Multi-Country Search</a>
      <a href="../aboutPage.php">About</a><?php
    } ?>
    <a style="margin-left: auto; padding-right: 10px;" href="/login.php">Admin Login</a>
  </div>

  <?php
  if ($_SERVER['REQUEST_URI'] != '/aboutPage.php') { ?>
    <div style="display: flex; flex-direction: row; flex: 1;">
      <div style="width: 120px; padding: 5px; display: flex; flex-direction: column;"> <?php
        global $period;
        if (isset($_GET["period"])) {
          $period = $_GET["period"];
        } else {
          $found = false;
          foreach ($mapperiods as $p) {
            if ($p['period'] == 'Cenozoic') {
              $found = true;
            }
          }
          if ($found) {
            $period = 'Cenozoic';
          } else if ($mapperiods[0] > 0) {
            $period = $mapperiods[1]["period"];
          }
        }

        foreach ($mapperiods as $p) { ?>
          <div style="background-color: #<?php echo $p["color"] ?>; padding: 5px;">
            <a href="/index.php?period=<?php echo $p["period"] ?>" style="text-decoration: none; font-family: Arial;"><?php echo $p["period"] ?></a>
          </div> <?php
        } ?>
      </div>

      <div class="mainBody" style=" width: 100%;"> <?php
      //someone thought that having divs across multiple files was a good idea. they didn't even leave a comment on where it gets closed (footer.php)
  }
}
?>
