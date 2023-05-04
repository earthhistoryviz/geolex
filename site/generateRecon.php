<?php

if ($_REQUEST["generateImage"] == "1") {
  $timedout = false;

  function removeOldHashDirs($pathpfx) {
    // Clear out any old reconstructions first
    $topdirs = scandir($pathpfx);
    $hashlength = 32; // 32 chars in a hash filename
    foreach ($topdirs as $topdir) {
      $path = "$pathpfx/$topdir";
      if (!is_dir($path) || $topdir[0] == '.') {
        continue;
      }
      if (strlen($topdir) == $hashlength) {
        // check how old it is, delete if older than 15 days
        // you need the /. on the end since the hash is a dir
        if (time()-filemtime("$path/.") > 15 * 24 * 3600) {
          system("rm -rf $path");
        }
      } else {
        // another dir like scotese, go check inside that
        removeOldHashDirs($path);
      }
    }
  }
  removeOldHashDirs(dirname(__FILE__) . "/pygplates/livedata");

  // we already had the folder up above, so just wait for image...
  if (!$initial_creation_outdir) {
    $count = 0;
  }

  // Run pygplates if either
  // a) we had to make the hash folder because it didn't exist, or
  // b) we timed out (try again)
  if ($initial_creation_outdir) {
    switch($_REQUEST["selectModel"]) {
      case "Default":
        $cmd = "cd pygplates && ./master_run_pygplates_pygmt.py ".$_REQUEST['recondate']." $outdirname 2>&1";
        $hello = exec($cmd, $output, $ending);

        // Sabrina's debugging
        // echo "Python returned ($ending): <pre>";
        // print_r($output);

        if($ending > 0) {
          echo "Python returned ($ending): <pre>";
          print_r($output);
          echo " And here is the command that generated it: $cmd</pre>";
        }
        break;
      case "Marcilly":
        $cmd = "cd pygplates && ./MarcillyModel.py ".$_REQUEST['recondate']." $outdirname 2>&1";
        $hello = exec($cmd, $output, $ending);

        // Sabrina's debugging
        // echo "Python returned ($ending): <pre>";
        // print_r($output);

        if($ending > 0) {
          echo "Python returned ($ending): <pre>";
          print_r($output);
          echo " And here is the command that generated it: $cmd</pre>";
        }
        break;
      case "Scotese":
        $cmd = "cd pygplates && ./ScoteseModel.py ".$_REQUEST['recondate']." $outdirname 2>&1";
        $hello = exec($cmd, $output, $ending);

        // Sabrina's debugging
        // echo "Python returned ($ending): <pre>";
        // print_r($output);

        if($ending > 0) {
          echo "Python returned ($ending): <pre>";
          print_r($output);
          echo " And here is the command that generated it: $cmd</pre>";
        }
        break;

        // TODO: Why do we need a second break here???
        break;
    }
  } ?>

  <div id="reconImg" align="center"> <?php
  if($_REQUEST["searchtype"] == "Period" && $_REQUEST["filterstage"] != "All") { ?>
    <figcaption style="text-align: center; font-size: 45px;"> Reconstruction for <?=$_REQUEST[recondate_description]?> of <?= $_REQUEST["filterstage"] ?> </figcaption><?php
  } else if($_REQUEST["searchtype"] == "Period") { ?>
    <figcaption style="text-align: center; font-size: 45px;"> Reconstruction for <?=$_REQUEST[recondate_description]?> of <?= $_REQUEST["filterperiod"] ?> </figcaption><?php
  }

  if(file_exists($outdirname_php."/final_image.png")) { ?>
    <div>
      <a href="<?=$outdirname_php?>/final_image.png">
        <img src="<?=$outdirname_php?>/final_image.png" style="text-align:center" width="80%"/>
      </a>
      <br/><br/>
      A very special thanks to the excellent
      <a href="https://gplates.org">GPlates</a> and their
      <a href="https://www.gplates.org/docs/pygplates/pygplates_getting_started.html">pyGPlates</a> software as well as
      <a href="https://www.pygmt.org/latest/">PyGMT</a> which work together to create these images.
    </div> <?php
  } else {
    // createImage();
    echo "No available reconstruction image";
  }
} else if($_REQUEST["generateImage"] != "2") {
  // User selection of reconstruction model
  if(isset($_REQUEST["agefilterstart"])) {
    $baseraw = $_REQUEST["agefilterstart"];
    $basepretty = number_format($baseraw, 2);
  } else {
    $baseraw = $_REQUEST["recondate"];
    $basepretty = number_format($baseraw, 2);
  }
  $topraw = $_REQUEST["agefilterend"];
  $toppretty = number_format($topraw, 2);

  if ($_REQUEST["searchtype"] == "Period") {
    $middleraw = ($_REQUEST["agefilterstart"] + $_REQUEST["agefilterend"]) / 2.0;
    $middlepretty = number_format($middleraw, 2);
    // $useperiod = true;

    if ($_REQUEST["filterstage"] && $_REQUEST["filterstage"] != "All") {
      $name = $_REQUEST["filterstage"];
    } else {
      $name = $_REQUEST["filterperiod"];
    }
  }
}
?>
