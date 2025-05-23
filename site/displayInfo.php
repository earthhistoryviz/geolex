<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
include_once("navBar.php");
$formaction = "/index.php";
$isFixedRegion = true;
include_once("generalSearchBar.php");
include_once("SqlConnection.php");
include_once("./makeReconstruction.php"); // has createGeoJSONForFormations
include_once("SimpleXLSX.php");

$formation = $_GET["formation"];
$macrostrat = false;
if (isset($_GET["macrostrat"]) && $_GET["macrostrat"] == "true") {
    $macrostrat = true;
    $col_id = $_GET["col_id"];
}

if ($formation == "") {?>
  <title>Empty Search</title>
  <h3 style="text-align: center;">Please type in the search box and click "Submit" to search for Formations<br>Click on "View All Formations" to view the list of all Formations</h3> <?php
  include("footer.php");
    exit(0);
}

$formationName = str_replace('Fm', 'Formation', $formation);
$imagedisplaycount = 0; ?>
<title><?=$formationName?></title> <?php


include_once("formationInfo.php");

//-----------------------------------------------------------
// Start outputting the page
//-----------------------------------------------------------

/* ---------- DEBUGGING ---------- */

/* ---------- DEBUGGING ---------- */

if (!$found) { ?>
  <title>No Match</title>
  <h3>Nothing found for "<?=$formation?>". Please search again.</h3> <?php
  include("footer.php");
    exit(0);
}

$name = $fmdata["name"]["display"];
// Fetch any image filenames for this formation from the disk
$dirs = scandir("./uploads/$name");
$images = array();
if ($dirs) {
    foreach ($dirs as $type) {
        if (preg_match("/^\./", $type)) {
            continue;
        }
        $files = scandir("./uploads/$name/$type");
        if ($files) {
            foreach ($files as $f) {
                if (preg_match("/^\./", $f)) {
                    continue;
                }
                if (preg_match("/^thumb_/", $f)) {
                    continue;
                }
                if (!$images[$type]) {
                    $images[$type] = array();
                }
                array_push($images[$type], array(
                  "full" => "/uploads/$name/$type/$f",
                  "thumbnail" => "uploads/$name/$type/$f",
                ));
            }
        }
    }
}

$geojson = createGeoJSONForFormations(array(
  array(
    "geojson" => strip_tags($fmdata["geojson"]["display"]),
    "name" => strip_tags($fmdata["name"]["display"]),
    "lithology_pattern" => strip_tags($fmdata["lithology_pattern"]["display"])
  )
));

// create output directory for json file to be processed by pygplates
// (each output directory corresponds to a different formation that is clicked and has a beginning date and geoJSON info to reconstruct from)

if ($_REQUEST["generateImage"]) {
    $toBeHashed = $fmdata["beg_date"]["display"]. $geojson. $_REQUEST["formation"];
    $outdirhash = md5($toBeHashed); // md5 hashing for the output directory name
    switch ($_REQUEST["selectModel"]) {
        case  "Default": $outdirname = "livedata/default/$outdirhash";
            break;
        case "Marcilly": $outdirname = "livedata/marcilly/$outdirhash";
            break;
        case  "Scotese": $outdirname = "livedata/scotese/$outdirhash";
            break;
        default:         $outdirname = "livedata/unknown/$outdirhash";
    }

    // outdirname is what pygplates should see
    // and php is running one level up:
    $outdirname_php = "pygplates/$outdirname";
    $initial_creation_outdir = false; // did we have to make the output hash directory name?
    if (!file_exists($outdirname_php)) {
        $initial_creation_outdir = true;
        mkdir($outdirname_php, 0777, true);
    }

    $reconfilename = "$outdirname_php/recon.geojson";
    if (!file_exists($reconfilename)) {
        file_put_contents($reconfilename, $geojson);
    };
} ?>

