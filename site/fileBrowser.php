
<!DOCTYPE html>
<html>
<body>
<?php 
include("navBar.php");
include("SearchBar.php");
include("SqlConnection.php");
?>
<form action="Upload.php" method="post" enctype="multipart/form-data">
    Select Word Document to upload:
    <input type="file" name="upfile" id="upfile">
    <input type="submit" value="Upload Word Document" name="submit">
</form>

</body>
</html>
