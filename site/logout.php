<?php

session_start();
unset($_SESSION['loggedIn']);
unset($_SESSION['username']);
session_regenerate_id(true);
session_destroy();
header('location:index.php');
