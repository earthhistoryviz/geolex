<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php 
    include_once("navBar.php");
    include_once("SqlConnection.php");
    ?>
    <br><br>
    <form action="Upload.php" method="post" enctype="multipart/form-data">
        Select Word Document to upload:
        <input type="file" name="upfile" id="upfile">
        <input type="submit" value="Upload Word Document" name="submit">
    </form>
</body>
</html>