<div class="reconstruction"> <?php
  // only want to make the buttons if there is valid geojson for the formation
  if (json_decode($fmdata["geojson"]["display"])) {
      include("./makeButtons.php");
      ?>
  <div class="buttonContainer">
    <button id="generateAllImagesBtn">Generate All Models</button>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      let generateAllImagesBtn = document.getElementById('generateAllImagesBtn');
      generateAllImagesBtn.addEventListener('click', function() {
        let data = {
          geojson: <?= json_encode($geojson) ?>,
          beg_date: <?= json_encode($fmdata["beg_date"]["display"]) ?>,
          formation: <?= json_encode($_REQUEST["formation"]) ?>
        };
        var xhr = new XMLHttpRequest();
        xhr.open("POST", '/addGeojsonToFiles.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        let outdirhash = "";
        xhr.onreadystatechange = function() {
          if (this.readyState === XMLHttpRequest.DONE) {
            if (this.status === 200) {
              outdirhash = this.responseText;
              const begDateDisplay = data["beg_date"];
              const url = '/generateAllImages.php?beg_date=' 
              + encodeURIComponent(begDateDisplay) 
              + '&outdirhash=' + encodeURIComponent(outdirhash);
              window.open(url, '_blank');
            } else {
              console.error('Error writing file:', this.responseText);
            }
          }
        };
        xhr.send(JSON.stringify(data));
      });
    });
  </script> <?php
  }
?>
</div>

<?php // display information below?>
<style>
  [contenteditable="true"] {
    font-family: "Rajdhani";
    color: #C00;
  }
  button:hover {
    background-color: #e67603;
  }
  .buttonContainer {
    display: flex;
    justify-content: center;
    align-items: center;
  }
  #generateAllImagesBtn {
    padding: 10px 20px;
    background-color: #e67603;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
  }
  #Save {
    height: 40px;
    border: 3px solid #000000;
    min-width: 60px;
    font-weight: bold;
    border-radius: 5px;
    padding-left: 20px;
    padding-right: 20px;
  }
  #Delete {
    height: 40px;
    border: 3px solid #000000;
  }
  #AddNewFile {
    height: 40px;
    border: 3px solid #000000;
    font-weight: bold;
    border-radius: 5px;
    min-width: 60px;
    padding-left: 20px;
    padding-right: 20px;
  }
  #Edit {
    height: 40px;
    border: 3px solid #000000;
    min-width: 60px;
    font-weight: bold;
    border-radius: 5px;
    padding-left: 20px;
    padding-right: 20px;
  }

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

<div style="font-family: 'Times New Roman', Times, Arial, serif;"> <?php
if ($auth) { ?>
    <input id="Edit" type ="button" value = "Edit">
    <input id="Save" type="button" value="Save" disabled>
    <input id="AddNewFile" type="button" value="Add new files" disabled>
    <input id="Delete" type="button" value="Delete" name="Delete Formation" onclick="deleteform()"/> <?php
} ?>

  <div>
    <h1 id="name_value">
      <b><?=$fmdata["name"]["display"]?></b>
    </h1> <?php
  if ($auth) { ?>
      <input type="file" name="title_image" id="title_image"/>
      <input id="Addtitle" type="button" name="add_title_image" value="Add Chosen Title Image" onclick="addImageClicked('title')"/> <?php
  } ?>
    <div style="display: flex; flex-direction: row;"> <?php
    displayImages($images, "title"); ?>
    </div>
    <hr>
  </div>

  <div id="period" class="horiz">
    <b>Period:&nbsp;</b>
    <div id="period_value" class="minwidth">
      <?=eliminateParagraphs($fmdata["period"]["display"]) ?>
    </div>
    <br>
  </div>

  <div id="age_interval" class="horiz">
    <b>Age Interval:&nbsp;</b>
    <div id="age_interval_value" class="minwidth">
      <?=eliminateParagraphs($fmdata["age_interval"]["display"]) ?>
    </div>
    <br>
  </div>

  <div id="province" class="horiz">
    <b>Province:&nbsp;</b>
    <div id="province_value" class="minwidth">
      <?=eliminateParagraphs($fmdata["province"]["display"]) ?>
    </div>
    <br>
  </div>

  <div id="type_locality">
    <h3><b>Type Locality and Naming</b></h3>
    <div id="type_locality_value" class="minwidth">
      <?=$fmdata["type_locality"]["display"] ?>
    </div>
    <br> <?php
  if ($auth) { ?>
      <input type="file" name="locality_image" id="locality_image"/>
      <input id="Addlocality" type="button" name="add_locality_image" value="Add Chosen Locality Image" onclick="addImageClicked('locality')"/> <?php
  }
