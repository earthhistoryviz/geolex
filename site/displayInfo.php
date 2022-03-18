<?php
include_once("navBar.php");
include_once("SearchBar.php");
include_once("SqlConnection.php");
//include_once("formationInfo.php");
//$recongeoJSON = fopen("reconstruct.txt", "w"); // output file to write all geojson information to
$formation = $_REQUEST;
$auth = $_SESSION["loggedIn"];
//echo $auth;
if($formation[formation] == "") {?>
    <title>Empty Search</title>
    <h3><center>Please type in the search box and click "Submit" to search for Formations<br>Click on "View All Formations" to view the list of all Formations</center></h3>
    <?php
    include("footer.php");
    exit(0);
}

/* Prep for reconstructions, mirrors code from general.php */


/* end of reconstruction prep */

$imagedisplaycount = 0;
function displayImages($images, $imtype) {
  global $fmdata;
  
  foreach($images[$imtype] as $i) {
    $id = "image_".$imtype."_".$imagedisplaycount;
    ?><div id="<?php echo $fmdata["name"]["display"];?>">
      <a href="<?php echo $i["full"];?>">
        <img src="<?php echo $i["thumbnail"];?>" style="max-width: 200px; max-height: 200px;" />
      </a>
  <?php 
  if($_SESSION["loggedIn"]){ ?>
      <input id="<?php echo $i["full"];?>" type="button" value="Delete" onclick='delImageClicked("<?php echo $i['full'];?>", "<?php echo $imtype;?>", "<?php echo $fmdata["name"]["display"];?>");' />
  <?php } ?>
    </div><?php
  }
}

function eliminateParagraphs($str) {
while(preg_match("/<p>.*</p>/g", $str)) {
    $str = preg_replace("/<p>.*</p>/g", "", $str);
  }
  return $str;
}

?>
<title><?=$formation[formation]?></title>

<?php
// Get all the formation names to build the regexp searches in the text for automatic link creation
$sql = "SELECT name FROM formation";
$result = mysqli_query($conn, $sql);
$nameregexes = array();
while($row = mysqli_fetch_array($result)) {
  $rname = $row['name'];
  // turn name into regular expression allowing arbitrary number of spaces between words
  preg_replace("/ /g", " \*", $rname);
  array_push($nameregexes, array(
    "name" => $rname,
    "regex" => "/\b($rname)/i"
  ));
}
//echo "nameregexes = <pre>"; print_r($nameregexes); echo "</pre>";

function findAndMakeFormationLinks($str, $nameregexes) {
   $orig = $str;
  for($i=0; $i<count($nameregexes); $i++) {
    $n = $nameregexes[$i];
    $str = preg_replace($n["regex"], "<a href=\"displayInfo.php?formation=".$n["name"]."\">".$n["name"]."</a>", $str);
  }
  return trim($str);
}


//$sql = "SELECT * FROM formation WHERE name LIKE '%$formation[formation]%'"; old query that won't work with Kali vs. Warkali formations or characters needing to be escaped
/*
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
*/
include_once("formationInfo.php");
/*
if($fmdata['geojson']){
	$index = 0; // used to get the name, from age and to age once geojson strings identified
	foreach($fmdata['geojson'] as $specific){
        $properties = '/"properties":{(.*)}/'; // gets the properties part of the long geoJSON text
	if($properties){
	$properties = preg_quote($properties, '/'); // adds the escape characters to those that need it
	$oldprop = preg_match($properties, $specific); // gets the properties part of the geoJSON string
	$newprop = substr_replace($oldprop, "", -1); // removes the closing bracket so more information can be added
        $newprop = $newprop.'"Name":'. $fmdata['name'][$index].','.'"FROMAGE":'.$fmdata['beg_date'][$index].','.'"TOAGE":'.$fmdata['end_date'][$index]."}"; // adds new parts
	str_replace($oldprop, $newprop, $specific); // replaces characters in properties with characters in oldproperties in string specific
	array_replace($fmdata['geojson'][$index], $specific);
        
	}
	$index = $index + 1;



  }
} */
$found = false;
while($row = mysqli_fetch_array($result)) {
  $found = true;
  // Fill in each of the variables that we're going to send to the browser
  foreach($fmdata as $varname => $varvalue) {
    $rowval = $row[$varname];
    $fmdata[$varname]["raw"] = trim($rowval);
    $fmdata[$varname]["display"] = trim($rowval);
    if ($varvalue["needlinks"]) {
      $fmdata[$varname]["display"] = findAndMakeFormationLinks($rowval, $nameregexes);
    }
  }
}
//var_dump($fmdata);
//var_dump($fmdata["beg_date"]["display"]);
//if ((float) $fmdata["beg_date"]["display"] > 0){
//	echo "True";
//}
//-----------------------------------------------------------
// Start outputting the page
//-----------------------------------------------------------

