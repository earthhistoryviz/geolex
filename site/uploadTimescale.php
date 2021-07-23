<?php
error_reporting(E_ALL);
include_once("navBar.php");
include_once("SearchBar.php");
include_once("SqlConnection.php");
include_once("TimescaleLib.php");

if (!$_SESSION["loggedIn"]) {
  echo "ERROR: You must be logged in to access this page.";
  exit(0);
}

// If they submitted the upload form, move the uploaded file to the list of timescales
$msgs = array();
if (isset($_REQUEST["submit_upload"])) {
  try {
    $uploads_dir ='  /uploads'.$_FILES['upfile']['tmp_name'];
    // Undefined | Multiple Files | $_FILES Corruption Attack
    // If this request falls under any of them, treat it invalid.
    if (!isset($_FILES['upfile']['error']) || is_array($_FILES['upfile']['error'])) {
      throw new RuntimeException('Invalid parameters.');
    }

    // Check $_FILES['upfile']['error'] value.
    switch ($_FILES['upfile']['error']) {
      case UPLOAD_ERR_OK: break;
      case UPLOAD_ERR_NO_FILE: throw new RuntimeException('No file sent.'); break;
      case UPLOAD_ERR_INI_SIZE:
      case UPLOAD_ERR_FORM_SIZE:
        throw new RuntimeException('Exceeded filesize limit.');
      default:
        throw new RuntimeException('Unknown errors.');
    }
    if ($_FILES['upfile']['size'] > 1000000) {
      throw new RuntimeException('Exceeded filesize limit.');
    }

    // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
    // Check MIME Type by yourself.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if (false === $ext = array_search($finfo->file($_FILES['upfile']['tmp_name']),array(
      'xlsx'=>"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    ),true )) {
      throw new RuntimeException('Invalid file format, must be xlsx file.');
    }

    // First try to read the file, if it parses correctly, move it to be the new timescale
    $timescale = parseTimescale($_FILES['upfile']['tmp_name']);

    // If timescale successfully parses, let's go ahead and save the excel file

    $filename = preg_replace('/[^A-Za-z0-9_.\-]/', '_', $_FILES['upfile']['name']);
    if (move_uploaded_file($_FILES['upfile']['tmp_name'], 'timescales/'.$filename)) {
      array_push($msgs, "File uploaded successfully");
      // The file is successfully there, move on to creating the default.xlsx file
      //echo "File uploaded and moved, next up is updating database calculations";
    } else {
      throw new RuntimeException("File failed to upload.");
    }

    //$newname = sha1_file($_FILES['upfile']['tmp_name']);
  } catch (RuntimeException $e) {
    array_push($msgs, $e->getMessage());
  }
  // Set this as the filename for the next step that will set the default
  $_REQUEST["existing_timescale"] = $filename;

// Form to upload the new time scale:
}

// Both forms (upload and existing) will end up with this variable set 
// So we can just set the default and process regardless of whether upload happened first
if (isset($_REQUEST["existing_timescale"])) {
  try {
    $f = $_REQUEST["existing_timescale"];
    $p = dirname(__FILE__) . "/timescales/$f";
    if (!file_exists($p)) {
      throw new RuntimeException("Selected timescale file $p does not exist");
    } else {
      array_push($msgs, 'Existing timescale found');
    }
    // Set this timescale as the default
    if (!copy($p, $DEFAULT_TIMESCALE_PATH)) {
      throw new RuntimeException("Failed to set $f as default timescale");
    } else {
      array_push($msgs, "Successfully set $f as default timescale");
    }
    // Load the default timescale
    $timescale = parseDefaultTimescale();
    if (!$timescale) {
      throw new RuntimeException("Failed to parse the default timescale.");
    } else {
      array_push($msgs, "Successfully parsed default timescale");
    }
    // Update all formations with this timescale computation
    $formations = getAllFormations();
    $failures = 0;
    foreach($formations as $form) {
      $form["beginning_stage"] = cleanupString($form["beginning_stage"]);
      $form["end_stage"] = cleanupString($form["end_stage"]);
      $form["frac_upB"] = cleanupString($form["frac_upB"]);
      $form["frac_upE"] = cleanupString($form["frac_upE"]);
      $newbase = computeAgeFromPercentUp($form["beginning_stage"], $form["frac_upB"], $timescale, true);
      $newtop = computeAgeFromPercentUp($form["end_stage"], $form["frac_upE"], $timescale, true);
      if ($newbase !== false && $newtop !== false) {
        if (!updateFormationAges($form["name"], $newtop, $newbase)) {
          $failures++;
          array_push($msgs, "Could not update dates for formation ".$form["name"]
            . " using new base (".$form["frac_upB"]."% up in ".cleanupString($form["beginning_stage"])." = $newbase) and "
            . " using new top (".$form["frac_upE"]."% up in ".cleanupString($form["end_stage"])." = $newtop)");
        } else {
          array_push($msgs, "Successfully updated formation ".$form["name"]
            . " to new base (".$form["frac_upB"]."% up in ".cleanupString($form["beginning_stage"])." = $newbase) and "
            . " to new top (".$form["frac_upE"]."% up in ".cleanupString($form["end_stage"])." = $newtop)");
        }
      } else {
        $failures++;
        array_push($msgs, "Could not compute dates for formation ".$form["name"]
          . " using new base (".$form["frac_upB"]."% up in ".cleanupString($form["beginning_stage"])." = $newbase) and "
          . " using new top (".$form["frac_upE"]."% up in ".cleanupString($form["end_stage"])." = $newtop)");
      }
    }
    if ($failures > 0) {
      throw new RuntimeException("Failed to update formation ages for $failures formations!");
    }
  } catch (RuntimeException $e) {
    array_push($msgs, $e->getMessage());
  }
}

// Compute list of all current timescale files:
$files = glob("timescales/*.xlsx");
for($i=0; $i<count($files); $i++) { // remove the "timescales/" part
  $files[$i] = preg_replace("/^timescales\//", "", $files[$i]);
}

?><!DOCTYPE html>
<html>
  <body>
    <?php if ($msgs && count($msgs) > 0) {
      ?><hr/><?php
      foreach($msgs as $m) {
        ?><div class="error"><?=$m?></div><?php
      }
    }?>
    <hr/>
    <form action="<?=$_SERVER["PHP_SELF"]?>" method="post" enctype="multipart/form-data">
      Set default timescale and recompute times:<br/>
      <select name="existing_timescale">
        <?php foreach($files as $tf) {
          ?><option value="<?=$tf?>"><?=$tf?></option><?php
        }?>
      </select>
      <input type="submit" name="submit_existing" value="Recompute" />
    </form>
    <hr/>
    <form action="<?=$_SERVER["PHP_SELF"]?>" method="post" enctype="multipart/form-data">
      Or upload new XLSX with timescale:
      <input type="file" name="upfile" id="upfile">
      <input type="submit" value="Upload Excel Timescale" name="submit_upload">
    </form>
    <hr/>
    You can download the existing timescales to see what they are:<br/><br/>
    <?php foreach($files as $tf) {?>
      <a href="timescales/<?=$tf?>"><?=$tf?></a><br/><br/>
    <?php } ?>
  </body>
</html>

