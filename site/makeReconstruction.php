<?php 

function createGeoJSONForFormations($formations) {
  $geojson = '{
    "type": "FeatureCollection",
    "name": "Triassic strata_10Feb2021",
    "crs": { "type": "name", "properties": { "name": "urn:ogc:def:crs:OGC:1.3:CRS84" } },
    "features": [';

   $first = true;
   foreach($formations as $f) {
     if (!$first) {
       $geojson .= ",\n";
       $first = false;
     }
     $parse = json_decode($f["geojson"]);
     $parse->properties->pattern = $f["lithoPattern"]; 
     $geojson .= json_encode($parse);
    }

  $geojson .= "]}";
  return $geojson;
}
?>
