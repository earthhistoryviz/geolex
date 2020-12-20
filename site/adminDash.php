<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>index</title>

    <link rel="stylesheet" type="text/css" href="style.css"/>

</head>
<body>

<div class="container">
    <!-- aside left -->
    <div class="left-menu">
        <div class="item <?php if (preg_match("/displayInfo.php/", $_SERVER["PHP_SELF"])) { echo "active"; } ?>" >
          <a class="menu-link" href="displayInfo.php">Manage Database</a>
        </div>
        <div class="item <?php if (preg_match("/fileBrowser.php/", $_SERVER["PHP_SELF"])) { echo "active"; } ?>" >
          <a class="menu-link" href="fileBrowser.php">Parse Word Document</a>
        </div>
        <div class="item <?php if (preg_match("/uploadTimescale.php/", $_SERVER["PHP_SELF"])) { echo "active"; } ?>" >
          <a class="menu-link" href="uploadTimescale.php">Timescale</a>
        </div>
	      <div class="item <?php if (preg_match("/manageUser.php/", $_SERVER["PHP_SELF"])) { echo "active"; } ?>" >
          <a class="menu-link" href="manageUser.php">Manage User information</a>
	</div>
		<div class ="itme <?php if (preg_match("/create_db.php/",$_SERVER["PHP_SELF"])){echo "active";}?>">
		<a class ="menu-link" href = "create_db.php">Clear Database </a>
	</div>	
  	    <div>
  	      <p class="fr user">User: <?=$_SESSION["username"]?></p>
  	    </div>

    </div>
    <!-- aside right -->
    <div class="mainBody aside-right" id="conts">
        <div class="top">
            <a href="logout.php" class="logout">Logout</a>

