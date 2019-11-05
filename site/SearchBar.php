<!DOCTYPE html>
<html>

<style>
    #searchbar {
        border: 3px solid #CC99FF;
        height: 40px;
        width: 700px;
        
    }
    #submitbtn1, #submitbtn2{
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
        <input id="searchbar" onkeyup="verify()" type="text" name="search" placeholder="Search Formation Name..." value="<?php if (isset($_POST['search'])) echo $_POST['search']; ?>">
         <input id="submitbtn1" type="submit" value="Submit" disabled> 
        <!--<button id="submitbtn1" type="button">Submit</button>-->

        <script type="text/javascript">
        	function verify(){
        		if (document.getElementById("searchbar").value === ""){
        			document.getElementById("submitbtn1").disabled = true;
        		}
        		else{
        			document.getElementById("submitbtn1").disabled = false;

				}
        	}
        	</script>
        <button id="submitbtn2" type="button" onclick="alert('Hello!')"> View All Formations </button>
        </form>
    	
    </div>
</body>
</html>
