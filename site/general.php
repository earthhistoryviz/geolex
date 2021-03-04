<?php
include("constants.php");

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
    $response = json_decode(file_get_contents($url));
    $results[$r["name"]] = array(
      "linkurl" => $r["linkurl"],
      "formations" => $response,
      "groupbyprovince" => array(),
    );
    foreach($response as $fname => $finfo) {
      $p = $finfo->province;
      if (!$p || strlen(trim($p)) < 1) $p = "Unknown Province";
      $results[$r["name"]]["groupbyprovince"][$p]["formations"][$fname] = $finfo;
      /*
      $results["china"][groupbyprovince][fujian][formations][taoziken Fm]
                                                            [Baratang Fm]
                                                [groupbyperiod][cretaceous][taoziken fm]
                                                               [jurassic  ][baratan fm]
      */
      /* Figure out which periods overlap this formation */
      foreach($periods as $searchperiod) {
        if (stripos($finfo->period, $searchperiod) === false) continue;
        $results[$r["name"]]["groupbyprovince"][$p]["groupbyperiod"][$searchperiod][$fname] = $finfo;
      }
    }
  }
}

/* This is necessary to get generalSearchBar to send things back to us */
$formaction = "general.php";
?>
<link rel="stylesheet" href="generalStyling.css">

<?php include("navBar.php");?>
<?php /* navBar will set $period for us */?>

<h2 align="center" style="color:blue;">Welcome to the International Geology Website and Database!<br>Please enter a formation name or group to retrieve more information.</h2>
<?php include("generalSearchBar.php");?>

<div style="display: flex; flex-direction: column;">
<?php
$sorted = array();
if ($didsearch) {
  if (count($results) < 0) {
    echo "No results found.";
  } else {  
    foreach($results as $regionname => $regioninfo) {?>
      <div class="formation-container" id="<?=$regionname?>">
        <h3 class="region-title"><?=$regionname?></h3>
        <hr/>
        <div><?php
          $count = 0; // What is this for?
          $sortByPeriod = array();
          // echo "$regioninfo";
          foreach($regioninfo["groupbyprovince"] as $province => $provinceinfo) {?>
            <h3><?=$province?></h3>
            <div class="province-container"> <?php
              foreach($periods as $p) {
                foreach($provinceinfo["groupbyperiod"] as $pname => $formations) {
                  if($pname !== $p) continue;?>
                  <h5><?=$pname?></h5>
                  <div class="period-container"><?php
                    foreach($formations as $fname => $finfo){?>
                      <div class = "button">
                        <a href="<?=$regioninfo["linkurl"]?>?formation=<?=$fname?>" target="_blank"><?=$fname?></a>
                      </div><?php 
                    }?>
                  </div><?php 
                }
              }?>
            </div><?php
          }?>
          <hr/>
        </div>
      </div><?php
    }
  } 
}?>
</div>