if(!$found) {
    ?>
    <title>No Match</title>
    <h3>Nothing found for "<?=$formation[formation]?>". Please search again.</h3>
    <?php
    include("footer.php");
    exit(0);
}

$name = $fmdata["name"]["display"];
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


$header = '{
    "type": "FeatureCollection",
    "name": "Triassic strata_10Feb2021",
    "crs": { "type": "name", "properties": { "name": "urn:ogc:def:crs:OGC:1.3:CRS84" } },
    "features": [
';

$reconForm = $header;

// geojson processing to output file 
//** BEGINNING OF PROCESSING THREE GEOJSONS FORMAT:
//Format 1: Thailand strata completely done by hand (i.e. Chaiburi Fm)
//Format 2: Digitized geojson where each formation is its own feature collection (i.e. Zanskar region)
//Format 3: Geojsons without a properties aspect once you get to the "type": "Feature" (See database for Milliolite for this)
//(It becomes easier to see these three formats when looking at the database too) 

   $output = json_decode(strip_tags($fmdata["geojson"]["display"]), true); // what will be sent to recon.geojson for reconstruction purposes (basically geoJSON wo FROMAGE and TOAGE)
   // will have another variable for the display under geoJSON section (that will have the from age and to age)
  // condition 1 of geojson processing 
  if(array_key_exists("features", $output) && !(array_key_exists("properties", $output["features"][0]) ||array_key_exists("properties", $output)) && $output) {
  $properties = array("NAME" => $name, "FROMAGE" => null, "TOAGE" => null); // creating properties array
  $appendProp["properties"] = $properties;
  array_splice($output["features"]["0"], 1, 0, $appendProp); // adding the properties array in with the geojson
  $output["features"]["0"]["properties"] = $output["features"]["0"][0]; // properties array in json is indexed with number rather than phrase "properties"
  unset($output["features"]["0"][0]); // renaming the key 0 to be properties instead
  krsort($output["features"]["0"]); // reverse sorting so that properties is in right place and pygplates can partition correctly
  $output = json_encode($output["features"]["0"], JSON_PRETTY_PRINT); // altering displayed geojson
  }


  else if(!(array_key_exists("features", $output)) && !(array_key_exists("properties", $output["features"][0]) ||array_key_exists("properties", $output)) && $output){
  $properties = array("NAME" => $name, "FROMAGE" => null, "TOAGE" => null);
  $appendProp["properties"] = $properties;
  array_splice($output, 1, 0, $appendProp); // adding the properties array in with the geojson
  $output["properties"] = $output[0]; // properties array in json is indexed with number rather than phrase "properties"
  unset($output[0]); // renaming the key 0 to be properties instead
  krsort($output); // reverse sorting so that properties is in right place and pygplates can partition correctly
  $output = json_encode($output, JSON_PRETTY_PRINT);
  }


  // condition 3 of geojson processing 
  // format with properties tag but each formation is feature collection 
else if($output["type"] == "FeatureCollection"){
    $output["features"][0]["properties"]["NAME"] = $name;
    $output["features"][0]["properties"]["FROMAGE"] = null;
    $output["features"][0]["properties"]["TOAGE"] = null;
    $output = json_encode($output["features"][0], JSON_PRETTY_PRINT);
}
 // condition four of geojson format 
else{
     $output = json_encode($output);
  }

$reconForm .= $output;
$reconForm .= ']}';



// geojson processing for under field called geoJSON on display page 
if($fmdata["geojson"]["display"] != "null"){
$output = json_decode(strip_tags($fmdata["geojson"]["display"]), true); // decoding one of the three formats from the database (database stores some HTML tags)

// if no where in the three formats there is a "properties" attribute (format 3)
//   CONDITION 1 DIRECTLY BELOW(for format 2)                 CONDITION 2 DIRECTLY BELOW (for format 1)      CONDITION 3 DIRECTLY BELOW (don't want to append if there's nothing)
if(array_key_exists("features", $output) && !(array_key_exists("properties", $output["features"][0]) ||array_key_exists("properties", $output)) && $fmdata["geojson"]["display"]) {
$properties = array("NAME" => $fmdata["name"]["display"], "FROMAGE" => $fmdata["beg_date"]["display"], "TOAGE" => $fmdata["end_date"]["display"]); // creating properties array
  $appendProp["properties"] = $properties;
  array_splice($output["features"]["0"], 1, 0, $appendProp); // adding the properties array in with the geojson 
  $output["features"]["0"]["properties"] = $output["features"]["0"][0]; // properties array in json is indexed with number rather than phrase "properties"
  unset($output["features"]["0"][0]); // renaming the key 0 to be properties instead
  krsort($output["features"]["0"]); // reverse sorting so that properties is in right place and pygplates can partition correctly
 // echo "<pre>";
 // print_r($output);
 // echo "</pre>"; 
  $fmdata["geojson"]["display"] = json_encode($output["features"]["0"]); // altering displayed geojson 
}
else if(!(array_key_exists("features", $output)) && !(array_key_exists("properties", $output["features"][0]) ||array_key_exists("properties", $output)) && $fmdata["geojson"]["display"]){
  $properties = array("NAME" => $fmdata["name"]["display"], "FROMAGE" => $fmdata["beg_date"]["display"], "TOAGE" => $fmdata["end_date"]["display"]); // creating properties array	
  $appendProp["properties"] = $properties;
  array_splice($output, 1, 0, $appendProp); // adding the properties array in with the geojson
  $output["properties"] = $output[0]; // properties array in json is indexed with number rather than phrase "properties"
  unset($output[0]); // renaming the key 0 to be properties instead
  krsort($output); // reverse sorting so that properties is in right place and pygplates can partition correctly
  //echo "<pre>";
  //print_r($output);
  //echo "</pre>";
  $fmdata["geojson"]["display"] = json_encode($output);
}

 
// if there is a properties attribute (deals w format one and two)
else if($fmdata["geojson"]["display"]){
// format 2 (with feature collection)
if($fmdata["beg_date"]["display"] && $fmdata["end_date"]["display"] && $output["type"] == "FeatureCollection"){
$output["features"][0]["properties"]["NAME"] = $fmdata["name"]["display"];	
$output["features"][0]["properties"]["FROMAGE"] = $fmdata["beg_date"]["display"];
$output["features"][0]["properties"]["TOAGE"] = $fmdata["end_date"]["display"];
} 

// format 1 geojsons by hand 
else if($fmdata["beg_date"]["display"] && $fmdata["end_date"]["display"]){
$output["properties"]["FROMAGE"] = $fmdata["beg_date"]["display"];
$output["properties"]["TOAGE"] = $fmdata["end_date"]["display"];
} 

// don't want changes if geojson from database was null 
if($output["type"] == "FeatureCollection"){
$fmdata["geojson"]["display"] =  json_encode($output["features"][0]);
}
else{
$fmdata["geojson"]["display"] = json_encode($output);
}
}
}
else {
 $fmdata["geojson"]["display"] = "";
}

// create output directory for json file to be processed by pygplates (each output directory corresponds to a different formation that is clicked and has a beginning date and geoJSON info to reconstruct from)
if ($_REQUEST["generateImage"]) {
    if($_REQUEST["generateImage"] == 1 && $_REQUEST["selectModel"] == "Marcilly"){
       $toBeHashed = $reconForm.$fmdata["beg_date"]["display"].$_REQUEST["selectModel"];
    }
    else if($_REQUEST["generateImage"] == 1 && $_REQUEST["selectModel"] == "Default"){    
        $toBeHashed = $reconForm.$fmdata["beg_date"]["display"];
    }
    else if($_REQUEST["generateImage"] == 1 && $_REQUEST["selectModel"] == "Scotese"){
      $toBeHashed = $reconForm.$fmdata["beg_date"]["display"].$_REQUEST["selectModel"];
    }
    $outdirhash = md5($toBeHashed)."newest"; // md5 hashing for the output directory name 
    
    // outdirname is what pygplates should see
    if($_REQUEST["selectModel"] == "Default"){
      $outdirname = "livedata/default/$outdirhash";
    }
    else if($_REQUEST["selectModel"] == "Marcilly") {
      //$outdirname .= $_REQUEST["selectModel"].'/';
      //$outdirname .= $outdirhash;
      $outdirname = "livedata/marcilly/$outdirhash";
    }
    else if($_REQUEST["selectModel"] == "Scotese"){
      $outdirname = "livedata/scotese/$outdirhash";
    }
    // and php is running one level up:
    $outdirname_php = "pygplates/$outdirname";
    //echo $outdirname_php;
    $initial_creation_outdir = false; // did we have to make the output hash directory name?
    if (!file_exists($outdirname_php)) {
      $initial_creation_outdir = true;
      //echo "Creating a new folder!!!";
      mkdir($outdirname_php, 0777, true);
    }
    $reconfilename = "$outdirname_php/recon.geojson";
    if (!file_exists($reconfilename)) {
      file_put_contents($reconfilename, $reconForm);
    }
 }
 ?> 
 <div> 
 <?php
 // only want to make the buttons if there is valid geojson for the formation
 if(json_decode($fmdata['geojson']['display'])){
    //include('./generateRecon.php');
    include('./makeButtons.php');
 }
 ?> 
 </div>
 <?php 
//}

/*
// dispalying the button as well as what happens after the press to reconstruct button is clicked 
if($fmdata["beg_date"]["display"] && $fmdata["geojson"]["display"]){
?>
      <div class="reconstruction">
        <?php if ($_REQUEST["generateImage"] == "1") {?>
         <?php
      $timedout = false;
      if (!$initial_creation_outdir) { // we already had the folder up above, so just wait for image...
        $count=0;
        while (!file_exists("$outdirname_php/final_image.png")) { // assume another thing is making this image
          usleep(500);
          $count++;
          if ($count > 30) { // we've tried for 20 seconds, just fail it
            $timedout = true;
            break;
          }
        }
        // If we get here, image should exist, or we gave up waiting
      }
      if ($_REQUEST["selectModel"] == "Default" && ($initial_creation_outdir || $timedout)) { // if this is the first time, or we timed out waiting for image, create it:
        // Otherwise, hash doesn't exist, so we need to spawn a pygplates to make it:
          exec("cd pygplates && ./master_run_pygplates_pygmt.py ".$fmdata["beg_date"]["display"]." $outdirname", $ending);
      }
      else if($_REQUEST["selectModel"] == "Marcilly" && ($initial_creation_outdir || $timedout)) {
         exec("cd pygplates && ./MarcillyModel.py ".$fmdata["beg_date"]["display"]." $outdirname", $ending);
      }
    //     $image_encode = shell_exec("base64 my-figure_2.png"); // TODO: This is for testing purpose. Actual base64 encoding should be done by pyGMT 
      ?> <img src="<?=$outdirname_php?>/final_image.png" width ="80%" > 
         <br/>A very special thanks to the excellent <a href="https://gplates.org">GPlates</a> and their
          <a href="https://www.gplates.org/docs/pygplates/pygplates_getting_started.html">pygplates</a> software as well as
          <a href="https://www.pygmt.org/latest/">PyGMT</a> which work together to create these images.
          <br/><br/> <?php
      } else {
        // User selection of reconstruction model

?>
    
     Please select reconstruction model to view the base reconstruction of <?= $fmdata["name"]["display"] ?> 
     <form action = "" method="POST">	
        <select name="selectModel" multiple>
          <option value="Default"> GPlates Default (Meredith, Williams, et al. 2021)</option>
          <option value="Marcilly">Continental flooding model (Marcilly, Torsvik et al., 2021)</option>
	</select> 
     <input type="submit">
     </form>

          <form method="GET" action="<?=$_SERVER["REQUEST_URI"]?>&generateImage=1">
            <input id="reconstruction" style="display:none;" type="submit" value="Press to Display on a Plate Reconstruction (<?=$fmdata["name"]["display"]?>)" style="padding: 5px;" />
            <?php foreach($_REQUEST as $k => $v) {?>
              <input type="hidden" name="<?=$k?>" value="<?=$v?>" />
            <?php } ?>
            <input type="hidden" name="generateImage" value="1" />
          </form>
	<?php } 
       if(isset($_REQUEST["selectModel"])){
?>      <script>
           document.getElementById("reconstruction").style.display = "block";
        </script>
<?php  } 


?>
      </div>
<?php   
   } */
//}

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
     {
      text-indent: 2em;
      margin-bottom: 0px;
      margin-top: 0px;
    }
</style>

<?php
//var_dump(fmkeys);
if ($auth) {
?>
  <script>
    function saveText(){
        var xr = new XMLHttpRequest();
        var url = "saveNewText.php";
        idname = "\""+idname+"\"";
        var text = document.getElementById("name").innerHTML;
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
      .hasborder {
        border: 1px solid #AAAAAA;
        border-radius: 3px;
        padding: 5px;
      }
    </style>
    <div>
    <?php if ($auth) {?>
      <input id="Edit" type ="button" value = "Edit">
      <input id="Save" type="button" value="Save" disabled>
      <input id="AddNewFile" type="button" value="Add new files" disabled>
      <input id="Delete" type="button" value="Delete" name="Delete Formation" onclick = deleteform() />
    <?php } ?>
    <div>
        <b>
          <h1 id='name_value'><?=$fmdata["name"]["display"]?></h1>
        </b>
        <?php if ($auth) {?>
          <input type="file" name="title_image" id ="title_image"/>
          <input id="Addtitle" type="button" name="add_title_image" value="Add Chosen Title Image" onclick = addImageClicked('title') />
        <?php } ?>
        <div style="display: flex; flex-direction: row;">
          <?php displayImages($images, 'title') ?>
        </div>
        <hr>
    </div>
   
    <div id="period" class="horiz">
        <b>Period:&nbsp; </b>
        <div id="period_value" class="minwidth"><?=eliminateParagraphs($fmdata["period"]["display"])?></div><br>
    </div>

    <div id="age_interval" class="horiz">
        <b>Age Interval:&nbsp; </b>
        <div id="age_interval_value" class="minwidth"><?=eliminateParagraphs($fmdata["age_interval"]["display"])?></div><br>
    </div>

    <div id="province" class="horiz" >
        <b>Province:&nbsp; </b>
        <div id="province_value" class="minwidth"><?=eliminateParagraphs($fmdata["province"]["display"])?></div><br>
    </div>

    <div id="type_locality">
        <h3><b>Type Locality and Naming</b></h3>
        <div id="type_locality_value" class="minwidth"><?=$fmdata["type_locality"]["display"]?></div><br>
        <?php if ($auth) {?>
          <input type="file" name="locality_image" id ="locality_image"/>
          <input id="Addlocality" type="button" name="add_locality_image" value="Add Chosen Locality Image" onclick = "addImageClicked('locality')" />
        <?php } ?>
        <?php displayImages($images, 'locality') ?>
    </div>

    <div id="lithology">
        <h3><b>Lithology and Thickness</b></h3>
        <div id="lithology_value" class="minwidth"><?=$fmdata["lithology"]["display"]?></div><br>
        <?php if ($auth) {?>
          <input type="file" name="lithology_image" id = "lithology_image"/>
          <input id="Addlithology" type="button" name="add_lithology_image" value="Add Chosen Lithology Image" onclick="addImageClicked('lithology')" />
        <?php } ?>
        <?php displayImages($images, 'lithology') ?>
    </div>

    <div id="Lithology-pattern" class = "horiz">
        <b>Lithology Pattern:&nbsp;</b>
        <div id="lithology_pattern_value" class="minwidth"><?=eliminateParagraphs($fmdata["lithology_pattern"]["display"])?></div><br>
        <?php if ($auth) {?>
          <input type="file" name="lithology_pattern_image" id = "lithology_pattern_image"/>
          <input id="AddlithologyPattern" type="button" name="add_lithology_pattern_image" value="Add Chosen Lithology Pattern Image" onclick="addImageClicked('Lithology-pattern')" />
        <?php } ?>
        <?php displayImages($images, 'Lithology-pattern') ?>
    </div>

    <div id="relationships_distribution">
        <h3><b>Relationships and Distribution</b></h3>
        <div id="lower_contact">
            <h4><i>Lower contact</i></h4>
            <div id="lower_contact_value" class="minwidth"><?=$fmdata["lower_contact"]["display"]?></div>
            <?php if ($auth) {?>
              <input type="file" name="lowercontact_image" id = "lowercontact_image"/>
              <input id="Addlowercontact" type="button" name="add_lowercontact_image" value="Add Chosen Lower Contact Image" onclick = addImageClicked('lowercontact') />
            <?php } ?>
            <?php displayImages($images, 'lowercontact') ?>
  </div>

        <div id="upper_contact">
            <h4><i>Upper contact</i></h4>
            <div id="upper_contact_value" class="minwidth"><?=$fmdata["upper_contact"]["display"]?></div>
            <?php if ($auth) {?>
              <input type="file" name="uppercontact_image" id = "uppercontact_image"/>
              <input id="Adduppercontact" type="button" name="add_uppercontact_image" value="Add Chosen Upper Contact Image" onclick = addImageClicked('uppercontact') />
            <?php } ?>
            <?php displayImages($images, 'uppercontact') ?>
  </div>

        <div id="regional_extent">
            <h4><i>Regional extent</i></h4>
            <div id="regional_extent_value" class="minwidth"><?=$fmdata["regional_extent"]["display"]?></div><br>
            <?php if ($auth) {?>
              <input type="file" name="regionalcontact_image" id = "regionalextent_image"/>
              <input id="Addregionalextent" type="button" name="add_regionalextent_image" value="Add Chosen Regional Extent Image" onclick = addImageClicked('regionalextent') />
            <?php } ?>
            <?php displayImages($images, 'regionalextent') ?>
        </div>
    </div> 
      <div id="GeoJSON">
        <h3><b>GeoJSON</b></h3>
        <div id="geojson_value" class="minwidth"><?=$fmdata["geojson"]["display"]?></div><br>
        <?php if ($auth) {?>
          <input type="file" name="GeoJSON_image" id = "GeoJSON_image"/>
          <input id="GeoJSON" type="button" name="add_GeoJSON_image" value="Add Chosen GeoJSON Image" onclick=addImageClicked('GeoJSON') />
        <?php } ?>
        <?php displayImages($images, 'GeoJSON') ?>
    </div>

    <div id="fossils">
        <h3><b>Fossils</b></h3>
        <div id="fossils_value" class="minwidth"><?=$fmdata["fossils"]["display"]?></div><br>
        <?php if ($auth) {?>
          <input type="file" name="fossil_image" id = "fossil_image"/>
          <input id="Addfossil" type="button" name="add_fossil_image" value="Add Chosen Fossil Image" onclick="addImageClicked('fossil')" />
        <?php } ?>
        <?php displayImages($images, 'fossil') ?>
    </div>

    <!-- this fixes any dangling strong tag that happens for age -->
    </strong>
    <div id="age">
        <h3><b>Age&nbsp; </b></h3>
        <div id="age_value" class="minwidth"><?=eliminateParagraphs($fmdata["age"]["display"])?></div><br>
        <?php if ($auth) {?>
          <input type="file" name="age_image" id = "age_image"/>
          <input id="Addage" type="button" name="add_age_image" value="Add Chosen Age Image" onclick="addImageClicked('age')" />
        <?php } ?>
        <?php displayImages($images, 'age') ?>

    </div>

    <div id="age_span" class="horiz">
        <b> Age Span:&nbsp;</b>
        <div id="age_span_value" class="minwidth"><?=eliminateParagraphs($fmdata["age_span"]["display"])?></div><br>
    </div> 

    <div id="beginning_stage" class="horiz">
        <b>&nbsp;&nbsp;&nbsp;&nbsp;Beginning stage:&nbsp;</b>
        <div id="beginning_stage_value" class="minwidth"><?=eliminateParagraphs($fmdata["beginning_stage"]["display"])?></div><br>
    </div>

    <div id="frac_upB" class="horiz">
        <b>&nbsp;&nbsp;&nbsp;&nbsp;Fraction up in beginning stage:&nbsp;</b>
    <div id="frac_upB_value" class="minwidth"><?=eliminateParagraphs($fmdata["frac_upB"]["display"])?></div><br>
    </div>

    <div id="beg_date" class="horiz">
        <b>&nbsp;&nbsp;&nbsp;&nbsp;Beginning date (Ma):&nbsp;</b>
        <div id="beg_date_value" class="minwidth"><?=number_format(eliminateParagraphs($fmdata["beg_date"]["display"]), 2)?></div><br>
    </div> 

    <div id="end_stage" class="horiz">
        <b>&nbsp;&nbsp;&nbsp;&nbsp;Ending stage:&nbsp;</b>
        <div id="end_stage_value" class="minwidth"><?=eliminateParagraphs($fmdata["end_stage"]["display"])?></div><br>
    </div>

    <div id="frac_upE" class = "horiz">
        <b>&nbsp;&nbsp;&nbsp;&nbsp;Fraction up in the ending stage:&nbsp;</b>
        <div id="frac_upE_value" class="minwidth"><?=eliminateParagraphs($fmdata["frac_upE"]["display"])?></div><br>
    </div>

    <div id="end_date" class="horiz">
        <b>&nbsp;&nbsp;&nbsp;&nbsp;Ending date (Ma): &nbsp;</b>
        <div id="end_date_value" class="minwidth"><?=number_format(eliminateParagraphs($fmdata["end_date"]["display"]), 2)?></div><br>
    </div>

    <div id="depositional">
        <h3><b>Depositional setting</b></h3>
        <div id="depositional_value" class="minwidth"><?=$fmdata["depositional"]["display"]?></div><br>
        <?php if ($auth) {?>
          <input type="file" name="depositional_image" id = "depositional_image"/>
          <input id="Adddepo" type="button" name="add_depositional_image" value="Add Chosen Depositional Image" onclick="addImageClicked('depositional')" />
        <?php } ?>
        <?php displayImages($images, 'depositional') ?>
    </div> 

    <div id="depositional_pattern" class = "horiz">
        <b>Depositional pattern: &nbsp</b>
        <div id="depositional_pattern_value" class="minwidth"><?=eliminateParagraphs($fmdata["depositional_pattern"]["display"])?></div><br>
        <?php if ($auth) {?>
          <input type="file" name="Depositional-pattern_image" id = "Depositional-pattern_image"/>
          <input id="Adddepo" type="button" name="add_depositional_image" value="Add Chosen Depositional Image" onclick="addImageClicked('Depositional-pattern')" />
        <?php } ?>
        <?php displayImages($images, 'Depositional-pattern') ?>
    </div>

    <div id="additional_info">
        <h3><b>Additional Information</b></h3>
        <div id="additional_info_value" class="minwidth"><?=$fmdata["additional_info"]["display"]?></div><br>
        <?php if ($auth) {?>
          <input type="file" name="additional_image" id = "additional_image"/>
          <input id="Addaddl" type="button" name="add_additional_image" value="Add Chosen Additional Image" onclick="addImageClicked('additional')" />
        <?php } ?>
        <?php displayImages($images, 'additional') ?>
    </div>

    <div id="compiler" class = "horiz">
        <b>Compiler: &nbsp;</b>
        <div id="compiler_value" class="minwidth"><?=eliminateParagraphs($fmdata["compiler"]["display"])?></div><br>
    </div>
   </div>
<?php if ($auth) {?> 
<script type ="text/javascript">
function deleteform(){
  console.log("delete pressed");
  var title1 = document.getElementById("name_value").innerHTML;
  console.log(title1);
  newform = document.createElement('form');
  document.body.appendChild(newform);
  newform.method = "POST";
  newform.action = "deleteForm.php";
  input = document.createElement('input');
  input.type = "hidden";
  input.name = "name";
  input.value = title1;
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
      alert( val);


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
    else if(type ==="Lithology-pattern") {
            img = document.getElementById('lithology_pattern_image').files[0]
        console.log("lithology")
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
    else if (type === "GeoJSON") {
        console.log("GeoJSON");
        img = document.getElementById('GeoJSON_image').files[0]
    }
    else if (type === "depositional") {
        console.log("depositional");
        img = document.getElementById('depositional_image').files[0]
    }
    else if (type === "Depositional-pattern") {
        console.log("Depositional-pattern");
        img = document.getElementById('Depositional-pattern_image').files[0]
    }
    else if (type === "additional") {
        console.log("additional");
        img = document.getElementById('additional_image').files[0]
    } else {
      console.log('WARNING: the image type '+type+' is not recognized!');
        img = 0;
    }
    let form = new FormData();

    form.append("formation_name", "<?php echo $fmdata["name"]["display"]?>");
    form.append("image_type", type);
    form.append("image", img);

    let x = fetch('/uploadImage.php', {method: "POST", body: form })
      .then(function(res) {
      console.log('HTTP response code:', res.text().then(function(a){alert(a)}));
  
  //alert("Image Upload complete,to see image, reload the page");
      }).catch(function(e) {
      console.log("Error uploading image: ", e);
    });
  // location.reload()
}

        var editBtn = document.getElementById('Edit');
        var saveBtn = document.getElementById('Save');
        var fmdata = <?=json_encode($fmdata)?>;

        var fmkeys = Object.keys(fmdata);
  console.log(fmkeys);       
  var editables = document.querySelectorAll(
          fmkeys.map(function(k) { return '#'+k+'_value'; }).join(', ') // "#name", "type_locality", ...
  );
  editBtn.addEventListener('click',function(e){
    if(!editables[0].isContentEditable){
                saveBtn.disabled = false;
                for (var i = 0;i<editables.length;i++){
                    editables[i].contentEditable = true;
                    editables[i].classList.add('hasborder');
        console.log(editables[i]);
        // Fill editable box with the raw text for editing
                    editables[i].innerHTML = fmdata[fmkeys[i]].raw
                }
            }
            else{
                saveBtn.disabled = true;
                for (var i = 0;i<editables.length;i++){
                    editables[i].contentEditable = false;
                    if (editables[i].innerHTML === fmdata[fmkeys[i]].raw) {
                      // they didn't change it, so set it back to the display version
                      editables[i].innerHTML = fmdata[fmkeys[i]].display;
                    }
                }
            }
        });

        saveBtn.addEventListener('click',function(e){
            var savedata = {};
            for (let i = 0; i<editables.length; i++) {
              savedata[fmkeys[i]] = editables[i].innerHTML;
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
            console.log('savedata = ', savedata);
            form.submit();
            document.removeChild(form);
        });

</script>
<?php } ?>
<?php
include("footer.php");
?>
