<?php
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
            // Check how old it is, delete if older than 15 days
            // You need the /. on the end since the hash is a dir
            if (time() - filemtime("$path/.") > 15 * 24 * 3600) {
                system("rm -rf $path");
            }
        } else {
            // Another dir like scotese, go check inside that
            removeOldHashDirs($path);
        }
    }
}
removeOldHashDirs("./pygplates/livedata");

$outdirhash = $_GET["outdirhash"];
$fileContent = file_get_contents("pygplates/livedata/default/$outdirhash/recon.geojson");
$formationNames = [];
$geojson = json_decode($fileContent, true);
foreach($geojson["features"] as $feature) {
    if (isset($feature["properties"]["name"])) {
        $formationNames[] = $feature["properties"]["name"];
    }
}

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
    //     echo "Here";
    //     // Handle the situation when the script runs too long
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

$pageKey = session_id() . '_' . uniqid();
$models = ["Default", "Marcilly", "Scotese"];
?>

<!DOCTYPE html>
<html>
<title>All Reconstruction Models</title>

<head>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="index.js"></script>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            text-align: center;
        }

        img[usemap] {
            width: 90%;
        }

        #loading-box {
            background-color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #E67603;
            width: 300px;
            height: 70px;
            border-radius: 10px;
            box-shadow: 5px 5px 10px rgba(0, 0, 0, 0.3);
        }

        #loading-container {
            display: flex;
            justify-content: center;
            padding-bottom: 20px;
            height: 100%;
        }

        #loading-text {
            color: #E67603;
            font-size: 20px;
            margin-left: 20px;
        }

        body p {
            font-size: 30px;
        }

        .ui-tooltip {
            background-color: white;
            color: #E67603;
            border: 3px solid #E67603;
            padding: 10px;
        }
    </style>

<body>
    <div id="image-container">
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const modelsJ = <?= json_encode($models) ?>;
                const urlParams = new URLSearchParams(window.location.search);
                const begDate = urlParams.get('beg_date');
                const outdirhash = <?= json_encode($outdirhash) ?>;
                const pageKey = <?= json_encode($pageKey) ?>;
                const formationNames = <?= json_encode($formationNames) ?>;
                const urlLinks = <?= json_encode($urlLinks) ?>;
                let currentIndex = 0;
                let loadingTextInterval;

                function generateNextImage() {
                    if (currentIndex >= modelsJ.length) {
                        // All models generated
                        const mapElements = document.getElementsByTagName('map');
                        let imageHtml = '';
                        if (mapElements.length > 0) {
                            imageHtml = '<div>';
                            imageHtml += 'A very special thanks to the excellent';
                            imageHtml += ' <a href="https://gplates.org">GPlates</a> and their';
                            imageHtml += ' <a href="https://www.gplates.org/docs/pygplates/pygplates_getting_started.html">pyGPlates</a> software as well as';
                            imageHtml += ' <a href="https://www.pygmt.org/latest/">PyGMT</a> which work together to create these images.';
                            imageHtml += '</div>';
                        } else {
                            imageHtml = "No available reconstruction image";
                        }
                        $('#image-container').append(imageHtml);
                        clearInterval(loadingTextInterval);
                        $('#loading-box').remove();
                        return;
                    }

                    const model = modelsJ[currentIndex];
                    $.ajax({
                        url: 'makeImageMap.php',
                        method: 'POST',
                        data: { model: model, beg_date: begDate, pageKey: pageKey, outdirhash: outdirhash, formationNames: formationNames, urlLinks: urlLinks },
                        success: function (data) {
                            $('#image-container').append(data);
                            $('img[usemap]').rwdImageMaps();
                            currentIndex++;
                            generateNextImage();
                        }
                    });
                }

                // Start generating images
                generateNextImage();

                function animateLoadingText() {
                    const loadingText = document.getElementById("loading-text");
                    const dots = ['.', '..', '...'];
                    let dotIndex = 0;

                    loadingTextInterval = setInterval(function () {
                        loadingText.textContent = "Loading reconstruction" + dots[dotIndex] + " This could take up to a minute. It was a long time ago.";
                        dotIndex = (dotIndex + 1) % dots.length;
                    }, 1000);
                }

                // Call the animation function
                animateLoadingText();
            });
        </script>
    </div>
    <div id="loading-container">
        <div id="loading-box">
            <img src="noun_Earth_2199992.svg" alt="Loading Image" width="50" height="50">
            <p id="loading-text">Loading reconstruction... This could take up to a minute. It was a long time ago.</p>
        </div>
    </div>
</body>

</html>