displayImages($images, "locality"); ?>
  </div>

  <div id="lithology">
    <h3><b>Lithology and Thickness</b></h3>
    <div id="lithology_value" class="minwidth">
      <?=$fmdata["lithology"]["display"] ?>
    </div>
    <br> <?php
if ($auth) { ?>
      <input type="file" name="lithology_image" id="lithology_image"/>
      <input id="Addlithology" type="button" name="add_lithology_image" value="Add Chosen Lithology Image" onclick="addImageClicked('lithology')"/> <?php
}
displayImages($images, "lithology") ?>
  </div>

  <div id="Lithology-pattern" class="horiz">
    <b>Lithology Pattern:&nbsp;</b>
    <div id="lithology_pattern_value" class="minwidth">
      <?=eliminateParagraphs($fmdata["lithology_pattern"]["display"]) ?>
    </div>
    <br> <?php
if ($auth) { ?>
      <input type="file" name="lithology_pattern_image" id="lithology_pattern_image"/>
      <input id="AddlithologyPattern" type="button" name="add_lithology_pattern_image" value="Add Chosen Lithology Pattern Image" onclick="addImageClicked('Lithology-pattern')"/> <?php
}
displayImages($images, "Lithology-pattern"); ?>
  </div>
  </strong>
  <div id="relationships_distribution">
    <h3><b>Relationships and Distribution</b></h3>
    <div id="lower_contact">
      <h4 style="font-weight: bold"><i>Lower contact</i></h4>
      <div id="lower_contact_value" class="minwidth">
        <?=$fmdata["lower_contact"]["display"] ?>
      </div> <?php
  if ($auth) { ?>
        <input type="file" name="lowercontact_image" id="lowercontact_image"/>
        <input id="Addlowercontact" type="button" name="add_lowercontact_image" value="Add Chosen Lower Contact Image" onclick="addImageClicked('lowercontact')"/> <?php
  }
displayImages($images, "lowercontact"); ?>
    </div>

    <div id="upper_contact">
      <h4 style="font-weight: bold"><i>Upper contact</i></h4>
      <div id="upper_contact_value" class="minwidth">
        <?=$fmdata["upper_contact"]["display"] ?>
      </div> <?php
if ($auth) { ?>
        <input type="file" name="uppercontact_image" id="uppercontact_image"/>
        <input id="Adduppercontact" type="button" name="add_uppercontact_image" value="Add Chosen Upper Contact Image" onclick="addImageClicked('uppercontact')"/> <?php
}
displayImages($images, "uppercontact"); ?>
    </div>

    <div id="regional_extent">
      <h4 style="font-weight: bold"><i>Regional extent</i></h4>
      <div id="regional_extent_value" class="minwidth">
        <?=$fmdata["regional_extent"]["display"] ?>
      </div>
      <br> <?php
if ($auth) { ?>
        <input type="file" name="regionalcontact_image" id="regionalextent_image"/>
        <input id="Addregionalextent" type="button" name="add_regionalextent_image" value="Add Chosen Regional Extent Image" onclick="addImageClicked('regionalextent')"/> <?php
}
displayImages($images, "regionalextent"); ?>
    </div>
  </div>

  <div id="GeoJSON">
    <h3><b>GeoJSON</b></h3>
    <div id="geojson_value" class="minwidth">
      <?=$fmdata["geojson"]["display"] ?>
    </div>
    <br> <?php
    if ($auth) { ?>
      <input type="file" name="GeoJSON_image" id="GeoJSON_image"/>
      <input id="GeoJSON" type="button" name="add_GeoJSON_image" value="Add Chosen GeoJSON Image" onclick="addImageClicked('GeoJSON')"/> <?php
    }
displayImages($images, "GeoJSON"); ?>
  </div>

  <div id="fossils">
    <h3><b>Fossils</b></h3>
    <div id="fossils_value" class="minwidth">
      <?php
    $fossilString = $fmdata["fossils"]["display"];
