<?php 


if ($_REQUEST["generateImage"] == "1") {
            $timedout = false;




if (!$initial_creation_outdir) { // we already had the folder up above, so just wait for image...
              $count=0;



/*
while (!file_exists("$outdirname_php/final_image.png")) { // assume another thing is making this image
                usleep(500);
                $count++;





if ($count > 30) { // we've tried for 20 seconds, just fail it
                  $timedout = true;
                  break;
                }


}
*/
              // If we get here, image should exist, or we gave up waiting

}

            // Run pygplates if either a) we had to make the hash folder because it didn't exist, or b) we timed out (try again)



if ($initial_creation_outdir) {




    switch($_REQUEST["selectModel"]) {
                case "Default":
                  $hello = exec("cd pygplates && ./master_run_pygplates_pygmt.py ".$_REQUEST['recondate']." $outdirname", $ending);
                  echo "<pre>";
                  print_r($hello);
                  echo "</pre>";
                break;
                case "Marcilly": 
                  $hello = exec("cd pygplates && ./MarcillyModel.py ".$_REQUEST['recondate']." $outdirname", $ending);
                  echo "<pre>";
                  print_r($hello);
                  echo "</pre>";
                break;
                case "Scotese":
                  $hello = exec("cd pygplates/ScoteseDocs && ./ScoteseModel.py ".$_REQUEST['recondate']." $outdirname", $ending);
                  //echo "<pre> Result: "; print_r($ending); echo "</pre>";
                  echo "<pre>";
                  print_r($hello);
                  echo "</pre>";
                break;



    }
  






}?>
 
            <div id="reconImg" align="center"><?php



if($_REQUEST["searchtype"] == "Period" && $_REQUEST["filterstage"] != "All"){?>
                <figcaption style="text-align: center; font-size: 45px;"> Reconstruction for <?=$_REQUEST[recondate_description]?> of <?= $_REQUEST["filterstage"] ?> </figcaption><?php
              } else if($_REQUEST["searchtype"] == "Period") { ?>
                <figcaption style="text-align: center; font-size: 45px;"> Reconstruction for <?=$_REQUEST[recondate_description]?> of <?= $_REQUEST["filterperiod"] ?> </figcaption><?php


} 


if(file_exists($outdirname_php."/final_image.png")){
              ?>
              <div>
              <a href="<?=$outdirname_php?>/final_image.png">
                <img src="<?=$outdirname_php?>/final_image.png" style="text-align:center" width ="80%" />
              </a>
              <br/><br/>
              A very special thanks to the excellent <a href="https://gplates.org">GPlates</a> and their
              <a href="https://www.gplates.org/docs/pygplates/pygplates_getting_started.html">pyGPlates</a> software as well as
              <a href="https://www.pygmt.org/latest/">pyGMT</a> which work together to create these images.
            </div> <?php
            } else {
              //createImage();
              echo "No available reconstruction image";

} 

          } else if($_REQUEST["generateImage"] != "2") {
            // User selection of reconstruction model
            if(isset($_REQUEST["agefilterstart"]))
            {
              $baseraw = $_REQUEST["agefilterstart"];
              $basepretty = number_format($baseraw, 2);
            }
            else 
            {
              $baseraw = $_REQUEST["recondate"];
              $basepretty = number_format($baseraw, 2);
            }
            $topraw = $_REQUEST["agefilterend"];
            $toppretty = number_format($topraw, 2);



if ($_REQUEST["searchtype"] == "Period") {
              $middleraw = ($_REQUEST["agefilterstart"] + $_REQUEST["agefilterend"])/2.0;
              $middlepretty = number_format($middleraw, 2);
              //$useperiod = true;


if ($_REQUEST["filterstage"] && $_REQUEST["filterstage"] != "All") {
                $name = $_REQUEST["filterstage"];
              } else {
                $name = $_REQUEST["filterperiod"];
}
}
          }


?>