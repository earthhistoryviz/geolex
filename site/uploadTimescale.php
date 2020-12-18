<?php
error_reporting(E_ALL);
include("navBar.php");
include("SearchBar.php");
include("SqlConnection.php");
include("parseTimescale.php");

// If they submitted the form, process file and update database
if (isset($_REQUEST["submit"])) {

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

    echo "We have the stages, they are: <pre>"; print_r($timescale);
    $filename = preg_replace('/[^A-Za-z0-9_.\-]/', '_', $_FILES['upfile']['name']);
    if (move_uploaded_file($_FILES['upfile']['tmp_name'], 'timescales/'.$filename)) {
      echo "File uploaded and moved, next up is updating database calculations";
    } else {
      echo "File failed to upload.";
    }

    //$newname = sha1_file($_FILES['upfile']['tmp_name']);
  } catch (RuntimeException $e) {
    echo $e->getMessage();
  }

// Form to upload the new time scale:
} else { ?>
  <!DOCTYPE html>
  <html>
    <body>
      <hr/>
      <form action="<?=$_SERVER["PHP_SELF"]?>" method="post" enctype="multipart/form-data">

<br/>
<br/>
NOTE: left off where this page should be the "Compute Timescale" page, and it allows you
to either upload a new timescale (And recalculate), or select an existing one and recalculate.
On upload, that one is set to the "Default" which will be used for the word doc upload.
Then, alter the word doc upload to do the computations as well.
<br/>
<br/>
        Select New Timescale XLSX to upload:
        <input type="file" name="upfile" id="upfile">
        <input type="submit" value="Upload Excel Timescale" name="submit">
      </form>
    </body>
  </html>
<?php } ?>

