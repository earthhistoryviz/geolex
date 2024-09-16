<?php

// Pass your __FILE__ constant to this function.
// This will check customizations/ for that path and use it instead of
// the default if it exists.
function allowCustomOverride($pagepath_file)
{
    // If this is the custom version, just return path prefix of ".."
    $filename = basename($pagepath_file);
    $path = dirname($pagepath_file);
    if (preg_match("/^OVERRIDE_/", $filename)) {
        return "";
    }
    $override_fullpath = "$path/OVERRIDE_$filename";
    if (file_exists($override_fullpath)) {
        return $override_fullpath;
    }
    // Otherwise, no override, return as normal
    return "";
}
