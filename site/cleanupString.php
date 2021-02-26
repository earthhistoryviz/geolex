<?php
// Use this to clean period/province names that were poorly parsed from word doc
function cleanupString($str) {
  return  trim(preg_replace("/<[^>]+>/", "", $str)); 
}

function cleanupGeojson($str){
	$str = preg_replace("/(&QUOT;|&quot;)/", '"', $str);
	$str = strip_tags($str);
	$str = preg_replace('/"COORDINATES"/',' "coordinates"', $str);
	return $str;
}

?>
