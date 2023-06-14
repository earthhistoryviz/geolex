<?php
include_once("SqlConnection.php");

$searchquery = addslashes($_REQUEST['searchquery']);
$periodfilter = addslashes($_REQUEST['periodfilter']);
$provincefilter = addslashes($_REQUEST['provincefilter']);
$agefilterstart = addslashes($_REQUEST['agefilterstart']);
$agefilterend = addslashes($_REQUEST['agefilterend']);
$lithofilter = addslashes($_REQUEST['lithoSearch']);

if (!$searchquery) {
  $searchquery = "";
}
if (!$periodfilter || $periodfilter == "All") {
  $periodfilter = "";
}
if (!$provincefilter || $provincefilter == "All") {
  $provincefilter = "";
}
if (!isset($_REQUEST['agefilterend']) || $agefilterend == "" || $agefilterstart < $agefilterend) {
  $agefilterend = $agefilterstart;
}

$apostrophes = array(
  "’",  // fancy apostrophe
  "'",  // regular apostrophe
  "’",
  "’",
);
$allapostrophes = join($apostrophes, '');
$regex = "/[$allapostrophes]/";

$sql = "SELECT * "
  ."FROM formation "
  ."WHERE (name LIKE '%$searchquery%' ";
if (preg_match($regex, $searchquery)) {
  foreach ($apostrophes as $apos) {
    $sql .= "OR name LIKE '%".preg_replace($regex, $apos, $searchquery)."%' ";
  }
}
$sql .= ") ";
$sql .= "AND period LIKE '%$periodfilter%' "
  ."AND province LIKE '%$provincefilter%' ";

if (strcmp($lithofilter, "") === 0) {
  $sql .= "AND lithology LIKE '%$lithofilter%' ";
} else {
  $lithofilter_lower = strtolower($lithofilter);

  if (strpos($lithofilter_lower, ' and ') !== false) { // if the user wants to search with 'and'
    $lithofilter_array = explode(' and ', $lithofilter_lower);
    foreach ($lithofilter_array as $value) {
      $sql .= "AND lithology LIKE '%$value%' ";
    }
  } else if (strpos($lithofilter_lower, ' or ') !== false) { // if the user wants to search with 'or'
    $lithofilter_array = explode(' or ', $lithofilter_lower);
    $index = 0;
    foreach ($lithofilter_array as $value) {
      if ($index === 0) {
        $sql .= "AND lithology LIKE '%$value%' ";
      } else {
        $sql .= "OR lithology LIKE '%$value%' ";
      }
      $index++;
    }
  } else { // user does not want to search and/or
    $sql .= "AND lithology LIKE '%$lithofilter%' ";
  }
}

preg_replace("+", "%", $searchquery);

// In case of Date/Date Range search
if ($agefilterstart != "") {
  $sql .= "AND NOT (beg_date < $agefilterend " // the cast make sure a float is compared with a float
	  ."OR end_date > $agefilterstart) "
	  ."AND beg_date != '' " // with 0 Ma formations without a beginning date and end date get returned (this avoids that)
    ."AND end_date != '' "; // same comment as line above
}

$result = mysqli_query($conn, $sql);
/* ---------- Debugging ---------- */
// echo '<pre>'."HERE'S THE SQL QUERY".'</pre>';
// echo '<pre>'.$sql.'</pre>';
/* ---------- Debugging ---------- */

$isSynonym = false;
if (mysqli_num_rows($result) == 0) {
  $isSynonym = true; // all things found here will be isSynonym = true
  // if formation name is not found, search Synonymns
  $sql = "SELECT * "
    ."FROM formation "
    ."WHERE type_locality LIKE '%$searchquery%' "
    ."AND period LIKE '%$periodfilter%' "
    ."AND province LIKE '%$provincefilter%' ";
  if ($agefilterstart != "") {
    $sql .= "AND NOT (beg_date < $agefilterend "
      ."OR end_date > $agefilterstart) "
      ."AND beg_date != '' "
      ."AND end_date != '' ";
  }
  $result = mysqli_query($conn, $sql);
  /* ---------- Debugging ---------- */
  // echo '<pre>'."HERE'S THE SQL QUERY".'</pre>';
  // echo '<pre>'.$sql.'</pre>';
  /* ---------- Debugging ---------- */
}

function sortByAge($a, $b) {
  $a1 = $a['beginning age'];
  $a1 = str_replace(",", "", $a1);
  $b1 = $b['beginning age'];
  $b1 = str_replace(",", "", $b1);

  if ($a1 == $b1) {
    return 0;
  }
  return ($a1 < $b1) ? -1: 1;
}

function removeHTML($str) {
  $str = trim(preg_replace("/<\/?[^>]+>/","", $str));
  return $str;
}

function sortByProvince($a, $b) {
	$a1 = $a['Beginning age'];
	$b1 = $b['Beginning age'];
  if ($a1 == "" && $b1 != "") {
	  return 1;
	}
	if ($a1 != "" && $b1 == "") {
    return -1;
	}
	if ($a1 == $b1) {
    return 0;
  }
	return ($a1 < $b1) ? -1 : 1;
}

?>