$xlsx = SimpleXLSX::parse("Treatise_FossilGenera-URL_lookup.xlsx");
if ($xlsx === false) {
    echo $fossilString;
} else {
    $rows = $xlsx->rows(0);
    $links = [];
    foreach ($rows as $row) {
        if (isset($row[0]) && isset($row[1])) {
            $name = trim($row[0]);
            $link = trim($row[1]);
            if (!empty($name)) {
                $links[$name] = $link;
            }
        }
    }
    preg_match_all('/<em>(.*?)<\/em>/', $fossilString, $matches);
    $italicizedWords = $matches[1];
    foreach ($italicizedWords as $italicizedWord) {
        $charactersToRemove = array('.', ',');
        $italicizedWord = str_replace($charactersToRemove, '', $italicizedWord);
        $italicizedWordFirst = explode(' ', $italicizedWord)[0];
        if (isset($links[$italicizedWordFirst])) {
            $link = '<em> <a href="' . htmlspecialchars($links[$italicizedWordFirst]) . '" target="_blank">' . htmlspecialchars($italicizedWord) . '</a> </em>';
            $fossilString = str_replace('<em>' . $italicizedWord . '</em>', $link, $fossilString);
        }
    }
    echo $fossilString;
}
?>
    </div>
    <br> <?php
    if ($auth) { ?>
      <input type="file" name="fossil_image" id="fossil_image"/>
      <input id="Addfossil" type="button" name="add_fossil_image" value="Add Chosen Fossil Image" onclick="addImageClicked('fossil')"/> <?php
    }
displayImages($images, "fossil"); ?>
  </div>

  </strong> <!-- this fixes any dangling strong tag that happens for age -->
  <div id="age">
    <h3><b>Age&nbsp;</b></h3>
    <div id="age_value" class="minwidth">
      <?=eliminateParagraphs($fmdata["age"]["display"]) ?>
    </div>
    <br> <?php
if ($auth) { ?>
      <input type="file" name="age_image" id="age_image"/>
      <input id="Addage" type="button" name="add_age_image" value="Add Chosen Age Image" onclick="addImageClicked('age')"/> <?php
}
displayImages($images, "age"); ?>
  </div>

  <div id="age_span" class="horiz">
    <b> Age Span:&nbsp;</b>
    <div id="age_span_value" class="minwidth">
      <?=eliminateParagraphs($fmdata["age_span"]["display"]) ?>
    </div>
    <br>
  </div>

  <div id="beginning_stage" class="horiz">
    <b>&nbsp;&nbsp;&nbsp;&nbsp;Beginning stage:&nbsp;</b>
    <div id="beginning_stage_value" class="minwidth">
      <?=eliminateParagraphs($fmdata["beginning_stage"]["display"]) ?>
    </div>
    <br>
  </div>

  <div id="frac_upB" class="horiz">
    <b>&nbsp;&nbsp;&nbsp;&nbsp;Fraction up in beginning stage:&nbsp;</b>
    <div id="frac_upB_value" class="minwidth">
      <?=eliminateParagraphs($fmdata["frac_upB"]["display"]) ?>
    </div>
    <br>
  </div>

  <div id="beg_date" class="horiz">
    <b>&nbsp;&nbsp;&nbsp;&nbsp;Beginning date (Ma):&nbsp;</b>
    <div id="beg_date_value" class="minwidth">
      <?=number_format(str_replace(",", "", eliminateParagraphs($fmdata["beg_date"]["display"])), 2) ?>
    </div>
    <br>
  </div>

  <div id="end_stage" class="horiz">
    <b>&nbsp;&nbsp;&nbsp;&nbsp;Ending stage:&nbsp;</b>
    <div id="end_stage_value" class="minwidth">
      <?=eliminateParagraphs($fmdata["end_stage"]["display"]) ?>
    </div>
    <br>
  </div>

  <div id="frac_upE" class="horiz">
    <b>&nbsp;&nbsp;&nbsp;&nbsp;Fraction up in the ending stage:&nbsp;</b>
    <div id="frac_upE_value" class="minwidth">
      <?=eliminateParagraphs($fmdata["frac_upE"]["display"]) ?>
    </div>
    <br>
  </div>

  <div id="end_date" class="horiz">
    <b>&nbsp;&nbsp;&nbsp;&nbsp;Ending date (Ma): &nbsp;</b>
    <div id="end_date_value" class="minwidth">
      <?=number_format(str_replace(",", "", eliminateParagraphs($fmdata["end_date"]["display"])), 2) ?>
    </div>
    <br>
  </div>

  <div id="depositional">
    <h3><b>Depositional setting</b></h3>
    <div id="depositional_value" class="minwidth">
      <?=$fmdata["depositional"]["display"] ?>
    </div>
    <br> <?php
