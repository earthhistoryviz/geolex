
<!DOCTYPE html>
<html>
<body>
<?php 
include_once("navBar.php");
include_once("SearchBar.php");
include_once("SqlConnection.php");
?>
<form action="Upload.php" method="post" enctype="multipart/form-data">
    Select Word Document to upload:
    <input type="file" name="upfile" id="upfile">
    <input type="submit" value="Upload Word Document" name="submit">
</form>

</body>
</html>
