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
      <br><br>

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
          <select id="selectType" name="searchtype" onchange="changeFilter()">
            <option value="Period" <?php echo (isset($_REQUEST['searchtype']) && $_REQUEST['searchtype'] == 'Period') ? 'selected' : ''; ?>>Period</option>
            <option value="Date" <?php echo (isset($_REQUEST['searchtype']) && $_REQUEST['searchtype'] == 'Date') ? 'selected' : ''; ?>>Date</option>
            <option value="Date Range" <?php echo (isset($_REQUEST['searchtype']) && $_REQUEST['searchtype'] == 'Date Range') ? 'selected' : ''; ?>>Date Range</option>
          </select>
        </div>
        <div id="searchform" style="padding: 5px;">
          <!--
          <select id="selectPeriod" name="filterperiod" onchange="changePeriod()">
          <option value="All" <?php echo (isset($_REQUEST['filterperiod']) && $_REQUEST['filterperiod'] == 'All') ? 'selected' : ''; ?>>All</option>
            <?php foreach($periods as $p) {?>
              <option value="<?=$p?>" <?php echo (isset($_REQUEST['filterperiod']) && $_REQUEST['filterperiod'] == $p) ? 'selected' : ''; ?>><?=$p?></option>
            <?php }?>
          </select>
          Stage: 
          <select name="filterstage">
            <option name="All">All</option>
            <option name="Norian">Norian</option>
          </select>
          <input id="begDate" name="agefilterstart" type="hidden" value="">
          <input id="endDate" name="agefilterend" type="hidden" value="">
          -->
        </div>
        <div style="padding: 5px;">
          <button id="filterbtn" value="filter" type="button" onclick="submitFilter()">Apply Filter</button>
        </div>
      </div>

    </form>
  </div>

  <script type="text/javascript">
    function verify() {
      if (document.getElementById("searchbar").value === ""){
        document.getElementById("submitbtn1").disabled = true;
      }
      else{
        document.getElementById("submitbtn1").disabled = false;
      }
    }

    function submitFilter() { // TODO: check if agefilterend is not greater than agefilterstart
      document.getElementById('form').submit();
	  }

    /* Change visible selection box/text box(es) based on user selection on <selectType> */
    function changeFilter() {
      var box = document.getElementById("selectType");
      var chosen = box.options[box.selectedIndex].value;
      var searchForm = document.getElementById("searchform");

      if (chosen == "Period") {
        var periodHTML = "<select id='selectPeriod' name='filterperiod' onchange='changePeriod()'>\
          <option value='All' <?php echo (isset($_REQUEST['filterperiod']) && $_REQUEST['filterperiod'] == 'All') ? 'selected' : ''; ?>>All</option>\
          <?php foreach($periods as $p) {?>\
              <option value='<?=$p?>' <?php echo (isset($_REQUEST['filterperiod']) && $_REQUEST['filterperiod'] == $p) ? 'selected' : ''; ?>><?=$p?></option>\
            <?php }?>\
          </select>\
          Stage: \
          <select id='filterStage' name='filterstage'>\
            <option name='All'>All</option>\
            <option name='Norian'>Norian</option>\
          </select>\
          <input id='begDate' name='agefilterstart' type='hidden' value=''>\
          <input id='endDate' name='agefilterend' type='hidden' value=''>";
        searchForm.innerHTML = periodHTML;
      } else if (chosen == "Date") {
        var dateHTML = "Enter Date: <input id='begDate' type='number' style='width: 90px' name='agefilterstart' min='0' value='<?php if (isset($_REQUEST['agefilterstart'])) echo $_REQUEST['agefilterstart']; ?>'>\
          <input id='selectPeriod' name='filterperiod' type='hidden' value='All'>";
        searchForm.innerHTML = dateHTML;
      } else if (chosen == "Date Range") {
        var rangeHTML = "Beginning Date: <input id='begDate' type='number' style='width: 90px' name='agefilterstart' min='0' value='<?php if (isset($_REQUEST['agefilterstart'])) echo $_REQUEST['agefilterstart']; ?>'>  \
          Ending Date: <input id='endDate' type='number' style='width: 90px' name='agefilterend' min='0' value='<?php if (isset($_REQUEST['agefilterend'])) echo $_REQUEST['agefilterend']; ?>'>\
          <input id='selectPeriod' name='filterperiod' type='hidden' value='All'>";
        searchForm.innerHTML = rangeHTML;
      }
    }

    /* Change the options in Stage based on user selection on Period */
    function changePeriod() {
      var box = document.getElementById("selectPeriod");
      var chosen = box.options[box.selectedIndex].value;
      var stageBox = document.getElementById("filterStage");

      // TODO: Implement switching stages
    }

    /* Keep selection on filter criteria (check previously selected option when page loads) */
    window.onload = changeFilter();

  </script>

</body>
</html>
