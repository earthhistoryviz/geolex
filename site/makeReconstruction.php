<?php 

function createGeoJSONForFormations($formations) {
  $geojson = '{
    "type": "FeatureCollection",
    "name": "Triassic strata_10Feb2021",
    "crs": { "type": "name", "properties": { "name": "urn:ogc:def:crs:OGC:1.3:CRS84" } },
    "features": [';

   $first = true;
   foreach($formations as $f) {
     // If $f is a stdClass object, convert it to an array instead:
     if (!is_array($f)) {
       $f = (array)$f;
     }
     if (!$first) {
       $geojson .= ",\n";
     }
     $first = false;

     $parse = $f["geojson"];
     if (is_string($parse)) {
       $parse = json_decode($parse);
     }
     $parse->properties->name = $f["name"]; 
     $parse->properties->pattern = $f["lithology_pattern"]; 
     $geojson .= json_encode($parse);
    }
  $geojson .= "]}";
  return $geojson;
}
?>
