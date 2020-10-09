<?php

  echo "REQUEST = <pre>"; print_r($_REQUEST); echo "</pre>";
echo "server <pre>"; print_r($_SERVER); echo "</pre>";

  // If we have a filterperiod and filterregion, send off the API requests
  if ($_REQUEST["filterperiod"] && $_REQUEST["filterregion"]) {
    $didsearch = true;
    $results = array();


    $url = "http://" . $_SERVER["HTTP_HOST"] . "/searchAPI.php";
    echo "URL = $url";
    $response = json_decode(file_get_contents($url));
    array_push($results, $response);
  }


  // This is necessary to get generalSearchBar to send things back to us
  $formaction = "general.php";
?>

<?php include("navBar.php");?>
<?php /* navBar will set $period for us */?>

  <h2 align="center" style="color:blue;">Welcome to the International Geology Website and Database! <br>Please enter a formation name or group to retrieve more information.</h2>
  <?php include("generalSearchBar.php");?>

  <?php
    if ($didsearch) {
      if (count($results) < 0) {
        echo "No results found.";
      } else {
	      echo "We have results!  it is: <pre>"; print_r($results); echo "</pre>";
	      if($_REQUEST["filterregion"] == "China"){
	      if($_REQUEST["filterperiod"] == "All"){
		      $data = file_get_contents("http://china.oada-dev.com/searchFm.php?search=&periodfilter=&provincefilter=");
	      }
	      else{
		      $data = file_get_contents("http://china.oada-dev.com/searchFm.php?search=&periodfilter=" .$_REQUEST["filterperiod"] . "&provincefilter=");
	      }
	      $first_s = explode( '<div class="formation-container">' , $data );
	      $second_s = explode("</div>" , $first_s[1] );
	      echo $first_s[1];
	      }

	      if($_REQUEST["filterregion"] == "India"){
              if($_REQUEST["filterperiod"] == "All"){
                      $data = file_get_contents("http://inplex.oada-dev.com/searchFm.php?search=&periodfilter=&provincefilter=");
              }
              else{
                      $data = file_get_contents("http://inplex.oada-dev.com/searchFm.php?search=&periodfilter=" .$_REQUEST["filterperiod"] . "&provincefilter=");
              }
              $first_s = explode( '<div class="formation-container">' , $data );
              $second_s = explode("</div>" , $first_s[1] );
              echo $first_s[1];

	      }
	       if($_REQUEST["filterregion"] == "All"){
              if($_REQUEST["filterperiod"] == "All"){
		      $china = file_get_contents("http://china.oada-dev.com/searchFm.php?search=&periodfilter=&provincefilter=");
		      $india= file_get_contents("http://inplex.oada-dev.com/searchFm.php?search=&periodfilter=&provincefilter=");
              }
              else{
		      $china= file_get_contents("http://china.oada-dev.com/searchFm.php?search=&periodfilter=" .$_REQUEST["filterperiod"] . "&provincefilter=");
		      $india= file_get_contents("http://inplex.oada-dev.com/searchFm.php?search=&periodfilter=" .$_REQUEST["filterperiod"] . "&provincefilter=");
              }
              $first_sC = explode( '<div class="formation-container">' , $china );
	      $second_sC = explode("</div>" , $first_sC[1] );
	      echo "China Formations";
	      echo  $first_sC[1];
	      $first_sI = explode( '<div class="formation-container">' , $india);
	      $second_sI = explode("</div>" , $first_sI[1] );
	      echo "India Formations";
              echo $first_sI[1];

		      
	      

	      }
      }
    }
     
    
?>

