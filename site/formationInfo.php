<?php 
include_once("SqlConnection.php");
$sql = "SELECT * FROM formation WHERE name LIKE '%$formation[formation]%'"; //old query that won't work with Kali vs. Warkali formations or characters needing to be escaped

$sql = sprintf("SELECT * FROM formation WHERE name= '%s'", mysqli_real_escape_string($conn, $formation["formation"]));
$result = mysqli_query($conn, $sql);
$fmdata = array(
   'name'                                  => array("needlinks" => false),
   'period'                                => array("needlinks" => false),
   'age_interval'                          => array("needlinks" => false), 
   'province'                              => array("needlinks" => false),
   'type_locality'                         => array("needlinks" => false),
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
?>