<?php
include_once("cleanupString.php");
include("SimpleXLSX.php");

// unformatted = true => don't include the commas in the result
function computeAgeFromPercentUp($stage, $percent, $timescale, $unformatted=false) {
  $percent = cleanupString($percent);
  if (strlen($percent) < 1) return false;
  if ($percent > 1) $percent /= 100.0;
  $stage = cleanupString($stage);
  if (strlen($stage) < 1) return false;
  // If we get here, we have actual percent and stage
  // Find the stage
  $stage_info = current(array_filter($timescale, function($v) use ($stage) { return cleanupString($v["stage"]) == $stage; }));
  if (!$stage_info) return false;
  $stop = $stage_info["top"];
  $sbase = $stage_info["base"];
  $span = $sbase - $stop;
  $comp = ($span * $percent)-$sbase;

  // If they want unformatted int, return it, otherwise format it
  if ($unformatted) {
    return abs($comp);
  }
  return number_format(abs($comp), 2); // need absolute value b/c millions of years ago should not actually be negative
}

$DEFAULT_TIMESCALE_PATH = dirname(__FILE__) . "/timescales/default_timescale.xlsx";
function parseDefaultTimescale() {
  global $DEFAULT_TIMESCALE_PATH;
  return parseTimescale($DEFAULT_TIMESCALE_PATH);
}

function parseTimescale($filepath) {
  $COLPERIOD=0;
  $COLSERIES=1;
  $COLSTAGE=2;
  $COLMA=3; // millions of years ago age
  $COLCOLOR=4;
  $xlsx = SimpleXLSX::parse($filepath);
  if (!$xlsx) throw new RuntimeException("XLSX Parse Error for path $filepath: ".SimpleXLSX::parseError());

  // Find the MasterChronostrat sheet:
  $sheetnames = $xlsx->sheetNames();
  $mastersheetindex = array_search('MasterChronostrat', $sheetnames);
  if ($mastersheetindex < 0) throw new RuntimeException("Could not find MasterChronostrat sheet");

  $rows = $xlsx->rows($mastersheetindex);

  // Headers are first row, data starts on the second row
  // Create an associative array for each row
  $stages = array();
  // Note: $i>=1 to skip header row
  $lastperiod="";
  $lastseries="";
  // Have to go in reverse because the period/series names show up first at the bottom
  for($i=(count($rows)-1); $i>=1; $i--) {
    $row = $rows[$i];
    // If this row has a period, use it until we see another one:
    if (strlen(trim($row[$COLPERIOD])) > 0) {
      $lastperiod = trim($row[$COLPERIOD]);
    }
    // If this row has a series, use it until we see another one:
    if (strlen(trim($row[$COLSERIES])) > 0) {
      $lastseries = trim($row[$COLSERIES]);
      // shorthand for "Early Cretaceous" is series = "Early"
      $up = strtoupper($lastseries);
      if ($up == "EARLY" || $up == "MIDDLE" || $up == "LATE") {
        $lastseries = "$lastseries $lastperiod";
      }
    }
    $series = strlen($lastseries) > 0 ? $lastseries : $row[$COLSTAGE];
    $period = strlen($lastperiod) > 0 ? $lastperiod : $lastseries;
    array_push($stages, array(
      "period" => $period,
      "series" => $series,
       "stage" => $row[$COLSTAGE],
        "base" => $row[$COLMA],
       "color" => $row[$COLCOLOR],
    ));
  }
  $stages = array_reverse($stages); // re-order them top down

  // Now, fill in the top ages for each stage
  for($i=1; $i<count($stages); $i++) {
    $stages[$i]["top"] = $stages[$i-1]["base"];
  }

  // Return the final array with the "TOP" removed from the beginning:
  return array_slice($stages, 1);
};
