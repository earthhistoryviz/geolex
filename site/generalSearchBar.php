<?php
include_once("SqlConnection.php");
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
  #submitbtn {
    height: 40px;
    border: 3px solid #000000;
  }
  .search-container {
    text-align: center;
    margin-top: 10px;
  }
</style> <?php

if (!$formaction) {
  $formaction = "index.php";
}

/* For Stage filter conversion */
$timescale = parseDefaultTimescale();

include_once("constants.php"); // gets us $periods and $regions
?>

<body>
  <div class="search-container">
    <form id='form' action="<?=$formaction?>" method="request">
      <input id="searchbar" type="text" name="search" placeholder="Search Formation Name..." value="<?php if (isset($_REQUEST['search'])) echo $_REQUEST['search']; ?>">
      <button id="submitbtn" value="filter" type="button" onclick="submitFilter()">Submit</button>

      <br><br> <?php
      
      if (!isset($isFixedRegion)) { ?>
        <div id="region-container" style="padding: 5px; display: flex; flex-direction: row; width: 100%; align-items: center; justify-content: center">
          <div style="padding: 5px;">
            Select Regions to search<br>
            Hold Ctrl key to select multiple
          </div> <?php

          $selected_values = $_REQUEST['filterregion'] ?? []; ?>
          <select name="filterregion[]" multiple> <?php
            foreach ($regions as $r) {
              $selected = in_array($r["name"], $selected_values) ? 'selected' : ''; ?>
              <option value="<?=$r["name"]?>" <?=$selected?>><?=$r["name"]?></option> <?php
            } ?>
          </select>
        </div> <?php
      } ?>

      <div id="filter-container" style="padding: 5px; display: flex; flex-direction: row; width: 100%; align-items: center; justify-content: center">
        <div style="padding: 5px;">
          Search by 
        </div>
        <div style="padding: 5px;">
          <select id="searchtype-select" name="searchtype" onchange="changeFilter()" multiple >
            <option value="Period" <?php echo (isset($_REQUEST['searchtype']) && $_REQUEST['searchtype'] == 'Period' || !isset($_REQUEST['searchtype'])) ? 'selected' : ''; ?>>Period</option>
            <option value="Date" <?php echo (isset($_REQUEST['searchtype']) && $_REQUEST['searchtype'] == 'Date') ? 'selected' : ''; ?>>Date</option>
            <option value="Date Range" <?php echo (isset($_REQUEST['searchtype']) && $_REQUEST['searchtype'] == 'Date Range') ? 'selected' : ''; ?>>Date Range</option>
          </select>
        </div>
        
        <div id="selected-filter" style="padding: 5px; white-space: nowrap;"></div>
        <div style="padding: 5px;">
          Lithology includes:
          <input id="lithoSearch" type="text" style="width: 75px" name="lithoSearch" value="<?php if (isset($_REQUEST['lithoSearch'])) echo $_REQUEST['lithoSearch']; ?>">
        </div>
      </div>
    </form>
  </div>

  <script type="text/javascript">
    function submitFilter() { // TODO: check if agefilterend is greater than agefilterstart. If so, pop alert. (Currently agefilterend is set to agefilterstart in searchAPI.php if so)
      document.getElementById('form').submit();
    }

    /* Change visible selection box/text box(es) based on user selection on <searchtype-select> */
    function changeFilter() {
      var box = document.getElementById("searchtype-select");
      if (!box) {
        return;
      }
      var chosen = box.options[box.selectedIndex].value;
      var searchForm = document.getElementById("selected-filter");

      if (chosen == "Period") {
        var periodHTML = 
          "<select id='selectPeriod' name='filterperiod' onchange='changePeriod()'> \
            <option value='All' <?php echo (isset($_REQUEST['filterperiod']) && $_REQUEST['filterperiod'] == 'All') ? 'selected' : ''; ?>>All</option> \
          <?php
          foreach ($periodsDate as $p => $d) {
            if ($p) { ?> \
              <option value='<?=$p?>' <?php echo (isset($_REQUEST['filterperiod']) && $_REQUEST['filterperiod'] == $p) ? 'selected' : ''; ?>><?=$p?> (<?=number_format($d["begDate"], 2)?> - <?=number_format($d["endDate"], 2)?>)</option> \
            <?php 
            } 
          } ?> \
          </select> \
          and Stage \
          <div id='selectStage' style='padding: 5px; display: inline-block;'> \
            <select id='filterstage' name='filterstage' disabled> \
              <option value='All'>--Select Period First--</option> \
            </select> \
          </div> \
          <input id='begDate' name='agefilterstart' type='hidden' value=''> \
          <input id='endDate' name='agefilterend' type='hidden' value=''>";
        searchForm.innerHTML = periodHTML;

        // To avoid a UI bug where switching back from Date/Date Range search to period search will cause the stage selection box to be grayed out 
        changePeriod();
      } else if (chosen == "Date") {
        var dateHTML = 
          "Enter Date: <input id='begDate' type='number' style='width: 90px' name='agefilterstart' min='0' value='<?php if (isset($_REQUEST['agefilterstart'])) echo $_REQUEST['agefilterstart']; ?>'> \
          <input id='selectPeriod' name='filterperiod' type='hidden' value='All'>";
        searchForm.innerHTML = dateHTML;
      } else if (chosen == "Date Range") {
        var rangeHTML = 
          "Beginning Date: <input id='begDate' type='number' style='width: 90px' name='agefilterstart' min='0' value='<?php if (isset($_REQUEST['agefilterstart'])) echo $_REQUEST['agefilterstart']; ?>'> \
          Ending Date: <input id='endDate' type='number' style='width: 90px' name='agefilterend' min='0' value='<?php if (isset($_REQUEST['agefilterend'])) echo $_REQUEST['agefilterend']; ?>'> \
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

      /* Epoch information*/
      var epochDate = <?php echo json_encode($epochDate); ?>;

      /* When Period = All, Stage has nothing */
      if (chosen == "All") {
        var AllHTML = 
          "<select id='filterstage' name='filterstage' disabled> \
            <option value='All'>--Select Period First--</option> \
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
        stageHTML = stageHTML + "<option value='All'>All</option>";
        var prev_series = false;            
        for (rowIdx = 0; rowIdx < timescale.length; rowIdx++) {
          if (timescale[rowIdx]["period"].toLowerCase() === chosen.toLowerCase()) { // Ignoring case to prevent errors from database
            var cur_series = timescale[rowIdx]["series"];
            if (cur_series !== prev_series) {                                            
              const seriesselected = !!(cur_series.toLowerCase().trim() === "<?=$_REQUEST["filterstage"]?>".toLowerCase().trim());
              stageHTML += "<option value='" + cur_series + "'"
                + (seriesselected ? ' selected' : '') 
                + ">" 
                + cur_series 
                + " (" + epochDate[cur_series]["begDate"].toFixed(2) 
                + " - " 
                + epochDate[cur_series]["endDate"].toFixed(2) 
                + ")</option>";
            }
            prev_series = cur_series;
            var stageName = timescale[rowIdx]["stage"];
            const stageselected = !!(stageName.toLowerCase().trim() === "<?=$_REQUEST["filterstage"]?>".toLowerCase().trim());
            stageHTML = stageHTML 
              + "<option value='"
              + timescale[rowIdx]["stage"]
              + "'"
              + (stageselected ? ' selected' : '')
              + ">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" 
              + timescale[rowIdx]["stage"] + " (" 
              + timescale[rowIdx]["base"].toFixed(2) + " - " 
              + timescale[rowIdx]["top"].toFixed(2) + ")"
              + "</option>";
          }
        }
        stageHTML += "</select>";
        stageBox.innerHTML = stageHTML;

        // To make sure that the initial selection of "All" takes effect in URL as well
        stageToDate();
      }
    }

    function stageToDate() {
      var input = document.getElementById("filterstage").value;

      /* Period Date lookup table */
      var periodsDate = <?php echo json_encode($periodsDate); ?>;
      var periodChosen = document.getElementById("selectPeriod").value;

      /* Epoch Date lookup table */
      var epochDate = <?php echo json_encode($epochDate); ?>;

      /* If user selected option All for stage, we use the begDate and endDate of the period selected */
      if (input === "All" && lithoChosen === "") {
        var begDate = document.getElementById("begDate");
        begDate.value = periodsDate[periodChosen]["begDate"];
        var endDate = document.getElementById("endDate");
        endDate.value = periodsDate[periodChosen]["endDate"];

        return;
      }

      /* Convert entered Stage to corresponding Start and End Date */
      var timescale = <?php echo json_encode($timescale); ?>;

      var rowIdx;
      for (rowIdx = 0; rowIdx < timescale.length; rowIdx++) {
        if (timescale[rowIdx]["stage"].toLowerCase() === input.toLowerCase()) {  // Compare each Stage with input, ignoring case
          var begDate = document.getElementById("begDate");
          begDate.value = timescale[rowIdx]["base"];
          var endDate = document.getElementById("endDate");
          endDate.value = timescale[rowIdx]["top"];
          break;
        } else if (timescale[rowIdx]["series"].toLowerCase() === input.toLowerCase()) { // If epoch matches
          var begDate = document.getElementById("begDate");
          begDate.value = epochDate[timescale[rowIdx]["series"]]["begDate"];
          var endDate = document.getElementById("endDate");
          endDate.value = epochDate[timescale[rowIdx]["series"]]["endDate"];
          break;
        }
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
