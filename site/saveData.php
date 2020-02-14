<?php
include("navBar.php");
include("SearchBar.php");
include("SqlConnection.php");
$list_names = ['title','period_value','agein_value','province_value','type_value','lithology_value','lower_value','upper_value','regional_value','fossil_value',
                'age_value','depo_value','ad_value','comp_value'];
$list_values = [];

for($i = 0;$i<sizeof($list_names); $i = $i +1){

    array_push($list_values,$_POST[$list_names[$i]]);

}
echo sizeof($list_values);
$sql = "INSERT INTO formation(name,period,age_interval,province,type_locality,lithology,lower_contact,upper_contact,regional_extent,fossils,age,depositional,additional_info,compiler)
       VALUES(
        '$list_values[0]',
        '$list_values[1]',
        '$list_values[2]',
        '$list_values[3]',
        '$list_values[4]',
        '$list_values[5]',
        '$list_values[6]',
        '$list_values[7]',
        '$list_values[8]',
        '$list_values[9]',
        '$list_values[10]',
        '$list_values[11]',
        '$list_values[12]',
        '$list_values[13]')";


if ($conn->query($sql) === TRUE) {
       echo "data inserted ";
} else {
     echo "Error inserted " . $conn->error;
}
?>
