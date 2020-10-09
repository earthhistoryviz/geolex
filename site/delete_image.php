<?php
$file_to_delete = $_POST['Img_select'];
//preg_replace("/.+(app).+/","site",$file_to_delete);
//echo "file_to_delete = $file_to_delete<br>";
//chmod($file_to_delete,777);
if(file_exists($file_to_delete)){
  $deleted = unlink($file_to_delete);
	if($deleted){
		echo "File successfully deleted,Please Reload the Page to See Changes";
	}
	else{
		echo "Error deleting file";
	}
}
else {
	echo "Error, This file Does not exist.It has been deleted before";
}
?>

