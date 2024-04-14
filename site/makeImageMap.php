<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedModel = $_POST['model'];
    $outdirhash = $_POST['outdirhash'];
    $begdate = $_POST['beg_date'];
    $outdirname_php = generateModel($selectedModel, $outdirhash, $begdate);
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
            $formationNames = $_POST["formationNames"];
            //Need to get url links for formations
            $urlLinks = $_POST["urlLinks"];
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
                            $y -= 70;
                        } else if ($mapType == 'Mollweide') {
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
                                $y += 60;
                            }
                        }
                        $coordinates[] = ['x' => $x, 'y' => $y];
                    }
                }
                $data[] = array (
                    "name" => $formationNames[$formationIndex],
                    "coordinates" => $coordinates
                );
                $formationIndex++;
            }

            //Build html to be appended to sight
            $imageHtml .= '</h1>';
            $areaTags = [];
            foreach ($data as $formation) {
                $areaTag = '';
                $coordinates = $formation["coordinates"];
                foreach ($coordinates as $coords) {
                    $x = $coords['x'];
                    $y = $coords['y'];
                    $areaTag .= $x . ',' . $y . ',';
                }
                $areaTag = rtrim($areaTag, ',');
                $name = $formation["name"];
                if (isset($urlLinks[$name])) {
                    $baseUrl = $urlLinks[$name] . "/formations";
                    $nameEncoded = urlencode($name);
                    $url = "$baseUrl/$nameEncoded";
                    $areaTags[] = '<area shape="poly" coords="' . $areaTag . '" href="' . $url . '" alt="' . $name . '" target="_blank" title="' . $name . '">';
                } else {
                    $areaTags[] = '<area shape="poly" coords="' . $areaTag . '" alt="' . $name . '" target="_blank" title="' . $name . '">';
                }
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

function generateModel($model, $outdirhash, $begdate) {
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

    $imagefilename = "$outdirname_php/final_image.png";
    if (!file_exists($imagefilename)) {
        $initial_creation_outdir = true;
    }

    if ($initial_creation_outdir) {
        switch ($model) {
            case "Default":
                $cmd = 'cd pygplates && ./DefaultModel.py ' . $begdate . ' ' . $outdirname . ' 2>&1';
                $hello = exec($cmd, $output, $ending);
                // foreach ($output as $line) {
                //     echo $line . "<br>";
                // }
                break;
            case "Marcilly":
                $cmd = 'cd pygplates && ./MarcillyModel.py ' . $begdate . ' ' . $outdirname . ' 2>&1';
                $hello = exec($cmd, $output, $ending);
                // foreach ($output as $line) {
                //     echo $line . "<br>";
                // }
                break;
            case "Scotese":
                $cmd = 'cd pygplates && ./ScoteseModel.py ' . $begdate .  ' ' . $outdirname . ' 2>&1';
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