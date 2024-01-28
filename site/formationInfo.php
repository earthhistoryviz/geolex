<?php 
include_once("SqlConnection.php");
// Get all the formation names to build the regexp searches in the text for automatic link creation
$sql = "SELECT name FROM formation";
$result = mysqli_query($conn, $sql);
$nameregexes = array();
function insertSorted(&$array, $element, $compareFunc) {
  $low = 0;
  $high = count($array);

  while ($low < $high) {
    $mid = floor(($low + $high) / 2);
      if ($compareFunc($element, $array[$mid]) < 0) {
        $high = $mid;
      } else {
        $low = $mid + 1;
      }
  }

  array_splice($array, $low, 0, [$element]);
}

// Comparison function for sorting by string length (descending)
function compareByNameLength($a, $b) {
  return strlen($b['name']) - strlen($a['name']);
}

while ($row = mysqli_fetch_array($result)) {
  $rname = $row["name"];
  $rnameRegex = preg_replace('/\s+/', '\s*', $rname);
  // turn name into regular expression allowing arbitrary number of spaces between words
  $newElement = array(
    "name" => $rname,
    "regex" => "/\b($rnameRegex)\b/i"
    //"superceeded_by" => array(), // nameregex that should supercede this name if it also matches (Bao Loc Fm -> Deo Bao Loc Fm: if Deo matches, then use it instead)
  );
  insertSorted($nameregexes, $newElement, 'compareByNameLength');
}
$fmdata = array(
  'name'                                  => array("needlinks" => false),
  'period'                                => array("needlinks" => false),
  'age_interval'                          => array("needlinks" => false), 
  'province'                              => array("needlinks" => false),
  'type_locality'                         => array("needlinks" => true),
  'lithology'                             => array("needlinks" => true),
  'lithology_pattern'                     => array("needlinks" => true),
  'lower_contact'                         => array("needlinks" => true),
  'upper_contact'                         => array("needlinks" => true),
  'regional_extent'                       => array("needlinks" => true),
  'geojson'                               => array("needlinks" => true),
  'fossils'                               => array("needlinks" => true),
  'age'                                   => array("needlinks" => false),
  'age_span'                              => array("needlinks" => false),
  'beginning_stage'                       => array("needlinks" => false),
  'frac_upB'                              => array("needlinks" => false),
  'beg_date'                              => array("needlinks" => false),
  'end_stage'                             => array("needlinks" => false),
  'frac_upE'                              => array("needlinks" => false),
  'end_date'                              => array("needlinks" => false),
  'depositional'                          => array("needlinks" => true),
  'depositional_pattern'                  => array("needlinks" => true),
  'additional_info'                       => array("needlinks" => true),
  'compiler'                              => array("needlinks" => false),
);
if (!$macrostrat) {
  $sql = "SELECT * FROM formation WHERE name LIKE '%$formation%'"; //old query that won't work with Kali vs. Warkali formations or characters needing to be escaped

  $searchname = mysqli_real_escape_string($conn, $formation);
  $sql = "SELECT * FROM formation WHERE name= '$searchname'";
  if (preg_match("/’/", $searchname)) {
    $sql .= " OR name = \"".preg_replace("/’/", "'", $searchname)."\"";
  }

  $result = mysqli_query($conn, $sql);
  
} else {
  
}

$found = false;
while ($row = mysqli_fetch_array($result)) {
  $found = true;
  // Fill in each of the variables that we're going to send to the browser
  foreach($fmdata as $varname => $varvalue) {
    $rowval = $row[$varname];
    $fmdata[$varname]["raw"] = trim($rowval);
    $fmdata[$varname]["display"] = trim($rowval);
    if ($varvalue["needlinks"]) {
      $fmdata[$varname]["display"] = findAndMakeFormationLinks($rowval, $nameregexes);
    }
  }
}

// This function finds formations in a string and replaces them with hyperlinked versions.
// It ensures that each formation is linked only once and prioritizes longer formations over shorter overlapping ones.
function findAndMakeFormationLinks($str, $nameregexes) {
  // This could potentially be optimized but the page loads fast enough
  // This code collects all regex matches and figures out the end positions of the matches. It then sorts them by the end and only considers the first instance since any
  // other instance represents a shorter string match (since the array $nameregex is sorted from longest to smallest).
  // An example is Deo Bao Loc Fm and Bao Loc Fm. The regex for Bao Loc Fm will match both formations, but the end position is the same. Since $nameregexes is sorted
  // from longest to smallest, we will match Deo Bao Loc Fm first. When we later filter Bao Loc Fm will be dropped since the last position is the same and it comes later
  // Then we use the filtered matches to replace links starting from the end of the string
  $allMatches = [];

  // Collect all matches with their start and end positions
  foreach ($nameregexes as $n) {
    if (preg_match_all($n["regex"], $str, $matches, PREG_OFFSET_CAPTURE)) {
      foreach ($matches[0] as $match) {
        $allMatches[] = [
          'start' => $match[1],
          'end' => $match[1] + strlen($match[0]),
          'replacement' => "<a href=\"/formations/".$n["name"]."\">".$n["name"]."</a>"
        ];
      }
    }
  }

  // Sort matches by end position in descending order
  usort($allMatches, function($a, $b) {
    return $b['end'] - $a['end'];
  });

  // Keep only the first instance of each end position
  $uniqueEnds = [];
  $filteredMatches = [];
  foreach ($allMatches as $match) {
    $endPos = $match['end'];
    if (!isset($uniqueEnds[$endPos])) {
      $uniqueEnds[$endPos] = true;
      $filteredMatches[] = $match;
    }
  }

  // Replace matches in the string
  foreach ($filteredMatches as $match) {
    $str = substr_replace($str, $match['replacement'], $match['start'], $match['end'] - $match['start']);
  }

  return $str;
}

