<?php

$validGeoJSONFound = false;
function createGeoJSONForFormations($formations)
{
    global $validGeoJSONFound;
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
        if ($f["geojson"] === null) {
            continue;
        }
        if (!$first) {
            $geojson .= ",\n";
        }
        $first = false;
        $parse = $f["geojson"];
        if (is_string($parse)) {
            $parse = json_decode($parse);
        }
        if (is_object($parse) && $parse !== null) {
            if (!isset($parse->properties)) {
                $parse->properties = new stdClass();
            }
            $validGeoJSONFound = true;
            $parse->properties->name = $f["name"];
            $parse->properties->pattern = $f["lithology_pattern"];
            //These need to be set null in order for the Scotese model to work
            $parse->properties->FROMAGE = null;
            $parse->properties->TOAGE = null;
            $geojson .= json_encode($parse);
        }
        //var_dump($parse);
    }
    $geojson .= "]}";
    return $geojson;
}
