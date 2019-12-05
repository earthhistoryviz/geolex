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
      h1 {
        margin-bottom: 0px;
      }
      h2 {
        margin-bottom: 0px;
      }
      h3 {
        margin-bottom: 0px;
      }
      h4 {
        margin-bottom: 0px;
      }
      p {
        margin-top: 0px;
      }
    </style>
  </head>
  <body>

  <div class="topnav">
    <a href="index.php">Home</a>
    <a href="searchFm.php">Search Formation</a>
    <a style="float: right;" href="login.php">Admin Login</a>
  </div>

    <div class="mainBody">

<?php 
}
?>
