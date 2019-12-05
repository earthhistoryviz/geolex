<?php
include("navBar.php");
include("SearchBar.php");
include("SqlConnection.php");
$formationName = $_REQUEST;
if($formationName[formation] == "") {?>
    <title>Empty Search</title>
    <h3><center>Please type in the search box and click "Submit" to search for Formations<br>Click on "View All Formations" to view the list of all Formations</center></h3>
    <?php
    include("footer.php");
    exit(0);
}
?>
<title><?=$formationName[formation]?></title>

<?php
$sql = "SELECT * FROM formation WHERE name LIKE '%$formationName[formation]%'";
$result = mysqli_query($conn, $sql);
while($row = mysqli_fetch_array($result)) {
    $name = $row['name'];
    $period = $row['period'];
    $age_interval = trim($row['age_interval']);
    $province = $row['province'];
    $type_locality = $row['type_locality'];
    
    $lithology = $row['lithology'];
    $lithology = preg_replace("~([^\s]+\sFm|[^\s]+\sGr)~", "<a href=\"displayInfo.php?formation=$1\">$1</a>", $lithology);
    
    $lower_contact = $row['lower_contact'];
    $lower_contact = preg_replace("~([^\s]+\sFm|[^\s]+\sGr)~", "<a href=\"displayInfo.php?formation=$1\">$1</a>", $lower_contact);
    
    $upper_contact = $row['upper_contact'];
    $upper_contact = preg_replace("~([^\s]+\sFm|[^\s]+\sGr)~", "<a href=\"displayInfo.php?formation=$1\">$1</a>", $upper_contact);
    
    $regional_extent = $row['regional_extent'];
    $regional_extent = preg_replace("~([^\s]+\sFm|[^\s]+\sGr)~", "<a href=\"displayInfo.php?formation=$1\">$1</a>", $regional_extent);
    
    $fossils = $row['fossils'];
    $fossils = preg_replace("~([^\s]+\sFm|[^\s]+\sGr)~", "<a href=\"displayInfo.php?formation=$1\">$1</a>", $fossils);
    
    $age = $row['age'];
    
    $depositional = $row['depositional'];
    $depositional = preg_replace("~([^\s]+\sFm|[^\s]+\sGr)~", "<a href=\"displayInfo.php?formation=$1\">$1</a>", $depositional);
    
    $additional_info = $row['additional_info'];
    $additional_info = preg_replace("~([^\s]+\sFm|[^\s]+\sGr)~", "<a href=\"displayInfo.php?formation=$1\">$1</a>", $additional_info);
    
    $compiler = $row['compiler'];

    //$my_array = [$lithology, $lower_contact, $upper_contact, $regional_extent, $fossils, $depositional];

}

if($name == "") {
    ?>
    <title>No Match</title>
    <h3>Nothing found for "<?=$formationName[formation]?>". Please search again.</h3>
    <?php
    include("footer.php");
    exit(0);
}

// display information below
?>
<script>
    function saveText(){
        var xr = new XMLHttpRequest();
        var url = "saveNewText.php";
        idname = "\""+idname+"\"";
        var text = document.getElementById("title").innerHTML;
        var vars = "newText="+text;
        xr.open("POST", url, true);
        xr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xr.send(vars);
    }
