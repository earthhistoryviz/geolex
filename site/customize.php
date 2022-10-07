<?php

// Pass your __FILE__ constant to this function.
// This will check customizations/ for that path and use it instead of
// the default if it exists.
function allowCustomOverride($pagepath_file) {
  // If this is the custom version, just return path prefix of ".."
  if (preg_match("/\/app\/customization/", $pagepath_file)) {
    return "..";
  }
  // If not, check if there is a custom version of this file:
  $custom_path = preg_replace("/^\/app/", "/app/customization", $pagepath_file);
  if (file_exists($custom_path)) {
    include($custom_path);
    exit();
  }
  // Otherwise, no override, return as normal
  return ".";
}
?>
