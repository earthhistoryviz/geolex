<!DOCTYPE html>
<html>

<style>
    #searchbar {
        border: 3px solid #CC99FF;
        height: 40px;
        width: 700px;
        
    }
    #submitbtn {
        height: 40px;
        border: 3px solid #000000;
    }

    .search-container{
	text-align: center;
        margin-top: 10px;
    }
    
</style>

<body>
    <div class = "search-container">
        
        <form action="searchFm.php" method="post">
        <input id="searchbar" type="text" name="search" placeholder="Search Formation Name...">
        <input id="submitbtn" type="submit" value="Submit">
        </form>
    </div>
</body>
</html>
