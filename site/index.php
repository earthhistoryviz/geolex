<?php 
include_once("allowCustomOverride.php");
if (allowCustomOverride(__FILE__)) return; // "true" means we had an override, do not continue execution of this script (i.e. top-level return)

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

  global $period;
  if($period) {?>
    <p>Map is clickable </p>
    <p>Click on any provinces to view detailed information</p><?php
    include mapForPeriod($period);
  }

?>
