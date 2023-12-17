<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedModel = $_POST['model'];
    $geojson = $_POST['geojson'];
    //var_dump($geojson);
    $outdirname_php = generateModel($selectedModel, $geojson);
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
            $fileContent = file_get_contents("$outdirname_php" . "/reconstructed_geom.gmt");
            $formationNames = extractFormationNames($fileContent);
            //Need to get url links for formations
            session_start();
            $urlLinks = array();
            if (isset($_SESSION[$_POST['pageKey']])) {
                $urlLinks = $_SESSION[$_POST['pageKey']];
            } else {
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
                            // Move the region to the beginning of the array, likely other formations from same region
                            unset($regions[$key]);
                            array_unshift($regions, $region);     
                            break;
                        }
                    }
                }
                //Store in session so that other two models don't need to process again
                $_SESSION[$_POST['pageKey']] = $urlLinks;
            }
            //var_dump($urlLinks);
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
                            $y -= 2.5;
                        } else if ($mapType == 'Mollweide') {
                            $x += 10;
                            $y -= 35;
                        } else {
                            $x += 20;
                            $y -= 22;
                        }
                        //Rectangular models do not include colorbar at bottom, may be an error
                        if ($selectedModel == 'Scotese' && $mapType != 'Rectangular') {
                            $y -= 320;
                        }
                        $coordinates[] = ['x' => $x, 'y' => $y];
                    }
                }
                $data[$formationNames[$formationIndex]] = $coordinates;
                $formationIndex++;
            }
            //Build html to be appended to sight
            $imageHtml .= '</h1>';

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
            $imageHtml .= '<img src="' . $outdirname_php . '/final_image.png" usemap="#image-map-' . $selectedModel . '"/>';
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

        echo $imageHtml;
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

function generateModel($model, $geojson)
{
    $toBeHashed = $geojson . $_POST['beg_date'] . $model . $_POST['formation'];
    $outdirhash = md5($toBeHashed);

    switch ($model) {
        case "Default":
            $outdirname = "livedata/default/$outdirhash";
            break;
        case "Marcilly":
            $outdirname = "livedata/marcilly/$outdirhash";
            break;
        case "Scotese":
            $outdirname = "livedata/scotese/$outdirhash";
            break;
    }

    $outdirname_php = "pygplates/$outdirname";
    $initial_creation_outdir = false;

    if (!file_exists($outdirname_php)) {
        $initial_creation_outdir = true;
        mkdir($outdirname_php, 0777, true);
    }

    $reconfilename = "$outdirname_php/recon.geojson";
    if (!file_exists($reconfilename)) {
        file_put_contents($reconfilename, $geojson);
    }
    $imagefilename = "$outdirname_php/final_image.png";
    if (!file_exists($imagefilename)) {
        $initial_creation_outdir = true;
    }

    if (!$initial_creation_outdir) {
        $count = 0;
    }

    if ($initial_creation_outdir) {
        switch ($model) {
            case "Default":
                $cmd = 'cd pygplates && ./DefaultModel.py ' . $_REQUEST['beg_date'] . ' ' . $outdirname . ' 2>&1';
                $hello = exec($cmd, $output, $ending);
                // foreach ($output as $line) {
                //     echo $line . "<br>";
                // }
                break;
            case "Marcilly":
                $cmd = 'cd pygplates && ./MarcillyModel.py ' . $_REQUEST['beg_date'] . ' ' . $outdirname . ' 2>&1';
                $hello = exec($cmd, $output, $ending);
                break;
            case "Scotese":
                $cmd = 'cd pygplates && ./ScoteseModel.py ' . $_REQUEST['beg_date'] .  ' ' . $outdirname . ' 2>&1';
                $hello = exec($cmd, $output, $ending);
                // foreach ($output as $line) {
                //     echo $line . "<br>";
                // }
                break;
        }
    }
    return $outdirname_php;
}
?>