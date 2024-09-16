<?php

include_once("SqlConnection.php");

$sql = "SELECT DISTINCT province FROM formation";
$results = mysqli_query($conn, $sql);

header("Content-Type: application/json");

$output = array();
while ($row = mysqli_fetch_array($results)) {
    $canonical = preg_replace("/<[^>]+>/", "", $row['province']);
    $canonical = trim($canonical);
    $canonical = explode(",", $canonical);
    foreach ($canonical as $c) {
        $c = trim($c);
        if (!empty($c) && !in_array($c, $output)) {
            array_push($output, $c);
        }
    }
}

sort($output);
echo json_encode($output);