if ($auth) { ?>
      <input type="file" name="depositional_image" id="depositional_image"/>
      <input id="Adddepo" type="button" name="add_depositional_image" value="Add Chosen Depositional Image" onclick="addImageClicked('depositional')"/> <?php
}
displayImages($images, "depositional"); ?>
  </div>

  <div id="depositional_pattern" class="horiz">
    <b>Depositional pattern: &nbsp</b>
    <div id="depositional_pattern_value" class="minwidth">
      <?=eliminateParagraphs($fmdata["depositional_pattern"]["display"]) ?>
    </div>
    <br> <?php
if ($auth) { ?>
      <input type="file" name="Depositional-pattern_image" id="Depositional-pattern_image"/>
      <input id="Adddepo" type="button" name="add_depositional_image" value="Add Chosen Depositional Image" onclick="addImageClicked('Depositional-pattern')"/> <?php
}
displayImages($images, "Depositional-pattern"); ?>
  </div>

  <div id="additional_info">
    <h3><b>Additional Information</b></h3>
    <div id="additional_info_value" class="minwidth">
      <?=$fmdata["additional_info"]["display"] ?>
    </div>
    <br> <?php
if ($auth) { ?>
      <input type="file" name="additional_image" id="additional_image"/>
      <input id="Addaddl" type="button" name="add_additional_image" value="Add Chosen Additional Image" onclick="addImageClicked('additional')"/> <?php
}
displayImages($images, "additional"); ?>
  </div>

  <div id="compiler" class="horiz">
    <b>Compiler: &nbsp;</b>
    <div id="compiler_value" class="minwidth">
      <?=eliminateParagraphs($fmdata["compiler"]["display"]) ?>
    </div>
    <br>
  </div>
</div> <?php

