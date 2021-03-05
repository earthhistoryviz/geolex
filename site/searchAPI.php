<?php
include_once("SqlConnection.php");

$searchquery = addslashes($_REQUEST['searchquery']);
$periodfilter = addslashes($_REQUEST['periodfilter']);
$provincefilter = addslashes($_REQUEST['provincefilter']);
$agefilterstart = addslashes($_REQUEST['agefilterstart']);
$agefilterend = addslashes($_REQUEST['agefilterend']);

if (!$searchquery) $searchquery = "";
if (!$periodfilter || $periodfilter == "All") $periodfilter = "";
if (!$provincefilter || $provincefilter == "All") $provincefilter = "";
if (!isset($_REQUEST['agefilterend']) || $agefilterend == "") {
  $agefilterend = $agefilterstart;
}

header("Content-Type: application/json");

function removeHTML($str) {
  $str = trim(preg_replace("/<\/?[^>]+>/","", $str));
  return $str;
}

function sortByProvince($a, $b){
	$a1 = $a['province'];
	$b1 = $b['province'];

	if($a1 == $b1) return 0;
	return ($a1 < $b1) ? -1: 1;
}


$sql = "SELECT * "
      ."  FROM formation "
      ." WHERE name LIKE '%$searchquery%' "
      ."       AND period LIKE '%$periodfilter%' "
      ."       AND province LIKE '%$provincefilter%'";

if ($agefilterstart && $agefilterstart != "") {
  $sql .= "       AND NOT (beg_date < $agefilterend"
         ."                OR end_date > $agefilterstart)";
}


$result = mysqli_query($conn, $sql);
//echo '<pre>'."HERES THE SQL QUERY".'</pre>';
//echo '<pre>'.$sql.'</pre>';
$count = mysqli_num_rows($result);

$arr = array();
while ($row = mysqli_fetch_array($result)) {
  $name = $row["name"];
  $province = removeHTML($row['province']);
  $period = removeHTML($row['period']);
  if (strlen($name) < 1) continue;
  $arr[$name] = array( "name" => $name, "province" => $province, "period" => $period);
}

usort($arr, 'sortByProvince');
$count = 0;
while($count < count($arr)){
	$currentElement = $arr[$count];
	$name = $currentElement["name"];
	$arr[$name] = $arr[$count];
	unset($arr[$count]);
	$count = $count + 1;
}



echo json_encode($arr);
?>
