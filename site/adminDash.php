
<?php
  session_start();
  if (!$_SESSION["loggedIn"]) {
    echo "ERROR: You must be logged in to access this page.";
    exit(0);
  }
  include_once("constants.php");
?>
<link rel="stylesheet" type="text/css" href="style.css"/>

<body>

<div class="container" style="height: 100%;">
    <!-- aside left -->
    <div class="left-menu">
        <div class="item <?php if ($_SERVER["PHP_SELF"] == "/adminIndex.php" && empty($_SERVER["QUERY_STRING"])) { echo "active"; } ?>" >
          <a class="menu-link" href="/adminIndex.php">Homepage</a>
        </div>
        <div class="item <?php
        if (preg_match("/adminDisplayInfo.php/", $_SERVER["PHP_SELF"]) ||
          ($_SERVER["PHP_SELF"] == "/adminIndex.php" && !empty($_SERVER["QUERY_STRING"]) && !preg_match("/\bperiod\b/", $_SERVER["QUERY_STRING"])) ||
          preg_match("/deleteForm.php/", $_SERVER["PHP_SELF"])) {
          echo "active";
        }
        ?>" >
          <a class="menu-link" href="/adminDisplayInfo.php">Manage Database</a>
        </div>
        <div class="item <?php if (preg_match("/fileBrowser.php/", $_SERVER["PHP_SELF"]) || preg_match("/Upload.php/", $_SERVER["PHP_SELF"])) { echo "active"; } ?>" >
          <a class="menu-link" href="/fileBrowser.php">Parse Word Document</a>
        </div>
        <div class="item <?php if (preg_match("/mapPackBrowser.php/", $_SERVER["PHP_SELF"])) { echo "active"; } ?>" >
          <a class="menu-link" href="/mapPackBrowser.php">Parse TSC MapPack Docs </a>
        </div>
        <div class="item <?php if (preg_match("/lithologyPatternCheckBrowser.php/", $_SERVER["PHP_SELF"])){ echo "active"; } ?>" >
          <a class="menu-link" href="/lithologyPatternCheckBrowser.php">Check Lithology Pattern </a>
        </div>
        <div class="item <?php if (preg_match("/uploadTimescale.php/", $_SERVER["PHP_SELF"])) { echo "active"; } ?>" >
          <a class="menu-link" href="/uploadTimescale.php">Timescale</a>
        </div>
	      <div class="item <?php if (preg_match("/manageUser.php/", $_SERVER["PHP_SELF"]) || preg_match("/Signup.php/", $_SERVER["PHP_SELF"])) { echo "active"; } ?>" >
          <a class="menu-link" href="/manageUser.php">Manage User information</a>
	      </div>
  	    <div>
  	      <p class="fr user">User: <?=$_SESSION["username"]?></p>
  	    </div>

    </div>
    <!-- aside right -->
    <div class="mainBody aside-right" id="conts">
        <div class="top">
            <a href="/logout.php" class="logout">Logout</a>