if ($auth) { ?>
  <script type="text/javascript">
    function deleteform() {
      console.log("delete pressed");
      var toDeleteHTML = document.getElementById("name_value");
      var toDeleteText = toDeleteHTML.innerText || toDeleteHTML.textContent;
      console.log(toDeleteText);

      newform = document.createElement("form");
      document.body.appendChild(newform);
      newform.method = "POST";
      newform.action = "/deleteForm.php";

      var input = document.createElement("input");
      input.type = "hidden";
      input.name = "name";
      input.value = toDeleteText;

      newform.appendChild(input);
      newform.submit();
      // document.removeChild(newform);
    }

    function delImageClicked(path, type, id) {
      console.log("delImageClicked: asked to delete image at path ", path);
      const fullpath = "/app" + path;
      let form = new FormData();
      form.append("Img_select", fullpath);

      let x = fetch("/delete_image.php", {method: "POST", body: form}).then(function(res) {
        res.text().then(function(val) {
          alert(val);

          img = document.getElementById(id);
          img.parentNode.removeChild(img);
        });
      }).catch(function(e) {
        console.log("Error deleting image:", e);
      });
    }

    function addImageClicked(type) {
      let img;
      if (type === "lithology") {
        console.log("lithology");
        img = document.getElementById("lithology_image").files[0];
      } else if (type === "title") {
        console.log("title");
        img = document.getElementById("title_image").files[0];
      } else if (type === "fossil") {
        console.log("fossil");
        img = document.getElementById("fossil_image").files[0];
      } else if (type ==="locality") {
        console.log("locality");
        img = document.getElementById("locality_image").files[0];
      } else if (type ==="Lithology-pattern") {
        console.log("lithology");
        img = document.getElementById("lithology_pattern_image").files[0];
      } else if (type === "lowercontact") {
        console.log("lowercontact");
        img = document.getElementById("lowercontact_image").files[0];
      } else if (type === "uppercontact") {
        console.log("uppercontact");
        img = document.getElementById("uppercontact_image").files[0];
      } else if (type === "regionalextent") {
        console.log("regionalextent");
        img = document.getElementById("regionalextent_image").files[0];
      } else if (type === "GeoJSON") {
        console.log("GeoJSON");
        img = document.getElementById("GeoJSON_image").files[0];
      } else if (type === "depositional") {
        console.log("depositional");
        img = document.getElementById("depositional_image").files[0];
      } else if (type === "Depositional-pattern") {
        console.log("Depositional-pattern");
        img = document.getElementById("Depositional-pattern_image").files[0];
      } else if (type === "additional") {
        console.log("additional");
        img = document.getElementById("additional_image").files[0];
      } else {
        console.log("WARNING: the image type '+type+' is not recognized!");
        img = 0;
      }

      let form = new FormData();
      form.append("formation_name", "<?php echo $fmdata["name"]["display"]?>");
      form.append("image_type", type);
      form.append("image", img);

      let x = fetch("/uploadImage.php", {method: "POST", body: form}).then(function(res) {
        console.log("HTTP response code:", res.text().then(function(a) {
          alert(a);
        }));
      // alert("Image Upload complete,to see image, reload the page");
      }).catch(function(e) {
        console.log("Error uploading image: ", e);
      });
      // location.reload()
    }

    var editBtn = document.getElementById("Edit");
    var saveBtn = document.getElementById("Save");
    var fmdata = <?=json_encode($fmdata) ?>;
    var fmkeys = Object.keys(fmdata);
    console.log(fmkeys);
    var editables = document.querySelectorAll(
      fmkeys.map(function(k) {
        return "#" + k + "_value";
      }).join(", ") // "#name", "type_locality", ...
    );
    editBtn.addEventListener("click", function(e) {
      if (!editables[0].isContentEditable) {
        saveBtn.disabled = false;
        for (var i = 0;i<editables.length;i++) {
          editables[i].contentEditable = true;
          editables[i].classList.add("hasborder");
          console.log(editables[i]);
          // Fill editable box with the raw text for editing
          editables[i].innerHTML = fmdata[fmkeys[i]].raw
        }
      } else {
        saveBtn.disabled = true;
        for (var i = 0; i < editables.length; i++) {
          editables[i].contentEditable = false;
          if (editables[i].innerHTML === fmdata[fmkeys[i]].raw) {
            // they didn't change it, so set it back to the display version
            editables[i].innerHTML = fmdata[fmkeys[i]].display;
          }
        }
      }
    });
    saveBtn.addEventListener("click",function(e) {
      var savedata = {};
      for (let i = 0; i < editables.length; i++) {
        savedata[fmkeys[i]] = editables[i].innerHTML;
      }
      form = document.createElement("form");
      document.body.appendChild(form);
      form.method = "POST";
      form.action = "/saveData.php";
      var length = Object.keys(savedata).length;
      
      // var originalNameInput = document.createElement("input");
      // originalNameInput.type = "hidden";
      // originalNameInput.name = "originalName";
      // originalNameInput.value = new URLSearchParams(window.location.search).get('formation');
      //form.appendChild(originalNameInput);
      for (var data in savedata) {
        var input = document.createElement("input");
        input.type = "hidden";
        input.name = data;
        input.value = savedata[data];
        form.appendChild(input);
      }
      console.log("savedata = ", savedata);
      form.submit();
      document.removeChild(form);
    });
  </script> <?php
}

include("footer.php"); ?>

<?php

function displayImages($images, $imtype)
{
    global $fmdata;
    global $auth;

    foreach ($images[$imtype] as $i) {
        $id = "image_".$imtype."_".$imagedisplaycount; ?>
    <div id="<?php echo $fmdata["name"]["display"]; ?>">
      <a href="<?php echo $i["full"]; ?>" target="_blank">
        <img src="/<?php echo $i["thumbnail"]; ?>" style="max-width: 200px; max-height: 200px;"/>
      </a>
      <?php if ($auth) { ?>
        <input
          id="<?php echo $i["full"]; ?>"
          type="button"
          value="Delete"
          onclick="delImageClicked('<?php echo $i['full']; ?>', '<?php echo $imtype; ?>', '<?php echo $fmdata['name']['display']; ?>')"
        /> <?php
      } ?>
    </div> <?php
    }
}

function eliminateParagraphs($str)
{
    while (preg_match("/<p>.*<\/p>/", $str)) {
        $str = preg_replace("/<p>(.*)<\/p>/", "\\1", $str);
    }
    return $str;
}

?>