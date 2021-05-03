<?php
include("constants.php");
include_once("TimescaleLib.php");

/* If we have a filterperiod and filterregion, send off the API requests */
if ($_REQUEST["filterperiod"] && $_REQUEST["filterregion"]) {
  $didsearch = true;

  $regionstosearch = array();
  foreach($regions as $r) {
    if ($r["name"] == $_REQUEST["filterregion"] || $_REQUEST["filterregion"] == "All") {
      array_push($regionstosearch, $r);
    }
  }

  $results = array();

  foreach($regionstosearch as $r) {
    $url = $r["searchurl"] . "?searchquery=".$_REQUEST["search"]."&periodfilter=".$_REQUEST["filterperiod"]."&agefilterstart=".$_REQUEST["agefilterstart"]."&agefilterend=".$_REQUEST["agefilterend"];
    $raw = file_get_contents($url);
    $response = json_decode($raw);

    $results[$r["name"]] = array(
      "linkurl" => $r["linkurl"],
      "formations" => $response,
      "groupbyprovince" => array(),
    );

    foreach($response as $fname => $finfo) {
      $p = $finfo->province;
      if(!$p || strlen(trim($p)) < 1) $p = "Unknown Province";
      //$results[$r["name"]]["groupbyprovince"][$p]["formations"][$fname] = $finfo;
      $newp = explode(", ", $p);
      $overlapCount = 0; // counts number of overlaps
      foreach($newp as $sepp){
	      // $results[$r["name"]]["groupbyprovince"][$sepp]["formations"][$fname] = $finfo;
        $results[$r["name"]]["groupbyprovince"][$sepp]["formations"][$fname] = $finfo; 

        /*
        $results["china"][groupbyprovince][fujian][formations][taoziken Fm]
                                                              [Baratang Fm]
                                                  [groupbyperiod][cretaceous][taoziken fm]
                                                                [jurassic  ][baratan fm]
        */

        /* Figure out which periods overlap this formation */
        foreach($periods as $searchperiod) {
          if(stripos($finfo->period, $searchperiod) === false) continue;
          $results[$r["name"]]["groupbyprovince"][$sepp]["groupbyperiod"][$searchperiod][$fname] = $finfo;
        
        }
      }
    }
    ksort($results[$r["name"]]["groupbyprovince"]);
  }
}
/* This is necessary to get generalSearchBar to send things back to us */
$formaction = "general.php"; ?>
<link rel="stylesheet" href="generalStyling.css">

<?php include("navBar.php"); /* navBar will set $period for us */?>

<h2 align="center" style="color:blue;">Welcome to the International Geology Website and Database!<br>Please enter a formation name or group to retrieve more information.</h2>
<?php include("generalSearchBar.php");?>

<div style="display: flex; flex-direction: column;">
<?php
$sorted = array();

// get all of the associated stage data 
$info = parseDefaultTimescale();
$stageConversion = array();
$storedStage = "none";
$count = 0; // used for indexing through the stageConversion array
foreach($info as $element) {
  foreach($element as $key => $val) {
    if($key == "stage"){
      array_push($stageConversion, array($val => "none"));
      $storedStage = $val;
    }
    if($key == "color") {
      $stageConversion[0][$storedStage] = str_replace('/', ', ',  $val);
      $count = $count + 1;
    }
  }
}
$stageArray = $stageConversion[0]; // stores the stages as well as the lookup in RGB 
// echo '<pre>';
// echo '</pre>'; // TODO: what's <pre> for?
if ($didsearch) {
  if (count($results) < 0) {
    echo "No results found.";
  } else {

    /*
      Provide option for showing reconstruction image when using:
        1. Date
        2. Date Range, with either:
          a. no End Date
          b. Start Date == End Date
    */
    if ($_REQUEST[agefilterstart] != "" && $_REQUEST[agefilterstart] == $_REQUEST[agefilterend]
        || $_REQUEST[agefilterstart] != "" && $_REQUEST[agefilterend] == "") {
      $image_encode = shell_exec("base64 test.JPG"); // TODO: This is for testing purpose. Actual base64 encoding should be done by pyGMT
      ?>
      <div class="reconstruction">
        <button id="toggle_img" type="button" style="padding: 5px;" onclick="toggle_reconstruction()">Press to Display on a Plate Reconstruction (<?php echo $_REQUEST[agefilterstart]; ?> Ma)</button>
        <div id="reconstruction_image" style="display:none;">
          <img src="data:image/jpg;base64, <?php echo $image_encode; ?>" width="50%" height="auto">
        </div>
      </div>

    <?php
    }
    /*
      Show all returned formations in following format:
        Region
        ----------
        Province
        PERIOD
        Formation_1 Formation_2 ...
        PERIOD
        Formation_3 Formation_4 ...
        ----------
        Province
        PERIOD
        Formation_5 Formation_6 ...
        ----------
        .
        .
        .
        
        Region
        ----------
        .
        .
        .
    */
    foreach($results as $regionname => $regioninfo) {?>
      <div class="formation-container" id="<?=$regionname?>">
        <h3 class="region-title"><?=$regionname?></h3>
        <hr>
        <div> <?php
          $sortByPeriod = array();
          foreach($regioninfo["groupbyprovince"] as $province => $provinceinfo) { ?>
            <hr> 
            <h3><?=$province?></h3>
            <div class="province-container"> <?php
              foreach($periods as $p) {
                foreach($provinceinfo["groupbyperiod"] as $pname => $formations) {
                  if($pname !== $p) continue; ?>
                  <h5><?=$pname?></h5>
                  <div class="period-container"> <?php
                    foreach($formations as $fname => $finfo){
                      $finfoArr = json_decode(json_encode($finfo), true); ?>
                      <div style="background-color:rgb(<?=$stageArray[$finfoArr["stage"]]?>, 0.8);" class = "button">
                        <a href="<?=$regioninfo["linkurl"]?>?formation=<?=$fname?>" target="_blank"><?=$fname?></a>
                      </div> <?php 
                    } ?>
                  </div> <?php 
                }
              } ?>
            </div> <?php
          } ?>
        </div>
      </div> <?php
    }
  } 
} ?>
</div>

<script type="text/javascript">
  function toggle_reconstruction() {
    var img = document.getElementById("reconstruction_image");
    if (img.style.display === "none") {
      img.style.display = "block";
    } else {
      img.style.display = "none";
    }
  }
</script>
