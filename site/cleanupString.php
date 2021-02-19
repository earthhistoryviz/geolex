<?php
// Use this to clean period/province names that were poorly parsed from word doc
function cleanupString($str) {
  return strtoupper(trim(preg_replace("/<[^>]+>/", "", $str)));
}

function cleanupGeojson($str){
	/*
	$str = preg_replace("/(&quot|&QUOT/)", '"', $str);
	$str = preg_replace("/(/</p/>|/<p/>)/", "", $str);
	$str = preg_replace("/(/<strong/>|/</strong/>)/", "", $str);
	 */
        /*
	$str = strip_tags($str, '<strong></strong><p></p>');
	$str = strip_tags($str, '&quot');
	$str = strip_tags($str, '&QUOT');
	 */
	$str = preg_replace("/(&QUOT;|&quot;)/", '"', $str);
	$str = strip_tags($str);
	//$str = preg_replace("/NULL/", '"NULL"', $str);
	$str = preg_replace('/"COORDINATES"/',' "coordinates"', $str);
	return $str;
}

?>
