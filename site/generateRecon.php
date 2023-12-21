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
        if (time() - filemtime("$path/.") > 15 * 24 * 3600) {
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
    switch ($_REQUEST["selectModel"]) {
      case "Default":
        $cmd = "cd pygplates && ./DefaultModel.py " . $_REQUEST['recondate'] . " $outdirname 2>&1";
        $hello = exec($cmd, $output, $ending);

        // Sabrina's debugging
        // echo "Python returned ($ending): <pre>";
        // print_r($output);

        // if ($ending > 0) {
        //   echo "Python returned ($ending): <pre>";
        //   print_r($output);
        //   echo " And here is the command that generated it: $cmd</pre>";
        // }
        break;
      case "Marcilly":
        $cmd = "cd pygplates && ./MarcillyModel.py " . $_REQUEST['recondate'] . " $outdirname 2>&1";
        $hello = exec($cmd, $output, $ending);



        // Sabrina's debugging
        // echo "Python returned ($ending): <pre>";
        // print_r($output);

        // if ($ending > 0) {
        //   echo "Python returned ($ending): <pre>";
        //   print_r($output);
        //   echo " And here is the command that generated it: $cmd</pre>";
        // }
        break;
      case "Scotese":
        $cmd = "cd pygplates && ./ScoteseModel.py " . $_REQUEST['recondate'] . " $outdirname 2>&1";
        $hello = exec($cmd, $output, $ending);

        // Sabrina's debugging
        // echo "Python returned ($ending): <pre>";
        // print_r($output);

        // if ($ending > 0) {
        //   echo "Python returned ($ending): <pre>";
        //   print_r($output);
        //   echo " And here is the command that generated it: $cmd</pre>";
        // }
        break;
    }
  }

  function extractFormationNames($fileContent)
  {
    //Look for lines that start with @D(some digit) and get formation name
    preg_match_all('/@D\d+\|[^|]*\|[^|]*\|"([^"]+)"/', $fileContent, $matches);
    if (isset($matches[1])) {
      $formationNames = $matches[1];
    }
    return $formationNames;
  }



  ?>

  <div id="reconImg" style="text-align: center;">
    <?php
    if ($_REQUEST["searchtype"] == "Period" && $_REQUEST["filterstage"] != "All") { ?>
      <figcaption style="text-align: center; font-size: 45px;"> Reconstruction for
        <?= $_REQUEST['recondate_description'] ?> of
        <?= $_REQUEST["filterstage"] ?>
      </figcaption>
      <?php
    } else if ($_REQUEST["searchtype"] == "Period") { ?>
        <figcaption style="text-align: center; font-size: 45px;"> Reconstruction for
        <?= $_REQUEST['recondate_description'] ?> of
        <?= $_REQUEST["filterperiod"] ?>
        </figcaption>
      <?php
    }

    if (file_exists($outdirname_php . "/final_image.png")) {
      // Create HTML content for the image for individual button presses
      //Build command gmt mapproject and title
      //Get region, projection, and map type information
      $fileContent = file_get_contents($outdirname_php . "/region_and_projection.txt");
      $projection = '';
      $region = '';
      $mapType = '';
      preg_match('/Projection:\s*(.*)/i', $fileContent, $projectionMatch);
      preg_match('/Region:\s*(.*)/i', $fileContent, $regionMatch);
      preg_match('/Map Type:\s*(.*)/i', $fileContent, $mapTypeMatch);
      $projection = trim($projectionMatch[1]);
      $region = trim($regionMatch[1]);
      $mapType = trim($mapTypeMatch[1]);
      $imageHtml = '<div class="model" id="' . $_REQUEST["selectModel"] . '">';
      $cmd = '';
      switch ($_REQUEST["selectModel"]) {
        case "Default":
          $cmd = "cd " . $outdirname_php . " && gmt mapproject -R" . $region .
            " reconstructed_geom.gmt -J" . $projection . " -Di | gmt math STDIN -Ca 300 MUL RINT = pixel_coordinates.txt";
          break;
        case "Marcilly":
          $cmd = "cd " . $outdirname_php . " && gmt mapproject -R" . $region .
            " reconstructed_geom.gmt -J" . $projection . " -Di | gmt math STDIN -Ca 300 MUL RINT = pixel_coordinates.txt";
          break;
        case "Scotese":
          $cmd = "cd " . $outdirname_php . " && gmt mapproject -R" . $region .
            " reconstructed_geom.gmt -J" . $projection . " -Di | gmt math STDIN -Ca 300 MUL RINT = pixel_coordinates.txt";
          break;
      }
      exec($cmd, $output, $returnCode);
      if ($returnCode === 0) {
        //Get names of formations from reconstructed_geom.gmt
        $fileContent = file_get_contents("$outdirname_php" . "/reconstructed_geom.gmt");
        $formationNames = extractFormationNames($fileContent);
        //Need to get url links for formations
        $regions = [
          "https://chinalex.geolex.org",
          "https://indplex.geolex.org",
          "https://thailex.geolex.org",
          "https://vietlex.geolex.org",
          "https://nigerlex.geolex.org",
          "https://malaylex.geolex.org",
          "https://africalex.geolex.org",
          "https://belgiumlex.geolex.org",
          "https://mideastlex.geolex.org",
          "https://panamalex.geolex.org",
          "https://qatarlex.geolex.org",
          "https://southamerlex.geolex.org",
        ];
        foreach ($formationNames as $formationName) {
          foreach ($regions as $key => $region) {
            // Construct the API URL for searching the formation
            $api_url = "{$region}/searchAPI.php?searchquery=" . urlencode($formationName);
            $response_json = file_get_contents($api_url);
            // Decode the JSON response
            $response_data = json_decode($response_json, true);
            // Check if the response contains data related to the formation
            if (isset($response_data) && is_array($response_data) && count($response_data) > 0) {
              $urlLinks[$formationName] = $region;
              // Move the region to the beginning of the array
              unset($regions[$key]);
              array_unshift($regions, $region);
              $_SESSION[$_POST['pageKey']] = $urlLinks;
              break;
            }
          }
        }
        //Go through file and get pixels
        $fileContent = file_get_contents($outdirname_php . "/pixel_coordinates.txt");
        $coordinateSets = explode(">\n", trim($fileContent));
        $coordinateSets = array_filter($coordinateSets);
        $data = [];
        $formationIndex = 0;
        //gmt mapproject considers origin to be bottom left, while HTML map consideres origin to be top left, use imageSize to adjust
        $imageSize = getimagesize($outdirname_php . "/final_image.png");
        foreach ($coordinateSets as $coordinateSet) {
          $lines = explode("\n", $coordinateSet);
          $coordinates = [];
          for ($i = 0; $i < count($lines); $i++) {
            $coords = explode("\t", $lines[$i]);
            if (count($coords) == 2) {
              $x = intval($coords[0]);
              $y = $imageSize[1] - intval($coords[1]);
              //Need to adjust due to inset, boundaries, etc...
              if ($mapType == 'Polar') {
                $x += 2.5;
                $y -= 25;
              } else if ($mapType == 'Mollweide') {
                $x += 10;
                $y -= 35;
              } else {
                $x += 20;
                $y -= 22;
              }
              //Rectangular models do not include colorbar at bottom, may be an error
              if ($selectedModel == 'Scotese') {
                if ($mapType == 'Mollweide') {
                    $y -= 315;
                } else if ($mapType == "Polar") {
                    $y -= 295;
                }
            }
              $coordinates[] = ['x' => $x, 'y' => $y];
            }
          }
          $data[$formationNames[$formationIndex]] = $coordinates;
          $formationIndex++;
        }
        //Build html to be appended to sight
  
        $areaTags = [];
        foreach ($data as $name => $coordinates) {
          $areaTag = '';
          foreach ($coordinates as $coords) {
            $x = $coords['x'];
            $y = $coords['y'];
            $areaTag .= $x . ',' . $y . ',';
          }
          $areaTag = rtrim($areaTag, ',');
          $baseUrl = $urlLinks[$name] . "/displayInfo.php";
          $nameEncoded = urlencode($name);
          $url = "$baseUrl?formation=$nameEncoded";
          $dataAttributes = "data-tooltip-title='" . htmlspecialchars($name) . "'";
          $areaTags[] = '<area shape="poly" coords="' . $areaTag . '" href="' . $url . '" alt="' . $name . '" target="_blank" title="' . $name . '" ' . $dataAttributes . '">';
        }
        $imageHtml .= '<img src="' . $outdirname_php . '/final_image.png" usemap="#image-map-' . $_REQUEST["selectModel"] . '"/>';
        $imageHtml .= '<map name="image-map-' . $_REQUEST["selectModel"] . '">';
        $imageHtml .= implode("\n", $areaTags);
        $imageHtml .= '</map>';
        $imageHtml .= '</div>';
        $imageHtml .= '<br/><br/>';
      } else {
        $imageHtml .= '<a href="' . $outdirname_php . '/final_image.png">';
        $imageHtml .= '<img src="' . $outdirname_php . '/final_image.png"/>';
        $imageHtml .= '</a>';
        $imageHtml .= '</div>';
        $imageHtml .= '<br/><br/>';
      }

      $imageHtml .= '<br/><br/>
      A very special thanks to the excellent
      <a href="https://gplates.org">GPlates</a> and their
      <a href="https://www.gplates.org/docs/pygplates/pygplates_getting_started.html">pyGPlates</a> software as well as
      <a href="https://www.pygmt.org/latest/">PyGMT</a> which work together to create these images. ';

      echo $imageHtml; ?>
      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
      <script>
        /*
        * rwdImageMaps jQuery plugin v1.6
        *
        * Allows image maps to be used in a responsive design by recalculating the area coordinates to match the actual image size on load and window.resize
        *
        * Copyright (c) 2016 Matt Stow
        * https://github.com/stowball/jQuery-rwdImageMaps
        * http://mattstow.com
        * Licensed under the MIT license
        */
        ; (function ($) {
            $.fn.rwdImageMaps = function () {
                var $img = this;

                var rwdImageMap = function () {
                    $img.each(function () {
                        if (typeof ($(this).attr('usemap')) == 'undefined')
                            return;

                        var that = this,
                            $that = $(that);

                        // Since WebKit doesn't know the height until after the image has loaded, perform everything in an onload copy
                        $('<img />').on('load', function () {
                            var attrW = 'width',
                                attrH = 'height',
                                w = $that.attr(attrW),
                                h = $that.attr(attrH);

                            if (!w || !h) {
                                var temp = new Image();
                                temp.src = $that.attr('src');
                                if (!w)
                                    w = temp.width;
                                if (!h)
                                    h = temp.height;
                            }

                            var wPercent = $that.width() / 100,
                                hPercent = $that.height() / 100,
                                map = $that.attr('usemap').replace('#', ''),
                                c = 'coords';

                            $('map[name="' + map + '"]').find('area').each(function () {
                                var $this = $(this);
                                if (!$this.data(c))
                                    $this.data(c, $this.attr(c));

                                var coords = $this.data(c).split(','),
                                    coordsPercent = new Array(coords.length);

                                for (var i = 0; i < coordsPercent.length; ++i) {
                                    if (i % 2 === 0)
                                        coordsPercent[i] = parseInt(((coords[i] / w) * 100) * wPercent);
                                    else
                                        coordsPercent[i] = parseInt(((coords[i] / h) * 100) * hPercent);
                                }
                                $this.attr(c, coordsPercent.toString());
                            });
                        }).attr('src', $that.attr('src'));
                    });
                };
                $(window).resize(rwdImageMap).trigger('resize');

                return this;
            };
        })(jQuery);
        $('img[usemap]').rwdImageMaps();
    </script>
    <style>
      img[usemap] {
          width: 80%;
        }
    </style> <?php
    } else {
      // createImage();
      echo "No available reconstruction image";
    }
} else if ($_REQUEST["generateImage"] != "2" && !empty($_REQUEST['agefilterstart'])) {
  // User selection of reconstruction model
  if (isset($_REQUEST["agefilterstart"])) {
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