<html>
<head>Manage Images</head>
<body>


<?php
//include("navBar.php");
include_once("SqlConnection.php");
//include("SearchBar.php");
//$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('/app/uploads/'));
//$files = array();
//var_dump($iter);
//foreach ($iter as $file){
//	if($file->isDir()){
//		continue;
//	} 	
//	$files[] = $file->getPathname();
//} 
//var_dump($files);
?>
<form action="delete_image.php" method="POST">
	<select name = "Img_select">
		<option selected = "selected"> Choose an image</option>
<?php
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('/app/uploads/'));
$files = array();
var_dump($iter);
foreach ($iter as $file){
	if($file->isDir()){
		continue;
	}
	$files[] = $file->getPathname();
}
var_dump($files);
		foreach ($files as $file){
			?>
		<option value = "<?php echo strtolower($file); ?>"><?php echo $file;
?></option>
<?php
		}
?>
	</select>
	<input type = "submit" value = "Submit" name = "Action">
</form>

</body>
</html>

