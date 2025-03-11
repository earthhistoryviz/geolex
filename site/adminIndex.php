<?php

session_start();
$auth = false;
if ($_SESSION["loggedIn"]) {
    $auth = true;
}
include_once("index.php");
