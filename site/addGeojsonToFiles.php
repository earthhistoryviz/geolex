<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $geojson = $data["geojson"];
    $beg_date = $data["beg_date"];
    $formation = $data["formation"];
    $toBeHashed = $beg_date . $geojson. $formation;   
    $outdirhash = md5($toBeHashed); // md5 hashing for the output directory name
    $paths = [
        "pygplates/livedata/default/$outdirhash",
        "pygplates/livedata/marcilly/$outdirhash",
        "pygplates/livedata/scotese/$outdirhash"
    ];
    foreach ($paths as $path) {
        // Check if the directory exists
        if (!file_exists($path)) {
            // Create the directory if it doesn't exist
            if (!mkdir($path, 0777, true)) {
                echo "Failed to create directory: $path";
                return;
            }
        }

        // Attempt to write the file
        if (file_put_contents("$path/recon.geojson", $geojson) === false) {
            echo "Failed to write file to: $path";
            return;
        }
    }
    echo $outdirhash;
}
?>
