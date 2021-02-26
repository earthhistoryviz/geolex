<?php include("SqlConnection.php") ?>
<!DOCTYPE html>
<html>

<style>
    #searchbar {
        border: 3px solid #CC99FF;
        height: 40px;
        width: 700px;
        
    }
    #submitbtn1, #submitbtn2, #submitbtn3{
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
  /*
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
  */

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
    <!--	 <button id = "submitbtn2" type = "button" onclick = "changeAge()"> Advanced Search (Age) </button> !--> 
    <!-- <button id = "submitbtn3" type = "button" onclick = "changeStage()"> Advanced Search (Stage) </button> !-->
  <br>
        <!--NEW ADDITIONS 12/3/2020
	<input id="searchbar" onkeyup="verify()" type="text" name="search" placeholder="Enter beginning date (Ma)..." value="<?php if (isset($_REQUEST['search'])) echo $_REQUEST['search']; ?>">
	<input id="searchbar" onkeyup="verify()" type="text" name="search" placeholder="Enter Ending date (Ma)..." value="<?php if (isset($_REQUEST['search'])) echo $_REQUEST['search']; ?>">
        -->
        <!-- END OF NEW ADDITIONS-->
        <!--<button id="submitbtn1" type="button">Submit</button>-->

  <script type="text/javascript">
    // var SearchCriteria = "Search by Period"; 
    function verify(){
      if (document.getElementById("searchbar").value === ""){
        document.getElementById("submitbtn1").disabled = true;
      }
      else{
        document.getElementById("submitbtn1").disabled = false;

      }
    }
    // function viewAll(){
    //   document.getElementById('searchbar').value = ''; 
    //   document.getElementById('periodfilter').value = '';
    //   document.getElementById('provincefilter').value = '';
    //   document.getElementById('begDate').value = '';
    //   document.getElementById('endDate').value = '';
    //   document.getElementById('form').submit();
    // }

    function submitFilter() {
      document.getElementById('form').submit();
	  }
	  // function changeAge() {
	  //   document.getElementById("SearchCriteria").innerHTML = "Search by Age";
	  // }
	  // function changeStage() {
    //   document.getElementById("SearchCriteria").innerHTML = "Search by Stage";
	  // }
	  // SearchCriteria = document.getElementById("SearchCriteria").value;
  </script>
       <!-- <button id="submitbtn2" type="button" onclick="viewAll()"> Advanced Search </button>!--> 
	<br/>
	<!-- <div style="display:inline" id="SearchCriteria"> <?php echo "<script>document.writeln(SearchCriteria)</script>"?> </div> -->

  Search Region 
  <select name="filterregion">
    <option value="All" <?php echo (isset($_REQUEST['filterregion']) && $_REQUEST['filterregion'] == 'All') ? 'selected' : ''; ?>>All</option>
    <?php foreach($regions as $r) {?>
      <option value="<?=$r["name"]?>" <?php echo (isset($_REQUEST['filterregion']) && $_REQUEST['filterregion'] == $r["name"]) ? 'selected' : ''; ?>><?=$r["name"]?></option>
    <?php }?>
  </select>


  <div id="searchcontainer" style="padding: 5px; display: flex; flex-direction: row; width: 100%; align-items: center; justify-content: center">
    <div style="padding: 5px;">
      Search by 
      <select name="searchtype">
        <option value="Period">Period</option>
        <option value="Period">Date</option>
        <option value="Period">Date Range</option>
      </select>
    </div>
    <div id="searchform" style="padding: 5px;">
      <select name="filterperiod">
      <option value="All" <?php echo (isset($_REQUEST['filterperiod']) && $_REQUEST['filterperiod'] == 'All') ? 'selected' : ''; ?>>All</option>
        <?php foreach($periods as $p) {?>
          <option value="<?=$p?>" <?php echo (isset($_REQUEST['filterperiod']) && $_REQUEST['filterperiod'] == $p) ? 'selected' : ''; ?>><?=$p?></option>
        <?php }?>
      </select>
      Stage: <select name="stage">
        <option name="All">All</option>
        <option name="Norian">Norian</option>
      </select>
    </div>
    <div style="padding: 5px;">
      <button id="filterbtn" value="filter" type="button" onclick="submitFilter()">Apply Filter</button>
    </div>
  </div>

<!--
    Beginning Date
  <input id="begDate" type="number" style="width: 75px" name="agefilterstart" min="0" value="<?php if (isset($_REQUEST['agefilterstart'])) echo $_REQUEST['agefilterstart']; ?>">
    Ending Date
  <input id="endDate" type="number" style="width: 75px" name="agefilterend" min="0" value="<?php if (isset($_REQUEST['agefilterend'])) echo $_REQUEST['agefilterend']; ?>">
-->

  </form>
    
  </div>
</body>
</html>
