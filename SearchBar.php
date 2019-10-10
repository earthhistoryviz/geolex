<!DOCTYPE html>
<html>

<style>
    #searchbar {
        border: 3px solid #CC99FF;
        height: 40px;
        width: 300px;
    }
    #submitbtn {
        height: 40px;
        border: 3px solid #000000;
    }
    
</style>

<body>
<h3><b>Enter Formation Name Below: </b></h3>
<form action="searchFm.php" method="post">
    <input id="searchbar" type="text" name="search" placeholder="Search Formation Name...">
    <input id="submitbtn" type="submit" value="Submit">
</form>
</body>
</html>