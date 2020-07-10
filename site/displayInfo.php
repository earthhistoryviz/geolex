<?php
include("navBar.php");
include("SearchBar.php");
include("SqlConnection.php");
$formation = $_REQUEST;
$auth = $_SESSION["loggedIn"];

if($formation[formation] == "") {?>
    <title>Empty Search</title>
    <h3><center>Please type in the search box and click "Submit" to search for Formations<br>Click on "View All Formations" to view the list of all Formations</center></h3>
    <?php
    include("footer.php");
    exit(0);
}


$imagedisplaycount = 0;
function displayImages($images, $imtype) {
  foreach($images[$imtype] as $i) {
    $id = "image_".$imtype."_".$imagedisplaycount;
    ?><div id="<?php echo $id?>">
      <a href="<?php echo $i["full"];?>">
        <img src="<?php echo $i["thumbnail"];?>" style="max-width: 200px; max-height: 200px;" />
	    </a>
	    <input id="<?php echo $i["full"];?>" type="button" value="Delete" onclick='delImageClicked("<?php echo $i['full'];?>", "<?php echo $imtype?>", "<?php echo $id?>");' />
    </div><?php
  }
}

function eliminateParagraphs($str) {
  while (preg_match("/<p>.*</p>/g", $str)) {
    $str = preg_replace("/<p>.*</p>/g", "", $str);
  }
  return $str;
}

?>
<title><?=$formation[formation]?></title>

<?php
// Get all the formation names to build the regexp searches in the text for automatic link creation
$sql = "SELECT ID,name FROM formation";
$result = mysqli_query($conn, $sql);
$nameregexes = array();
while($row = mysqli_fetch_array($result)) {
  $rid = $row['ID'];
  $rname = $row['name'];
  // turn name into regular expression allowing arbitrary number of spaces between words
  preg_replace("/ /g", " \*", $rname);
  array_push($nameregexes, array(
    "ID" => $rid,
    "name" => $rname,
    "regex" => "/($rname)/i"
  ));
}
//echo "nameregexes = <pre>"; print_r($nameregexes); echo "</pre>";

function findAndMakeFormationLinks($str, $nameregexes) {
  for($i=0; $i<count($nameregexes); $i++) {
    $n = $nameregexes[$i];
    $str = preg_replace($n["regex"], "<a href=\"displayInfo.php?formation=".$n["name"]."\">".$n["name"]."</a>", $str);
  }
  return $str;
}

$sql = "SELECT * FROM formation WHERE name LIKE '%$formation[formation]%'";
$result = mysqli_query($conn, $sql);
while($row = mysqli_fetch_array($result)) {
    $id = $row['ID'];
    $name = $row['name'];
    $period = $row['period'];
    $age_interval = trim($row['age_interval']);
    $province = $row['province'];
    $type_locality = $row['type_locality'];
    $age = $row['age'];
    
    //$fmgr_regexp = "~([^\s]+\sFm|[^\s]+\sGr)([ .,;:])~"; // this is the old regexp
    $lithology = findAndMakeFormationLinks($row['lithology'], $nameregexes);
    $lower_contact = findAndMakeFormationLinks($row['lower_contact'], $nameregexes);
    $upper_contact = findAndMakeFormationLinks($row['upper_contact'], $nameregexes);
    $regional_extent = findAndMakeFormationLinks($row['regional_extent'], $nameregexes);
    $fossils = findAndMakeFormationLinks($row['fossils'], $nameregexes);
    $depositional = findAndMakeFormationLinks($row['depositional'], $nameregexes);
    $additional_info = findAndMakeFormationLinks($row['additional_info'], $nameregexes);

    $compiler = $row['compiler'];

    //$my_array = [$lithology, $lower_contact, $upper_contact, $regional_extent, $fossils, $depositional];

}

if($name == "") {
    ?>
    <title>No Match</title>
    <h3>Nothing found for "<?=$formation[formation]?>". Please search again.</h3>
    <?php
    include("footer.php");
    exit(0);
}


