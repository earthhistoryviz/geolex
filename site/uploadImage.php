<?php
include ("docx_read.php");
try {
    $uploads_dir ='  /imguploads'.$_FILES['upfile']['tmp_name'];
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

// Check MIME Type by yourself.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if (false === $ext = array_search(
            $finfo->file($_FILES['upfile']['tmp_name']),
            array(
                'jpeg' => "image/jpeg",
                'png'=>"image/png"
            ),
            true
        )) {
        throw new RuntimeException('Invalid file format.');
    }


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