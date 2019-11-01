<?php
  session_start();
?>
<!DOCTYPE html>
<html>
  <head>
<?php
if ($_SESSION["loggedIn"]) {
  include("./adminDash.php");
} else {

?>
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
    </style>
  </head>
  <body>

  <div class="topnav">
    <a href="index.php">Home</a>
    <a href="searchFm.php">Search Formation</a>
    <a href="login.php">Admin Login</a>
  </div>

    <div class="mainBody">

<?php 
}
?>
