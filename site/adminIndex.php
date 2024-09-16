<?php

session_start();
if ($_SESSION["loggedIn"]) {
    $auth = true;
}
include_once("index.php");
