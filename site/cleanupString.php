<?php
// Use this to clean period/province names that were poorly parsed from word doc
function cleanupString($str) {
  return  trim(preg_replace("/<[^>]+>/", "", $str)); 
}

function isAssoc(array $arr)
{
    if (array() === $arr) return false;
    return array_keys($arr) !== range(0, count($arr) - 1);
}

function keepOnlyTheseKeys($orig, $keep_recurse, $keep_verbatim) {
  if (!is_array($orig)) return $orig;

  $ret = array();
  foreach($orig as $origkey => $origval) {
    // If this is an integer key, recurse like normal
    if (is_numeric($origkey)) {
      $ret[$origkey] = keepOnlyTheseKeys($origval, $keep_recurse, $keep_verbatim);

    } else {
      foreach($keep_recurse as $keepkey) {
        if ($origkey == $keepkey) {
          $ret[$keepkey] = keepOnlyTheseKeys($origval, $keep_recurse, $keep_verbatim);
          break;
        }
      }
      foreach($keep_verbatim as $keepkey) {
        if ($origkey == $keepkey) {
          $ret[$keepkey] = $origval;
          break;
        }
      }
    }

  }

  return $ret;
}

function cleanupGeojson($str){
  //echo "Cleaning up GeoJSON str: <pre>"; htmlspecialchars(print_r($str)); echo "</pre>";
	$str = preg_replace("/(&QUOT;|&quot;)/", '"', $str);
	$str = strip_tags($str);
	$str = preg_replace('/"COORDINATES"/',' "coordinates"', $str);

  // Remove non-standard keys and properties
  $json = json_decode($str, true);

  $cleaned = keepOnlyTheseKeys($json, array("type", "geometry", "features"), array("coordinates", "crs"));
  $str = json_encode($cleaned);
  if ($_SERVER["HTTP_HOST"] == "dev.timescalecreator.com:5100") {
    echo "<hr/>Inserting cleaned GeoJSON: $str<hr/>";
  }
  return $str;
}

?>
