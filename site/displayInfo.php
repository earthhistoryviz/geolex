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
    $id = $row['ID'];
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
<style>
    [contenteditable="true"] {
        font-family: "Rajdhani";
        color: #C00;
    }
    #Save{
        height: 40px;
        border: 3px solid #000000;
    }
    #AddNewFile{
        height: 40px;
        border: 3px solid #000000;
    }
    #Edit{
        height: 40px;
        border: 3px solid #000000;
    }
</style>

<script>
    function saveText(){
        var xr = new XMLHttpRequest();
        var url = "saveNewText.php";
        idname = "\""+idname+"\"";
        var text = document.getElementById("title").innerHTML;
        // var id = document.getElementById("").innerHTML;
        console.log(text);
        var vars = "newText="+text;
        xr.open("POST", url, true);
        xr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xr.send(vars);
    }
    function editValues(){
        alert("Form submitted!");
        return false;
    }
</script>
<?php
if (!($_SESSION["loggedIn"])) {
    ?>
    <form onsubmit ="return editValues();" class="my-form">
    <div id="title" style="max-width: 1024px;">
        <h1><b><?=$name?></b></h1>
        <hr>
    </div>

    <div id="id" style="max-width: 1024px;">
        <h3 style="display: inline;"><b>ID: </b></h3>
        <span><?=$id?></span>
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
        <div id="lower_contact" style="text-indent: 50px; max-width: 1024px;" >
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
    </form>


        <?php
}

// If the user logged in

else {
    ?>
    <input id="Edit" type ="button" value = "Edit">
    <input id="Save" type="button" value="Save" disabled onclick="save()">
    <input id="AddNewFile" type="button" value="Add new files" disabled>
    <div id= onblur="saveText()">
        <b><h1 id ='title'><?=$name?></h1></b>
        <hr>
    </div>
    
    <div id="id" style="max-width: 1024px;">
        <h3 style="display: inline;"><b>ID: </b></h3>
        <span id ="id_value"><?=$id?></span>
    </div>

    <div id="period">
        <h3 style="display: inline;"><b>Period: </b></h3>
        <span id="period_value"><?=$period?></span><br>
    </div>

    <div id="age_interval">
        <h3 style="display: inline;"><b>Age Interval: </b></h3>
        <span id ="agein_value"><?=$age_interval?></span><br>
    </div>

    <div id="province" >
        <h3 style="display: inline;"><b>Province: </b></h3>
        <span id = "province_value"><?=$province?></span><br>
    </div>

    <div id="type_locality">
        <h3><b>Type Locality and Naming</b></h3>
        <p id="type_value"><?=$type_locality?></p><br>
    </div>

    <div id="lithology">
        <h3><b>Lithology and Thickness</b></h3>
        <p id ="lithology_value"><?=$lithology?></p><br>
    </div>

    <div id="relationships_distribution">
        <h3><b>Relationships and Distribution</b></h3>
        <div id="lower_contact">
            <h4><i>Lower contact</i></h4>
            <p id="lower_value"><?=$lower_contact?></p>
        </div>
        <div id="upper_contact">
            <h4><i>Upper contact</i></h4>
            <p id="upper_value"><?=$upper_contact?></p>
        </div>
        <div id="regional_extent">
            <h4><i>Regional extent</i></h4>
            <p id="regional_value"><?=$regional_extent?></p><br>
        </div>
    </div>

    <div id="fossils">
        <h3><b>Fossils</b></h3>
        <p id ="fossil_value"><?=$fossils?></p><br>
    </div>

    <div id="age">
        <h3 style="display: inline;"><b>Age: </b></h3>
        <span id="age_value"><?=$age?></span><br>
    </div>

    <div id="depositional">
        <h3><b>Depositional setting</b></h3>
        <p id="depo_value"><?=$depositional?></p><br>
    </div>

    <div id="additional_info">
        <h3><b>Additional Information</b></h3>
        <p id="ad_value"><?=$additional_info?></p><br>
    </div>

    <div id="compiler">
        <h3 style="display: inline;"><b>Compiler: </b></h3>
        <span id="comp_val"><?=$compiler?></span><br>
    </div>
    <?php
}
?>
<script type="text/javascript">
        var editBtn = document.getElementById('Edit');
        var saveBtn = document.getElementById('Save');
        var editables = document.querySelectorAll('#id_value, #title, #period_value, #agein_value , #province_value, #type_value, #lithology_value, #lower_value, #upper_value, #regional_value, #fossil_value, #age_value,' +
            '#depo_value, #ad_value, #comp_val');
        editBtn.addEventListener('click',function(e){
            if(!editables[0].isContentEditable){
                saveBtn.disabled = false;
                for (var i = 0;i<editables.length;i++){
                    editables[i].contentEditable = true;

                }
            }
            else{
                saveBtn.disabled = true;
                for (var i = 0;i<editables.length;i++){
                    editables[i].contentEditable = false;

                }
            }
        });
        saveBtn.addEventListener('click',function(e){
            for (var i = 0;i<editables.length;i++) {
                localStorage.setItem(editables[i].getAttribute('id'), editables[i].innerHTML);
            }
            var id_value = localStorage.getItem('id_value');
            var title = localStorage.getItem('title');
            var period_value = localStorage.getItem('period_value');
            var agein_value = localStorage.getItem('agein_value');
            var province_value = localStorage.getItem('province_value');
            var type_value = localStorage.getItem('type_value');
            var lithology_value = localStorage.getItem('lithology_value');
            var lower_value = localStorage.getItem('lower_value');
            var upper_value = localStorage.getItem('upper_value');
            var regional_value = localStorage.getItem('regional_value');
            var fossil_value = localStorage.getItem('fossil_value');
            var age_value = localStorage.getItem('age_value');
            var depo_value = localStorage.getItem('depo_value');
            var ad_value = localStorage.getItem('ad_value');
            var comp_value = localStorage.getItem('comp_val');
            var savedata = {
                            "id_value":id_value,
                            "title":title,
                            "period_value":period_value,
                            "agein_value":agein_value,
                            "province_value":province_value,
                            "type_value":type_value,
                            "lithology_value":lithology_value,
                            "lower_value":lower_value,
                            "upper_value":upper_value,
                            "regional_value":regional_value,
                            "fossil_value":fossil_value,
                            "age_value":age_value,
                            "depo_value":depo_value,
                            "ad_value":ad_value,
                            "comp_value":comp_value};

            form = document.createElement('form');
            document.body.appendChild(form);
            form.method="POST";
            form.action = "saveData.php";
            var length = Object.keys(savedata).length;
            for(var data in savedata) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = data;
                input.value = savedata[data];
                form.appendChild(input);
            }
            form.submit();
            document.removeChild(form);

        });
</script>

<?php
include("footer.php");
?>