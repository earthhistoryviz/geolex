<?php
include_once("SqlConnection.php");

$arr = array();
$count = -1;

if (isset($_POST['search'])) {
    $searchquery = $_POST['search'];

    $sql = "SELECT * FROM formation WHERE name LIKE '%$searchquery%'";

    $result = mysqli_query($conn, $sql);

    $count = mysqli_num_rows($result);

    while ($row = mysqli_fetch_array($result)){
        $name = $row['name'];
        array_push($arr, $name);
        $output = '<h4>'.$name.'</h4>';

    }
}

?>


<!DOCTYPE html>
<html>
<title>Search for Formation</title>

<?php include("SearchBar.php"); ?>

<div>
    <?php
    if($count == -1){
    }
    else if($count == 0){
        $output = '<h4>'.'Formation not found'.'</h4>';
        print($output);
    }
    else if ($count == 1) {
        header("Location: displayInfo.php?formation=".$arr[0]);
        exit(0);
    }
    else{
    }
    foreach ($arr as $formation)
    {
        ?><b><hr><a href="displayInfo.php?formation=<?=$formation?>">Formation: <?=$formation?></a></hr></b><?php
    }?>
</div>

</body>
</html>


