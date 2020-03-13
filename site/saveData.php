<?php
include("navBar.php");
include("SearchBar.php");
include("SqlConnection.php");
$list_names = ['id_value','title','period_value','agein_value','province_value','type_value','lithology_value','lower_value','upper_value','regional_value','fossil_value',
                'age_value','depo_value','ad_value','comp_value'];
$list_values = [];

for($i = 0;$i<sizeof($list_names); $i = $i +1){

    array_push($list_values,mysqli_real_escape_string($conn,$_POST[$list_names[$i]]));

}
/*for($i = 0; i<sizeof($list_values);$i = $i+1){
$list_values[i] = mysqli::real_escape_string($list_values[i]);
}*/
echo sizeof($list_values);
$sql = "UPDATE formation
       SET name = '$list_values[1]',
           period ='$list_values[2]',
           age_interval ='$list_values[3]',
           province = '$list_values[4]',
           type_locality ='$list_values[5]',
           lithology ='$list_values[6]',
           lower_contact = '$list_values[7]',
           upper_contact = '$list_values[8]',
           regional_extent ='$list_values[9]',
           fossils = '$list_values[10]',
           age ='$list_values[11]',
           depositional = '$list_values[12]',
           additional_info = '$list_values[13]',
           compiler = '$list_values[14]'
           WHERE id = '$list_values[0]';";
echo "<pre>$sql</pre>";

if ($conn->query($sql) === TRUE) {
       echo "data inserted ";
} else {
     echo "Error inserted " . $conn->error;
}
header("Location:displayInfo.php");
?>
