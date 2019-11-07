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
    <div class="aside-left">
        <ul id="menus">
            <li>

                <a href="javascript:;" class="m-title active">Dashboard</a>
            </li>
	    <li class="item <?php if (preg_match("/manageUser.php/", $_SERVER["PHP_SELF"])) { echo "active"; } ?>" >
                <a href="manageUser.php">Manage User information</a>
            </li>
            <li class="item <?php if (preg_match("/displayInfo.php/", $_SERVER["PHP_SELF"])) { echo "active"; } ?>" >
                <a href="displayInfo.php">Manage Database</a>
            </li>

	    <li>
	      <p class="fr user">User: <?=$_SESSION["username"]?></p>
	    </li>

        </ul>
    </div>
    <!-- aside right -->
    <div class="mainBody aside-right" id="conts">
        <div class="top">
            <a href="##" class="logout">Logout</a>

