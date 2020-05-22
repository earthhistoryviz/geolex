<?php
error_reporting(E_ALL);
include ("docx_read.php");
try {
    $uploads_dir ='  /uploads'.$_FILES['upfile']['tmp_name'];
// Undefined | Multiple Files | $_FILES Corruption Attack
// If this request falls under any of them, treat it invalid.
if (
!isset($_FILES['upfile']['error']) ||
is_array($_FILES['upfile']['error'])
) {
throw new RuntimeException('Invalid parameters.');
}

// Check $_FILES['upfile']['error'] value.
switch ($_FILES['upfile']['error']) {
case UPLOAD_ERR_OK:
break;
case UPLOAD_ERR_NO_FILE:
throw new RuntimeException('No file sent.');
case UPLOAD_ERR_INI_SIZE:
case UPLOAD_ERR_FORM_SIZE:
throw new RuntimeException('Exceeded filesize limit.');
default:
throw new RuntimeException('Unknown errors.');
}

// You should also check filesize here.
if ($_FILES['upfile']['size'] > 1000000) {
throw new RuntimeException('Exceeded filesize limit.');
}

// DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
// Check MIME Type by yourself.
$finfo = new finfo(FILEINFO_MIME_TYPE);
if (false === $ext = array_search(
$finfo->file($_FILES['upfile']['tmp_name']),
array(
'doc' => "application/msword",
'docx'=>"application/vnd.openxmlformats-officedocument.wordprocessingml.document"
),
true
)) {
throw new RuntimeException('Invalid file format.');
}

// You should name it uniquely.
// DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
// On this example, obtain safe unique name from its binary data
docx_read($_FILES['upfile']['tmp_name']);
$newname = sha1_file($_FILES['upfile']['tmp_name']);
/*echo $_FILES['upfile']['tmp_name'];
if (!copy($newname, $uploads_dir)){
throw new RuntimeException('Failed to move uploaded file.');
}

echo 'File is uploaded successfully.';
*/
} catch (RuntimeException $e) {

echo $e->getMessage();

}
?>
