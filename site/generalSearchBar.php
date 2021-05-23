<?php
include("SqlConnection.php");
include_once("TimescaleLib.php");
?>
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
  // We need to clean up the html tags from the periods and provinces to get a canonical name
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

  /* For Stage filter conversion */
  $timescale = parseDefaultTimescale();

  include("constants.php"); // gets us $periods and $regions
?>

<body>
  <div class="search-container">
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
        <div id="searchform" style="padding: 5px; white-space: nowrap;"></div>
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

    function submitFilter() { // TODO: check if agefilterend is greater than agefilterstart. If so, pop alert. (Currently agefilterend is set to agefilterstart in searchAPI.php if so)
      document.getElementById('form').submit();
	  }

    /* Change visible selection box/text box(es) based on user selection on <selectType> */
    function changeFilter() {
      var box = document.getElementById("selectType");
      if (!box) {
        return;
      }
      var chosen = box.options[box.selectedIndex].value;
      var searchForm = document.getElementById("searchform");

      if (chosen == "Period") {
        var periodHTML = 
          "<select id='selectPeriod' name='filterperiod' onchange='changePeriod()'>\
            <option value='All' <?php echo (isset($_REQUEST['filterperiod']) && $_REQUEST['filterperiod'] == 'All') ? 'selected' : ''; ?>>All</option>\
            <?php foreach($periods as $p) {?>\
              <option value='<?=$p?>' <?php echo (isset($_REQUEST['filterperiod']) && $_REQUEST['filterperiod'] == $p) ? 'selected' : ''; ?>><?=$p?></option>\
            <?php }?>\
          </select>\
          and Stage\
          <div id='selectStage' style='padding: 5px; display: inline-block;'>\
            <select id='filterstage' name='filterstage' disabled>\
              <option value='All'>--Select Period First--</option>\
            </select>\
          </div>\
          <input id='begDate' name='agefilterstart' type='hidden' value=''>\
          <input id='endDate' name='agefilterend' type='hidden' value=''>";
        searchForm.innerHTML = periodHTML;
      } else if (chosen == "Date") {
        var dateHTML = 
          "Enter Date: <input id='begDate' type='number' style='width: 90px' name='agefilterstart' min='0' value='<?php if (isset($_REQUEST['agefilterstart'])) echo $_REQUEST['agefilterstart']; ?>'>\
          <input id='selectPeriod' name='filterperiod' type='hidden' value='All'>";
        searchForm.innerHTML = dateHTML;
      } else if (chosen == "Date Range") {
        var rangeHTML = 
          "Beginning Date: <input id='begDate' type='number' style='width: 90px' name='agefilterstart' min='0' value='<?php if (isset($_REQUEST['agefilterstart'])) echo $_REQUEST['agefilterstart']; ?>'>\
          Ending Date: <input id='endDate' type='number' style='width: 90px' name='agefilterend' min='0' value='<?php if (isset($_REQUEST['agefilterend'])) echo $_REQUEST['agefilterend']; ?>'>\
          <input id='selectPeriod' name='filterperiod' type='hidden' value='All'>";
        searchForm.innerHTML = rangeHTML;
      }
    }

    /* Change the options in Stage based on user selection on Period */
    function changePeriod() {
      var box = document.getElementById("selectPeriod");
      if (!box || box.type === "hidden") {
        return;
      }
      var chosen = box.options[box.selectedIndex].value;
      var stageBox = document.getElementById("selectStage");

      /* Timescale Array */
      var timescale = <?php echo json_encode($timescale); ?>;

      /* When Period = All, Stage has nothing */
      if (chosen == "All") {
        var AllHTML = 
        "<select id='filterstage' name='filterstage' disabled>\
          <option value='All'>--Select Period First--</option>\
        </select>";
        stageBox.innerHTML = AllHTML;
        /* Since Stage filter is not used, both Dates are set to empty. */
        var begDate = document.getElementById("begDate");
        begDate.value = '';
        var endDate = document.getElementById("endDate");
        endDate.value = '';
      } else { // When a Period is selected
        var stageHTML = "<select id='filterstage' name='filterstage' onchange='stageToDate()'>";
        var rowIdx;
        for (rowIdx = 0; rowIdx < timescale.length; rowIdx++) {
          if (timescale[rowIdx]["period"].toLowerCase() === chosen.toLowerCase()) { // Ignoring case to prevent errors from database
            stageHTML = stageHTML + "<option value='" + timescale[rowIdx]["stage"] + "'>" + timescale[rowIdx]["stage"] + "</option>";
          }
        }
        stageHTML += "</select>";
        stageBox.innerHTML = stageHTML;
      }
    }

    /* Temporary: implementation of Stage input textbox */
    function stageToDate() {
      var input = document.getElementById("filterstage").value;

      /* Convert entered Stage to corresponding Start and End Date */
      var timescale = <?php echo json_encode($timescale); ?>;
      var rowIdx;
      var found = false;
      for (rowIdx = 0; rowIdx < timescale.length; rowIdx++) {
        if (timescale[rowIdx]["stage"].toLowerCase() === input.toLowerCase()) {  // Compare each Stage with input, ignoring case
          found = true;
          break;
        }
      }
      if (found) { // Change Starting Date and Ending Date accordingly
        var begDate = document.getElementById("begDate");
        begDate.value = timescale[rowIdx]["base"];
        var endDate = document.getElementById("endDate");
        endDate.value = timescale[rowIdx]["top"];
      } 
    }

    /* Keep selection on filter criteria and, if applicable, stage filter (check last selected options when page loads) */
    function onLoad() {
      changeFilter();
      changePeriod();
    }

    window.onload = onLoad();

  </script>

</body>
</html>
