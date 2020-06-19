<?php
$file_to_delete = $_POST['Img_select'];
//preg_replace("/.+(app).+/","site",$file_to_delete);
echo "file_to_delete = $file_to_delete<br>";
//chmod($file_to_delete,777);
if(file_exists($file_to_delete)){
  $deleted = unlink($file_to_delete);
	if($deleted){
		echo "file successfully deleted";
	}
	else{
		echo "Error deleting file".$file_to_delete;
	}
}
else {
	echo "file doesnt exist, file_exists = " . file_exists($file_to_delete);
}
?>