// Fetch any image filenames for this formation from the disk
$dirs = scandir("./uploads/$name");
$images = array();
if ($dirs) {
  foreach($dirs as $type) {
    if (preg_match('/^\./', $type)) continue;
    $files = scandir("./uploads/$name/$type");
    if ($files) {
      foreach($files as $f) {
        if (preg_match('/^\./', $f)) continue;
        if (preg_match('/^thumb_/', $f)) continue;
        if (!$images[$type]) $images[$type] = array();
        array_push($images[$type], array(
          "full" => "/uploads/$name/$type/$f",
          "thumbnail" => "uploads/$name/$type/$f",
        ));
      }
    }
  }
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
        min-width: 60px;
        font-weight: bold;
        border-radius: 5px;
        padding-left: 20px;
        padding-right: 20px;
    }
    #Delete{ 
    	height:40px;
      	border:3px solid #000000;
	}
    #AddNewFile{
        height: 40px;
        border: 3px solid #000000;
        font-weight: bold;
        border-radius: 5px;
        min-width: 60px;
        padding-left: 20px;
        padding-right: 20px;
    }
    #Edit{
        height: 40px;
        border: 3px solid #000000;
        min-width: 60px;
        font-weight: bold;
        border-radius: 5px;
        padding-left: 20px;
        padding-right: 20px;
    }
    p {
      text-indent: 2em;
      margin-bottom: 0px;
      margin-top: 0px;
    }
</style>

<?php
if ($auth) {
?>
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
}

    ?>
    <style>
      .horiz {
        display: flex;
        flex-direction: row;
      }
      .big {
        font-size: 1.2em;
      }
      .minwidth {
        min-width: 50px;
      }
    </style>

    <?php if ($auth) {?>
      <input id="Edit" type ="button" value = "Edit">
      <input id="Save" type="button" value="Save" disabled>
      <input id="AddNewFile" type="button" value="Add new files" disabled>
      <input id="Delete" type="button" value="Delete" name="Delete Formation" onclick = deleteform() />
    <?php } ?>

    <div>
        <b><h1 id ='title'><?=$name?></h1></b>
        <?php if ($auth) {?>
          <input type="file" name="title_image" id ="title_image"/>
          <input id="Addtitle" type="button" name="add_title_image" value="Add Chosen Title Image" onclick = addImageClicked('title') />
        <?php } ?>
        <div style="display: flex; flex-direction: row;">
          <?php displayImages($images, 'title') ?>
        </div>
        <hr>
    </div>
   
    <?php if ($auth) {?>
    <div id="id" class="horiz" style="max-width: 1024px;">
        <b>ID:&nbsp; </b>
        <div id ="id_value"><?=eliminateParagraphs($id)?></div>
    </div>
    <?php } ?>

    <div id="period" class="horiz">
        <b>Period:&nbsp; </b>
        <div id="period_value" class="minwidth"><?=eliminateParagraphs($period)?></div><br>
    </div>

    <div id="age_interval" class="horiz">
        <b>Age Interval:&nbsp; </b>
        <div id ="agein_value" class="minwidth"><?=eliminateParagraphs($age_interval)?></div><br>
    </div>

    <div id="province" class="horiz" >
        <b>Province:&nbsp; </b>
        <div id = "province_value" class="minwidth"><?=eliminateParagraphs($province)?></div><br>
    </div>

    <div id="type_locality">
        <h3><b>Type Locality and Naming</b></h3>
        <div id="type_value"><?=$type_locality?></div><br>
        <?php if ($auth) {?>
          <input type="file" name="locality_image" id ="locality_image"/>
          <input id="Addlocality" type="button" name="add_locality_image" value="Add Chosen Locality Image" onclick = "addImageClicked('locality')" />
        <?php } ?>
        <?php displayImages($images, 'locality') ?>
    </div>

    <div id="lithology">
        <h3><b>Lithology and Thickness</b></h3>
        <div id ="lithology_value"><?=$lithology?></div><br>
        <?php if ($auth) {?>
          <input type="file" name="lithology_image" id = "lithology_image"/>
          <input id="Addlithology" type="button" name="add_lithology_image" value="Add Chosen Lithology Image" onclick="addImageClicked('lithology')" />
        <?php } ?>
        <?php displayImages($images, 'lithology') ?>
    </div>

    <div id="relationships_distribution">
        <h3><b>Relationships and Distribution</b></h3>
        <div id="lower_contact">
            <h4><i>Lower contact</i></h4>
            <div id="lower_value"><?=$lower_contact?></div>
            <?php if ($auth) {?>
              <input type="file" name="lowercontact_image" id = "lowercontact_image"/>
              <input id="Addlowercontact" type="button" name="add_lowercontact_image" value="Add Chosen Lower Contact Image" onclick = addImageClicked('lowercontact') />
            <?php } ?>
            <?php displayImages($images, 'lowercontact') ?>
        </div>
        <div id="upper_contact">
            <h4><i>Upper contact</i></h4>
            <div id="upper_value"><?=$upper_contact?></div>
            <?php if ($auth) {?>
              <input type="file" name="uppercontact_image" id = "uppercontact_image"/>
              <input id="Adduppercontact" type="button" name="add_uppercontact_image" value="Add Chosen Upper Contact Image" onclick = addImageClicked('uppercontact') />
            <?php } ?>
            <?php displayImages($images, 'uppercontact') ?>
        </div>
        <div id="regional_extent">
            <h4><i>Regional extent</i></h4>
            <div id="regional_value"><?=$regional_extent?></div><br>
            <?php if ($auth) {?>
              <input type="file" name="regionalcontact_image" id = "regionalextent_image"/>
              <input id="Addregionalextent" type="button" name="add_regionalextent_image" value="Add Chosen Regional Extent Image" onclick = addImageClicked('regionalextent') />
            <?php } ?>
            <?php displayImages($images, 'regionalextent') ?>
        </div>
    </div>

    <div id="fossils">
        <h3><b>Fossils</b></h3>
        <div id ="fossil_value"><?=$fossils?></div><br>
        <?php if ($auth) {?>
          <input type="file" name="fossil_image" id = "fossil_image"/>
          <input id="Addfossil" type="button" name="add_fossil_image" value="Add Chosen Fossil Image" onclick="addImageClicked('fossil')" />
        <?php } ?>
        <?php displayImages($images, 'fossil') ?>
    </div>

    <div id="age">
        <h3><b>Age&nbsp; </b></h3>
        <div id="age_value"><?=eliminateParagraphs($age)?></div><br>
    </div>

    <div id="depositional">
        <h3><b>Depositional setting</b></h3>
        <div id="depo_value"><?=$depositional?></div><br>
    </div>

    <div id="additional_info">
        <h3><b>Additional Information</b></h3>
        <div id="ad_value"><?=$additional_info?></div><br>
    </div>

    <div id="compiler">
        <h3><b>Compiler</b></h3>
        <div id="comp_val"><?=eliminateParagraphs($compiler)?></div><br>
    </div>

