<?php
error_reporting(E_ALL);
$target_dir = "uploads/";
$type = $_POST['image_type'];
$formname = $_POST['formation_name'];
ini_set('upload_max_filesize','10M');
ini_set('post_max_size','10M');
//echo $type;
//echo $formname;
$folder_path = $target_dir.$formname.'/'.$type.'/';
//echo $folder_path;
mkdir($folder_path,0777,true);
$target_file = $target_dir.$formname.'/'.$type.'/'. basename($_FILES["image"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
// Check if image file is a actual image or fake image
if(isset($_POST["submit"])) {
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if($check !== false) {
   //     echo "File is an image - " . $check["mime"] . ".";
        $uploadOk = 1;
    } else {
   echo "File is not an image.";
        $uploadOk = 0;
    }
}
// image_type, formation_name
//echo "POST = "; print_r($_POST);

// Check if file already exists
if (file_exists($target_file)) {
    echo "Sorry, file $target_file already exists.";
    $uploadOk = 0;
}
// Check file size
if ($_FILES["image"]["size"] > 50000000)
{    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}
// Allow certain file formats
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.  This one is of type: $imageFileType";
    $uploadOk = 0;
}
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
} else {
   // echo "Moving file to ".$_FILES["image"]["tmp_name"].", target_file =  $target_file";
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        echo "The file  has been uploaded. Please Reload the Page to See it";
    } else {
        echo "Sorry, there was an error uploading your file. upload_max_filesize = ".ini_get("upload_max_filesize").", post_max_size = ".ini_get("post_max_size").", memory_limit = ".ini_get("memory_limit").", failed when moving ".$_FILES["image"]["tmp_name"]." to $target_file.  FILES = "; print_r($_FILES);
    }
}
?>
