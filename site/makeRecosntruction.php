<?php 

function prepare() {
  global $retgeoJSON, $results, $header, $recongeojson, $firstRun, $url, $raw, $response,
         $recongeojsonmid, $firstform, $midAge, $geoJSONmid, $outdirname, $outdirname_php, $outdirhash,
         $initial_creation_outdir, $reconfilename, $timedout;
  $retgeoJSON = array();
  $results = array();
  $header = '{
    "type": "FeatureCollection",
    "name": "Triassic strata_10Feb2021",
    "crs": { "type": "name", "properties": { "name": "urn:ogc:def:crs:OGC:1.3:CRS84" } },
    "features": [';
  $recongeojson = $header;
  $firstRun = 1;

foreach($regionstosearch as $r) {
    $url = $r["searchurl"] . "?searchquery=".$_REQUEST["search"]."&periodfilter=".$_REQUEST["filterperiod"]."&agefilterstart=".$_REQUEST["agefilterstart"]."&agefilterend=".$_REQUEST["agefilterend"];


if ($_REQUEST["generateImage"]) {
      $url .= "&generateImage=1";
    }
    $raw = file_get_contents($url);
    $response = json_decode($raw);

    $results[$r["name"]] = array(
      "linkurl" => $r["linkurl"],
      "formations" => $response,
      "groupbyprovince" => array(),
    );
    foreach($response as $fname => $finfo) {
      // Compiling all geoJSON strings from the returned formations into recon.geojson
      $p = $finfo->province;
      $geo = $finfo->geojson;
      $name = $finfo->name;
      $begAge = $finfo->begAge;
      $endAge = $finfo->endAge;
      if ($geo && $geo != "null") {
        //geoJSONmid below filters out all formations that end by the middle of a date range/period
        $geoJSONmid[$name] = array("name" => $name, "begAge" => $begAge, "endAge" => $endAge, "geojson" => $geo);
        if(!$firstRun) {
          $recongeojson .= ",\n";

 }
        $recongeojson .= $geo;
        $firstRun = 0;

 }

      if(!$p || strlen(trim($p)) < 1) $p = "Unknown Province";
      //$results[$r["name"]]["groupbyprovince"][$p]["formations"][$fname] = $finfo;
      $newp = explode(", ", $p);
      $overlapCount = 0; // counts number of overlaps
      foreach($newp as $sepp) {
        // $results[$r["name"]]["groupbyprovince"][$sepp]["formations"][$fname] = $finfo;
        $results[$r["name"]]["groupbyprovince"][$sepp]["formations"][$fname] = $finfo;
        /* Figure out which periods overlap this formation */
        foreach($periodsDate as $searchperiod) {
          if(stripos($finfo->period, $searchperiod["period"]) === false) continue;
          $results[$r["name"]]["groupbyprovince"][$sepp]["groupbyperiod"][$searchperiod["period"]][$fname] = $finfo;

 }

 }

 }
    ksort($results[$r["name"]]["groupbyprovince"]);
  }

  $recongeojson .= "]}";
  // recongeojsonmid is what needs to be put into the geojson file when the middle reconstruction button gets pressed
  $recongeojsonmid = $header;
  $firstform = 1; // don't want a comma before the very first geojson that gets appended

  $midAge = ($_REQUEST["agefilterstart"] +  $_REQUEST["agefilterend"]) / 2;
  if(isset($_REQUEST["agefilterstart"]) && isset($_REQUEST["agefilterend"])) {
    foreach($geoJSONmid as $midForm){
      if ($midAge <= $midForm["begAge"] ||  $midAge >= $midForm["endAge"]) {
        $recongeojsonmid .= $midForm["geojson"];
        $recongeojsonmid .= ",";

 }
      $firstform = 0;

 }

 }
  $recongeojsonmid = substr($recongeojsonmid, 0, -1);

  $recongeojsonmid .= "]";
  $recongeojsonmid .= '}';
  // Only create the output directory if we are generating an image:

 if ($_REQUEST["generateImage"]) {

 if($_REQUEST["recondate_description"] == "on date" || $_REQUEST["recondate_description"] == "base") {
      switch($_REQUEST["selectModel"]){
        case "Default":  $toBeHashed = $recongeojson.$_REQUEST["agefilterstart"];
        default: $toBeHashed = $recongeojson.$_REQUEST["agefilterstart"].$_REQUEST["selectModel"];

  }
    } else if($_REQUEST["recondate_description"] == "middle") {
      switch($_REQUEST["selectModel"]){
        case "Default":  $toBeHashed = $recongeojsonmid.$_REQUEST["agefilterstart"];
        default: $toBeHashed = $recongeojsonmid.$_REQUEST["agefilterstart"].$_REQUEST["selectModel"];

  }
    } else if($_REQUEST["recondate_description"] == "middle") {
      switch($_REQUEST["selectModel"]){
        case "Default":  $toBeHashed = $recongeojsonmid.$_REQUEST["agefilterstart"];
        default: $toBeHashed = $recongeojsonmid.$_REQUEST["agefilterstart"].$_REQUEST["selectModel"];

  }
    }
    $outdirhash = md5($toBeHashed);
    // outdirname is what pygplates should see
    switch($_REQUEST["selectModel"]) {
      case  "Default": $outdirname = "livedata/default/$outdirhash"; break;
      case "Marcilly": $outdirname = "livedata/marcilly/$outdirhash"; break;
      case  "Scotese": $outdirname = "livedata/scotese/$outdirhash"; break;
      default:         $outdirname = "livedata/unknown/$outdirhash";

  }
    // and php is running one level up:
    $outdirname_php = "pygplates/$outdirname";
    $initial_creation_outdir = false; // did we have to make the output hash directory name?

    if (!file_exists($outdirname_php)) {
      $initial_creation_outdir = true;
      mkdir($outdirname_php, 0777, true);

  }
    $reconfilename = "$outdirname_php/recon.geojson";

    if (!file_exists($reconfilename)) {
      file_put_contents($reconfilename, $recongeojson);
    }
  }
}
?>