<?php if ($auth) {?> 
<script type ="text/javascript">
function deleteform(){
	console.log("delete pressed");
	var title1 = document.getElementById('title');
	newform = document.createElement('form');
	document.body.appendChild(newform);
	newform.method = "POST";
	newform.action = "deleteForm.php";
	input = document.createElement('input');
	input.type = "hidden";
	input.name = "title";
	input.value = title1.innerHTML;
	newform.appendChild(input);
	newform.submit();
	document.removeChild(newform);

}
function delImageClicked(path, type, id){
	console.log("delImageClicked: asked to delete image at path ", path);
	const fullpath = "/app" + path;
	let form = new FormData();
	form.append("Img_select",fullpath);

	let x = fetch('/delete_image.php',{method:"POST",body:form}).then(function(res){
    res.text().then(function(val) {
      console.log('Response text = ', val);
	    img = document.getElementById(id);
    	img.parentNode.removeChild(img);
    });
	}).catch(function(e){
 		console.log("Error deleting image:",e);
	});

}


function addImageClicked(type) {
    let img;
    if (type === "lithology") {
        console.log("lithology");
        img = document.getElementById('lithology_image').files[0]
    } else if (type === "title") {
        console.log("title");
        img = document.getElementById('title_image').files[0]
    } else if (type === "fossil") {
        console.log("fossil");

        img = document.getElementById('fossil_image').files[0]
    }
        else if(type ==="locality") {
            img = document.getElementById('locality_image').files[0]
        console.log("locality")
        }
    else if (type === "lowercontact") {
        console.log("lowercontact");
        img = document.getElementById('lowercontact_image').files[0]
    }
    else if (type === "uppercontact") {
        console.log("uppercontact");
        img = document.getElementById('uppercontact_image').files[0]
    }
    else if (type === "regionalextent") {
        console.log("regionalextent");
        img = document.getElementById('regionalextent_image').files[0]
    }
    else {
        img = 0;
    }
    let form = new FormData();

    form.append("formation_name", "<?php echo $name?>");
    form.append("image_type", type);
    form.append("image", img);

    let x = fetch('/uploadImage.php', {method: "POST", body: form })
    .then(function(res) {
      console.log('HTTP response code:', res.text().then(function(a){console.log(a)}));
    }).catch(function(e) {
      console.log("Error uploading image: ", e);
    });
   //location.reload()
}

</script>
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
            var savedata = {};
            for (let i = 0; i<editables.length; i++) {
              savedata[editables[i].id] = editables[i].innerHTML;
            }
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
            //console.log(savedata["id_value"])
            form.submit();
            document.removeChild(form);
        });

</script>
<?php } ?>
<?php
include("footer.php");
?>
