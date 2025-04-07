<?php
if ($_REQUEST["generateImage"] == "1") {
    $timedout = false;

    function removeOldHashDirs($pathpfx)
    {
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
                // break;
        }
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
      } elseif ($_REQUEST["searchtype"] == "Period") { ?>
        <figcaption style="text-align: center; font-size: 45px;"> Reconstruction for
        <?= $_REQUEST['recondate_description'] ?> of
        <?= $_REQUEST["filterperiod"] ?>
        </figcaption>
      <?php
      }
    if (file_exists($outdirname_php . "/final_image.png")) {
        // Create HTML content for the image and send it back to the AJAX call
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
        $selectedModel = $_REQUEST["selectModel"];
        $imageHtml = '<div class="model" id="' . $selectedModel . '">';
        $imageHtml .= '<h1>';
        $cmd = "cd " . $outdirname_php . " && gmt mapproject -R" . $region .
            " reconstructed_geom.gmt -J" . $projection . " -Di | gmt math STDIN -Ca 300 MUL RINT = pixel_coordinates.txt";
        switch ($selectedModel) {
            case "Default":
                $imageHtml .= 'Reconstruction Model: GPlates Default (Meredith, Williams, et al., 2021)';
                break;
            case "Marcilly":
                $imageHtml .= 'Reconstruction Model: Continental flooding model (Marcilly, Torsvik et al., 2021)';
                break;
            case "Scotese":
                $imageHtml .= 'Reconstruction Model: Paleo-topograph (Chris Scotese, 2018)';
                break;
        }
        exec($cmd, $output, $returnCode);
        if ($returnCode === 0) {
            //Get names of formations from reconstructed_geom.gmt
            $formationNames = [];
            $geojsonDecoded = json_decode($recongeojson, true);
            foreach($geojsonDecoded["features"] as $formationA) {
                $formationNames[] = $formationA["properties"]["name"];
            }
            //Need to get url links for formations
            $urlLinks = array();
            set_time_limit(30);
            $regions = [
                "https://macrostrat.org/api",
                "https://chinalex.geolex.org",
                "https://indplex.geolex.org",
                "https://thailex.geolex.org",
                "https://vietlex.geolex.org",
                "https://nigerialex.geolex.org",
                "https://nigerlex.geolex.org",
                "https://malaylex.geolex.org",
                "https://africalex.geolex.org",
                "https://belgiumlex.geolex.org",
                "https://mideastlex.geolex.org",
                "https://panamalex.geolex.org",
                "https://qatarlex.geolex.org",
                "https://southamerlex.geolex.org",
            ];
            if ($_SERVER['HTTP_HOST'] == "dev") {
                $regions[] = "https://dev.geolex.org";
            }
            foreach ($formationNames as $formationName) {
                // if (time() >= ini_get('max_execution_time')) {
                //     // Handle the situation when the script runs too long
                //     echo "here";
                //     break;
                // }
                foreach ($regions as $key => $region) {
                    // Construct the API URL for searching the formation
                    $api_url = "";
                    if ($region == "https://macrostrat.org/api") {
                        $api_url = "{$region}/units?strat_name=" . urlencode($formationName);
                    } else {
                        $api_url = "{$region}/searchAPI.php?searchquery=" . urlencode($formationName);
                    }
                    $response_json = file_get_contents($api_url);
                    // Decode the JSON response
                    $response_data = json_decode($response_json, true);
                    // Check if the response contains data related to the formation
                    if (isset($response_data) && is_array($response_data) && count($response_data) > 0) {
                        if ($region == "https://macrostrat.org/api") {
                            if (isset($response_data["success"])) {
                                unset($regions[$key]);
                                array_unshift($regions, $region);
                                break;
                            }
                        } else {
                            $urlLinks[$formationName] = $region;
                            // Move the region to the beginning of the array, likely other formations from same region
                            unset($regions[$key]);
                            array_unshift($regions, $region);
                            break;
                        }
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
                            $y -= 92;
                        } elseif ($mapType == 'Mollweide') {
                            $x += 10;
                            $y -= 35;
                        } else {
                            $x += 20;
                            $y -= 22;
                        }
                        //Rectangular models do not include colorbar at bottom, may be an error
                        if ($selectedModel == 'Scotese') {
                            if ($mapType != 'Rectangular') {
                                $y -= 315;
                            }
                            if ($mapType == 'Polar') {
                                $y += 85;
                            }
                        }
                        $coordinates[] = ['x' => $x, 'y' => $y];
                    }
                }
                $data[] = array(
                    "name" => $formationNames[$formationIndex],
                    "coordinates" => $coordinates
                );
                $formationIndex++;
            }

            //Build html to be appended to sight
            $imageHtml .= '</h1>';
            $areaTags = [];
            foreach ($data as $formationA) {
                $areaTag = '';
                $coordinates = $formationA["coordinates"];
                foreach ($coordinates as $coords) {
                    $x = $coords['x'];
                    $y = $coords['y'];
                    $areaTag .= $x . ',' . $y . ',';
                }
                $areaTag = rtrim($areaTag, ',');
                $name = $formationA["name"];
                if (isset($urlLinks[$name])) {
                    $baseUrl = $urlLinks[$name] . "/formations";
                    $nameEncoded = urlencode($name);
                    $url = "$baseUrl/$nameEncoded";
                    $areaTags[] = '<area shape="poly" coords="' . $areaTag . '" href="' . $url . '" alt="' . $name . '" target="_blank" title="' . $name . '">';
                } else {
                    $areaTags[] = '<area shape="poly" coords="' . $areaTag . '" alt="' . $name . '" target="_blank" title="' . $name . '">';
                }
            }
            $imageHtml .= '<img src="/' . $outdirname_php . '/final_image.png" usemap="#image-map-' . $selectedModel . '"/>';
            $imageHtml .= '<map name="image-map-' . $selectedModel . '" width=' . $imageSize[0] . ' height=' . $imageSize[1] . '>';
            $imageHtml .= implode("\n", $areaTags);
            $imageHtml .= '</map>';
            $imageHtml .= '</div>';
            $imageHtml .= '<br/><br/>';
        } else {
            $imageHtml .= '</h1>';
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
      <script src="/index.js"></script>
      <script>
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
} elseif ($_REQUEST["generateImage"] != "2" && !empty($_REQUEST['agefilterstart'])) {
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