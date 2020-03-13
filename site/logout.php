<?php
session_start();
session_destroy();
unset($_SESSION['loggedIn']);
unset($_SESSION['username']);
header('location:index.php');
echo "You have been logged out";
?>