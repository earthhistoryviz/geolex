<?php  // purpose of file is to produce the reconstruction buttons when search by period (not all) and/or stage 
// but also with search by date and search by date range 
include_once("./generateRecon.php");
include_once("./formationInfo.php");
?> 
<style>
.reconbutton {
    width:250px; 
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
</style>
<?php
if(!isset($_REQUEST["generateImage"]) && ($_REQUEST["filterperiod"] != "All" || $_REQUEST["searchtype"] != "period")){
?>
 <!--changes the link when clicked and adds the Click to display map phrase -->
    <form id="reconstruction_form" method="GET" action="<?=$_SERVER["REQUEST_URI"]?>&generateImage=1">
        <div style="display: flex; flex-direction: column; align-items: center">
            <div id="reconbutton-message" style="padding-bottom: 5px">
                  Click to display on map of the Ancient World at:
            </div>
            <div style="display: flex; flex-direction: row; align-items: center; padding-bottom: 10px;">

<?php
}


function reconbutton($text, $id, $recondate, $recondate_desc) { ?>
    <div class="reconbutton"  id="<?=$id?>"
      onclick="submitForm('<?=$recondate?>', '<?=$recondate_desc?>')"> <!-- Rather than both buttons submitting form, each button will go to submitForm function and approratie instructions happen then --> 
      <div style="flex-grow: 0">
        <div>
             <img src="noun_Earth_2199992.svg" width="50px" height="50px"/>
        </div>
      </div>
      <div style="margin-left: 5px; flex-grow: 0; color: #E67603; font-family: arial">
        <?=$text?>
      </div>
    </div>


<?php 
} // corresponds to the end of function reconbutton

?> <script type="text/javascript">
function submitForm(recondate, recondate_description) {
    document.getElementById('recondate').value = recondate;
    document.getElementById('recondate_description').value = recondate_description;
    document.getElementById('reconstruction_form').submit();

}
</script> <?php

// coordiantes the generation of the different buttons depending on the search type (period, date, date range)
if(!isset($_REQUEST["generateImage"])){
    if ($_REQUEST["searchtype"] == "Date") {
        reconbutton("$basepretty Ma", "reconbutton-base", $baseraw, 'on date');
        createOptions();
    } else if ($_REQUEST["searchtype"] == "Period" && $_REQUEST["filterperiod"] != "All") {
        reconbutton("<b>Base</b> of $name<br/>($basepretty Ma)",  "reconbutton-base", $baseraw, 'base');
        reconbutton("<b>Middle</b> of $name<br/>($middlepretty Ma)",  "reconbutton-middle", $middleraw, 'middle');
        createOptions();
    } else if(isset($_REQUEST["formation"])) {
        reconbutton($_REQUEST["formation"]. " base reconstruction", "reconbutton-base", $fmdata["beg_date"]["display"], 'base');
        createOptions();
    } else if($_REQUEST["searchtype"] == "Date Range") {
        reconbutton("$basepretty Ma", "reconbutton-base", $baseraw, 'base');
        reconbutton("$middlepretty Ma", "reconbutton-middle", $middleraw, 'middle');
        createOptions();
    }
    

}
?>

</div> <!-- div tag responsible for displaying the different buttons -->
<div> <!--// gives options for the three different reconstruction models to choose from -->
<?php 
function createOptions(){
?>
<div>
    <select id="selectModel"  name="selectModel" size="3" style="overflow: auto">
        <option value="Default" <?php if ($_REQUEST["selectModel"] == "Default" || !$_REQUEST["selectModel"]) echo "SELECTED"; ?>>
            Reconstruction Model: GPlates Default (Merdith, Williams, et al., 2021)
        </option> 
            <!--      <option value="Chris" <?php if ($_REQUEST["selectModel"] == "Chris") echo "SELECTED"; ?>>Chris' Model</option> !-->
       
        <option value="Marcilly" <?php if ($_REQUEST["selectModel"] == "Marcilly") echo "SELECTED"; ?>>
            Reconstruction Model: Continental flooding model (Marcilly, Torsvik et al., 2021)
        </option> 
        <option value="Scotese" <?php if ($_REQUEST["selectModel"] == "Scotese") echo "SELECTED"; ?>>
            Reconstruction Model: Paleo-topography (Chris Scotese, 2021)
        </option> 
    </select> 
</div> 
</div> 
<?php
}


//} // corresponds to the if(!isset($_REQUEST["generateImage"])){
/* Create placeholders for the buttons to fill in when they are clicked for middle/base */ ?>
                <input type="hidden" name="recondate" id="recondate" value="<?=$_REQUEST["recondate"]?>"  /> 
                <input type="hidden" name="recondate_description" id="recondate_description" value="<?=$_REQUEST["recondate_description"]?>" /> 


<?php 


foreach($_REQUEST as $k => $v) {?>
                  <input type="hidden" name="<?=$k?>" id="<?=$k?>" value="<?=$v?>" /> 

 

<?php } 
?>
                <input type="hidden" name="generateImage" value="1" /> 
              </div>
            </form>

