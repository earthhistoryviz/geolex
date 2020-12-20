<?php
// Use this to clean period/province names that were poorly parsed from word doc
function cleanupString($str) {
  return strtoupper(trim(preg_replace("/<[^>]+>/", "", $str)));
}

?>
