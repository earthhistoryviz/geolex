<?php include("SqlConnection.php") ?>
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
<?php
  
  // Get all the unique periods and provinces
  $sql = "SELECT name, period, province FROM formation";
  $result = mysqli_query($conn, $sql);
  $filters = array();
  // We need to clean up the html tags from the periods and provices to get a canonical name
  while ($row = mysqli_fetch_array($result)) {
    foreach (array("province", "period") as $v) {
      //$canonical = preg_replace("/<[^>]+>/", "", $row[$v]);
      $canonical = trim($canonical);
      $canonical = strtoupper($canonical);
      $canonical = explode(",", $canonical);
      foreach ($canonical as $c) {
        $c = trim($c);
        if (strlen($c) > 0) {
          $filters[$v][$c] = true;
	}
       
      }
    }
  }
  function selectFilter($v) {
   global $filters;
   $list = array_keys($filters[$v]);
   sort($list);
   ?>
   <select name="<?=$v?>filter" id="<?=$v?>filter">
      <option value="">All</option>
      <?php
        foreach($list as $p) {
          ?><option <?php if ($_REQUEST[$v."filter"] == $p) echo "SELECTED"; ?> value="<?=$p?>"><?=$p?></option><?php
        }
      ?>
    </select><?php
  }

  if (!$formaction) {
    $formaction = "searchFm.php";
  }

  include("constants.php"); // gets us $periods and $regions
?>
<body>
    <div class = "search-container">
        
        <form id='form' action="<?=$formaction?>" method="request">
        <input id="searchbar" onkeyup="verify()" type="text" name="search" placeholder="Search Formation Name..." value="<?php if (isset($_REQUEST['search'])) echo $_REQUEST['search']; ?>">
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
          function viewAll(){
            document.getElementById('searchbar').value = ''; 
            document.getElementById('periodfilter').value = '';
            document.getElementById('provincefilter').value = '';
            document.getElementById('form').submit();

          }
          function submitFilter() {
            document.getElementById('form').submit();
          }

          </script>
       <!-- <button id="submitbtn2" type="button" onclick="viewAll()"> View All Formations </button> -->
        <br/>

        Search by Period
      <select name="filterperiod">
        <option value="All">All</option>
        <?php foreach($periods as $p) {?>
          <option value="<?=$p?>"><?=$p?></option>
        <?php }?>
      </select>

      and Region 
      <select name="filterregion">
        <option value="All">All</option>
        <?php foreach($regions as $r) {?>
          <option value="<?=$r["name"]?>"><?=$r["name"]?></option>
        <?php }?>
      </select>

      <button id="filterbtn" value="filter" type="button" onclick="submitFilter()">Apply Filter</button>

      </form>
      
    </div>
</body>
</html>
