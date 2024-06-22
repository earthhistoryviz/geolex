<?php
// purpose of file is to produce the reconstruction buttons when search by period (not all) and/or stage
// but also with search by date and search by date range

include_once("./generateRecon.php");
include_once("./formationInfo.php");
?>

<style>
  .reconbutton {
    width: 250px; 
    display: flex; 
    flex-grow: 0; 
    flex-direction: row; 
    justify-content: center; 
    align-items: center; 
    border: 3px solid #E67603; 
    border-radius: 8px; 
    padding: 10px; 
    cursor: hand; 
    margin-left: 10px;
    box-shadow: 3px 3px 5px grey;
  }
  .clickedbutton {
    box-shadow: -5px -5px 5px grey;
    background-color: #EEEEEE;
    border-color: #3366FF;
  }
</style>

<?php
// Only show this section when no reconstruction is displayed yet.
if (!isset($_REQUEST["generateImage"]) && ($_REQUEST["filterperiod"] != "All" || $_REQUEST["searchtype"] != "period")) { ?>
  <!-- changes the link when clicked and adds the Click to display map phrase -->
  <form id="reconstruction_form" method="GET" action="<?=$_SERVER["REQUEST_URI"]?>&generateImage=1">
    <div id="recon-entire-area" style="display: flex; flex-direction: column; align-items: center">
      <div id="reconbutton-message" style="padding-bottom: 5px">
        Click to display on map of the Ancient World at:
      </div>
      <div id="recon-button-selection-area" style="display: flex; flex-direction: row; align-items: center; padding-bottom: 10px;"> <?php
}

// coordiantes the generation of the different buttons depending on the search type (period, date, date range)
if (!isset($_REQUEST["generateImage"])) {
  if ($_REQUEST["searchtype"] == "Date") {
    reconbutton("$basepretty Ma", "reconbutton-base", $baseraw, 'on date');
    createOptions();
  } else if ($_REQUEST["searchtype"] == "Period" && $_REQUEST["filterperiod"] != "All") {
    reconbutton("<b>Base</b> of $name<br/>($basepretty Ma)", "reconbutton-base", $baseraw, 'base');
    reconbutton("<b>Middle</b> of $name<br/>($middlepretty Ma)", "reconbutton-middle", $middleraw, 'middle');
    createOptions();
  } else if (isset($_REQUEST["formation"])) {
    reconbutton($_REQUEST["formation"]." base reconstruction", "reconbutton-base", $fmdata["beg_date"]["display"], 'base');
    createOptions();
  } else if ($_REQUEST["searchtype"] == "Date Range") {
    reconbutton("$basepretty Ma", "reconbutton-base", $baseraw, 'base');
    reconbutton("$middlepretty Ma", "reconbutton-middle", $middleraw, 'middle');
    createOptions();
  } else if (isset($_REQUEST["searchtypelist"])) {
    if ($_REQUEST['searchtypelist'] == 'formation name') {
      reconbutton($_REQUEST['SearchFm']." base reconstruction", "reconbutton-base", $fmdata["beg_date"]['display'], 'base');
      createOptions();
    } else if ($_REQUEST['searchtypelist'] == 'formation id') {
      reconbutton("Formation id ".$_REQUEST['SearchFm']." base reconstruction", "reconbutton-base", $fmdata["beg_date"]["display"], 'base');
      createOptions();
    }
  }
} ?>
</div> <?php /* end div for id=recon-entire-area */ ?>

<div id="hidden-params"> <!-- Add hidden fields to remember all user choices -->
  <?php /* Create placeholders for the buttons to fill in when they are clicked for middle/base */ ?>
  <input type="hidden" name="recondate" id="recondate" value="<?=$_REQUEST["recondate"]?>" /> 
  <input type="hidden" name="recondate_description" id="recondate_description" value="<?=$_REQUEST["recondate_description"]?>" />
  <input type="hidden" name="generateImage" value="1" /> <?php

  foreach ($_REQUEST as $k => $v) {
    if ($k == "filterprovince" && is_array($v)) {
      // To ensure the selections in the Region filter is parsed as an array instead of the word "array"
      foreach ($v as $vregion) { ?>
        <input type="hidden" name="filterprovince[]" value="<?=$vregion?>" /> <?php
      }
    } else { ?>
      <input type="hidden" name="<?=$k?>" id="<?=$k?>" value="<?=$v?>" /> <?php
    }
  } ?>
</div>
</form>

<script type="text/javascript">
  function submitForm(recondate, recondate_description) {
    document.getElementById('recondate').value = recondate;
    document.getElementById('recondate_description').value = recondate_description;
    document.getElementById('reconstruction_form').submit();
  }
</script>

<?php
function reconbutton($text, $id, $recondate, $recondate_desc) { ?>
  <div class="reconbutton" id="<?=$id?>"
    onmousedown="
      document.getElementById('<?=$id?>').className = 'reconbutton clickedbutton';
    "
    onmouseup="
      document.getElementById('<?=$id?>').className = 'reconbutton';
      document.getElementById('<?=$id?>-text').innerHTML = 'Loading reconstruction... This could take up to a minute. It was a long time ago.';
    "
    onclick="submitForm('<?=$recondate?>', '<?=$recondate_desc?>')"
  > <!-- Rather than both buttons submitting form, each button will go to submitForm function and approratie instructions happen then --> 
    <div style="flex-grow: 0">
      <div>
        <img src="/noun_Earth_2199992.svg" width="50px" height="50px"/>
      </div>
    </div>
    <div id="<?=$id?>-text" style="margin-left: 5px; flex-grow: 0; color: #E67603; font-family: arial">
      <?=$text?>
    </div>
  </div> <?php 
}

function createOptions() { ?>
  <div id="model-selections">
    <select id="selectModel"  name="selectModel" size="3" style="overflow: auto">
      <option value="Default" <?php if ($_REQUEST["selectModel"] == "Default" || !$_REQUEST["selectModel"]) echo "SELECTED"; ?>>
        Reconstruction Model: GPlates Default (Merdith, Williams, et al., 2021)
      </option>
      <option value="Marcilly" <?php if ($_REQUEST["selectModel"] == "Marcilly") echo "SELECTED"; ?>>
        Reconstruction Model: Continental flooding model (Marcilly, Torsvik et al., 2021)
      </option> 
      <option value="Scotese" <?php if ($_REQUEST["selectModel"] == "Scotese") echo "SELECTED"; ?>>
        Reconstruction Model: Paleo-topography (Chris Scotese, 2018)
      </option> 
    </select> 
  </div> 
</div> <?php /* end div for id=recon-button-selection-area */
}
