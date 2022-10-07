<?php 
include_once("customize.php");
$include_prefix = allowCustomOverride(__FILE__);

  // Default welcome page:

  /* navBar will set $period, $maps, and $mapperiods for us
   * and create mapForPeriod function for us (in getmaps.php)
   */
  include("navBar.php");
  ?><h2 align="center" style="color:blue;">
    Welcome to the International Geology Website and Database! <br>
    Please enter a formation name or group to retrieve more information.
  </h2><?php
  include("SearchBar.php");

  if($period) {?>
    <p>Map is clickable </p>
    <p>Click on any provinces to view detailed information</p><?php
    include mapForPeriod($period);
  }

?>