</script>
<?php
if (!($_SESSION["loggedIn"])) {
    ?>

    <div id="title" style="max-width: 1024px;">
        <h1><b><?=$name?></b></h1>
        <hr>
    </div>

    <div id="period" style="max-width: 1024px;">
        <h3 style="display: inline;"><b>Period: </b></h3>
        <span><?=$period?></span>
    </div>

    <div id="age_interval" style="max-width: 1024px;">
        <h3 style="display: inline;"><b>Age Interval: </b></h3>
        <span><?=$age_interval?></span>
    </div>

    <div id="province" style="max-width: 1024px;">
        <h3 style="display: inline;"><b>Province: </b></h3>
        <span><?=$province?></span>
    </div>

    <div id="type_locality" style="max-width: 1024px;">
        <h3><b>Type Locality and Naming</b></h3>
        <p><?=$type_locality?></p>
    </div>

    <div id="lithology" style="max-width: 1024px;">
        <h3><b>Lithology and Thickness</b></h3>
        <p><?=$lithology?></p>
    </div>

    <div id="relationships_distribution" style="max-width: 1024px;">
        <h3><b>Relationships and Distribution</b></h3>
        <div id="lower_contact" style="text-indent: 50px; max-width: 1024px;">
            <h4 style="display: inline;">Lower Contact: </h4>
            <span><?=$lower_contact?></span>
        </div>
        <div id="upper_contact" style="text-indent: 50px; max-width: 1024px;">
            <h4 style="display: inline;">Upper Contact: </h4>
            <span><?=$upper_contact?></span>
        </div>
        <div id="regional_extent" style="text-indent: 50px; max-width: 1024px;">
            <h4 style="display: inline;">Regional Extent: </h4>
            <span><?=$regional_extent?></span>
        </div>
    </div>

    <div id="fossils" style="max-width: 1024px;">
        <h3><b>Fossils</b></h3>
        <p><?=$fossils?></p>
    </div>

    <div id="age" style="max-width: 1024px;">
        <h3 style="display: inline;"><b>Age:</b></h3>
        <span><?=$age?></span>
    </div>

    <div id="depositional" style="max-width: 1024px;">
        <h3><b>Depositional setting</b></h3>
        <p><?=$depositional?></p>
    </div>

    <div id="additional_info" style="max-width: 1024px;">
        <h3><b>Additional Information</b></h3>
        <p><?=$additional_info?></p>
    </div>

    <div id="compiler" style="max-width: 1024px;">
        <h3 style="display: inline;"><b>Compiler:</b></h3>
        <span><?=$compiler?></span>
    </div>

    <?php
}

// If the user logged in

else {
    ?>
    <div id="title" contenteditable="true" onblur="saveText()">
        <h1><b><?=$name?></b></h1>
        <hr>
    </div>

    <div id="period" contenteditable="true">
        <h3 style="display: inline;"><b>Period: </b></h3>
        <span><?=$period?><br></span>
    </div>

    <div id="age_interval" contenteditable="true">
        <h3 style="display: inline;"><b>Age Interval: </b></h3>
        <span><?=$age_interval?><br></span>
    </div>

    <div id="province" contenteditable="true">
        <h3 style="display: inline;"><b>Province: </b></h3>
        <span><?=$province?><br></span>
    </div>

    <div id="type_locality" contenteditable="true">
        <h3><b>Type Locality and Naming</b></h3>
        <p><?=$type_locality?><br></p>
    </div>

    <div id="lithology" contenteditable="true">
        <h3><b>Lithology and Thickness</b></h3>
        <p><?=$lithology?><br></p>
    </div>

    <div id="relationships_distribution">
        <h3><b>Relationships and Distribution</b></h3>
        <div id="lower_contact">
            <h4><i>Lower contact</i></h4>
            <p contenteditable="true"><?=$lower_contact?></p>
        </div>
        <div id="upper_contact">
            <h4><i>Upper contact</i></h4>
            <p contenteditable="true"><?=$upper_contact?></p>
        </div>
        <div id="regional_extent">
            <h4><i>Regional extent</i></h4>
            <p contenteditable="true"><?=$regional_extent?><br></p>
        </div>
    </div>

    <div id="fossils">
        <h3><b>Fossils</b></h3>
        <p contenteditable="true"><?=$fossils?><br></p>
    </div>

    <div id="age">
        <h3 style="display: inline;"><b>Age: </b></h3>
        <span contenteditable="true"><?=$age?><br></span>
    </div>

    <div id="depositional">
        <h3><b>Depositional setting</b></h3>
        <p contenteditable="true"><?=$depositional?><br></p>
    </div>

    <div id="additional_info">
        <h3><b>Additional Information</b></h3>
        <p contenteditable="true"><?=$additional_info?><br></p>
    </div>

    <div id="compiler">
        <h3 style="display: inline;"><b>Compiler: </b></h3>
        <span contenteditable="true"><?=$compiler?><br></span>
    </div>
    <?php
}
?>

<?php
include("footer.php");
?>
