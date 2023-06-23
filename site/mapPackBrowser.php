<!DOCTYPE html>
<html>
<body>
<?php 
error_reporting(E_ALL);
include_once("navBar.php");
include_once("SearchBar.php");
include_once("SqlConnection.php");
include_once("TimescaleLib.php");



if (!$_SESSION["loggedIn"]) {
    echo "ERROR: You must be logged in to access this page.";
    exit(0);
}

?>
<br><br> 

<form action="" method="post" enctype="multipart/form-data">
    Select the Map Pack to upload:
    <span style="color:red;" class="req">*</span>
    <input type="file" name="mapPackDoc" id="mapPackDoc" required >
    <br><br>

    Select Data Pack to upload:
    <span style="color:red;" class="req">*</span>
    <input type="file" name="dataPackDoc" id="dataPackDoc" required >
    <br><br>
    <input type="submit" value="Upload MapPack and DataPack File" name="submit">
</form>


<?php
    //function to create a diamond shaped GeoJSON given the lat and long of a formation
    function createGeoJSONFromLatLon(float $lon, float $lat) {
        $geoJsonString = "{\"type\":\"Feature\",\"geometry\":{\"type\":\"MultiPolygon\",\"coordinates\":[[[";
        
        //The strings in php are annoying, they are hard to do arithmetic inside of quotes..

        //lat+1
        $lat_add1 = $lat + 1;
        //lat-1
        $lat_sub1 = $lat -1;
        //lon+1
        $lon_add1 = $lon + 1;
        //lon-1
        $lon_sub1 = $lon - 1;

        //adds to string a geoJSON that is converted
        //it makes a diamond shape, the original point is (lat,lon)
        //
        $geoJsonString .= "[$lon,$lat_add1],[$lon_sub1,$lat],[$lon,$lat_sub1],[$lon_add1,$lat],[$lon,$lat_add1]";

        $geoJsonString .= "]]]}}";

        return $geoJsonString;

    }

     //function to find if duplicate formation's exist 
    function duplicateFormationExist(array $objects, $property, $value): bool {
        foreach ($objects as $object) {
            if (property_exists($object, $property) && $object->{$property} === $value) {
                return true;
            }
        }
        return false;
    }

    function calculatePercentUp($timescale, $date) {
        $previous_scale = 0;
        
        foreach( $timescale as $scale) {
            $current_scale = $scale['base'];
            
            //echo $scale['base'] . "\n";

            if($date <= $current_scale) {
                //calculating the percent up
                $percentUp = ($current_scale - $date) / ($current_scale - $previous_scale);
                return round($percentUp, 2);
                
            }

            $previous_scale = $current_scale;
            
        }    

    }

    function calculateStage($timescale, $date) {
        $previous_scale = 0;
        
        foreach( $timescale as $scale) {
            $current_scale = $scale['base'];
            
            if($date <= $current_scale) {
                //calculating the stage
                return $scale['stage'];                
            }
            
        }    

    }

    function calculatePeriod($timescale, $date) {
        $previous_scale = 0;
        
        foreach( $timescale as $scale) {
            $current_scale = $scale['base'];
            
            if($date <= $current_scale) {
                //calculating the stage
                return $scale['period'];                
            }
            
        }    

    }

    function updateStage($timescale, $form) {

        $form->frac_upB = calculatePercentUp($timescale, $form->begin_date);
        $form->frac_upE = calculatePercentUp($timescale, $form->end_date);

        $form->beginning_stage = calculateStage($timescale, $form->begin_date);
        $form->end_stage = calculateStage($timescale, $form->end_date);   
        
        $form->period = calculatePeriod($timescale, $form->begin_date);   
    }

    if(isset($_POST['submit'])){        
        try {
            if (!file_exists('./uploads1')) {
                mkdir('./uploads1', 0777, true); //if the uploads1 folder does not exist, create it, set the permissions of the directory
            }
            $uploads_dir ='uploads1';
        
        
            // Undefined | Multiple Files | $_FILES Corruption Attack
            // If this request falls under any of them, treat it invalid.
            if (!isset($_FILES['mapPackDoc']['error']) || is_array($_FILES['mapPackDoc']['error'])) {
                throw new RuntimeException('Invalid parameters.');
            }
            if (!isset($_FILES['dataPackDoc']['error']) || is_array($_FILES['dataPackDoc']['error'])) {
                throw new RuntimeException('Invalid parameters.');
            }
        
                // You should also check filesize here.
            if ($_FILES['mapPackDoc']['size'] > 2097152 || $_FILES['dataPackDoc']['size'] > 2097152) {
                throw new RuntimeException('Exceeded filesize limit.');
            }
        
            //gets the 2 required files name and replace all spaces with underscores
            $mapPackName = str_replace(' ', '_', $_FILES["mapPackDoc"]["name"]);
            $dataPackName = str_replace(' ', '_', $_FILES["dataPackDoc"]["name"]);
        
            $mapPackFolderName = $_FILES["mapPackDoc"]["tmp_name"];
            $dataPackFolderName = $_FILES["dataPackDoc"]["tmp_name"];
        
            $mapPackNewPath = "./$uploads_dir/$mapPackName";
            $dataPackNewPath = "./$uploads_dir/$dataPackName";
        
        
            //if(move_uploaded_file($mapPackFolderName,"./$uploads_dir/$mapPackName"))
            if(!move_uploaded_file($mapPackFolderName, $mapPackNewPath)) {
                echo 'Failed to move MAPPACK File from temp file of PHP. FAILURE IN FILE Movement <br>';
                print_r(error_get_last());
            }
        
            //if(move_uploaded_file($dataPackFolderName,"./$uploads_dir/$dataPackName"))
            if(!move_uploaded_file($dataPackFolderName, $dataPackNewPath)) {
                echo 'Failed to move DATAPACK File from temp file of PHP. FAILURE IN FILE Movement <br>';
                print_r(error_get_last());
            }
        
        
            $cmd = "./mapPackParser.py ". "$mapPackNewPath" . " $dataPackNewPath" ." $mapPackName" ." 2>&1";
            $hello = exec($cmd, $output, $ending);
            print_r($output[0]);
        
        
            if($ending > 0) {
                echo "Python returned ($ending): <pre>";
                print_r($output);
                echo " And here is the command that generated it: $cmd</pre>";
              }

            //echo("Successfully Parsed the MapPack and DataPack... <br><br>"); 
            
            //The parsed JSON file is called formations.json in uploads1 directory
            $formationJSON = './uploads1/' . str_replace(" ", "", substr($mapPackName,0, 8)) .'_formations.json';
            
            $json_raw = file_get_contents($formationJSON);
            $full_formation_list = json_decode($json_raw, true); //contains all of the formations parsed from the datapack, including duplicate names         
            

            #create an array of the duplicate formations removed and the end_date "correct" by comparing all of the end age of the duplicate formations and choosing the newest date (oldest date = begin,  newest date = end because older rock is on the bottom... )
            $formation_set = [];

            foreach($full_formation_list as $formation_list) {
                // Compiling all geoJSON strings from the returned formations into recon.geojson
                
                if (!duplicateFormationExist($formation_set, 'name', $formation_list['name'])) {  //if it is NOT a duplicate formation
                    //add the formation to the formation_set array

                    // echo"<pre> Formation_List =";
                    // print_r($formation_list);
                    // echo"</pre>";

                    array_push($formation_set, (object)[
                        'name' => $formation_list['name'],
                        'begin_date' => $formation_list['begin_date'],
                        'end_date' => $formation_list['end_date'],
                        'lithology_pattern' => $formation_list['lithology_pattern'],
                        'formation_description' => $formation_list['formation_description'],
                        'province' => $formation_list['province'],
                        'long_lat' => "long: " .$formation_list['longitude'] ." lat: ".$formation_list['latitude'],
                        'GeoJSON' => createGeoJSONFromLatLon($formation_list['longitude'], $formation_list['latitude']),
                        'frac_upB' => "",
                        'frac_upE' => "",
                        'beginning_stage' => "",
                        'end_stage' => "",
                        'period' => "",
                        'type_locality' => "Column: " .$formation_list['column'],
                        ]);


                } else {

                    //loop through formation_set and get the formation object
                    //replace ONLY if new_begindate is greater than current begin_date 
                    //replace ONLY if new_ENDdate is less than current end_date

                    foreach($formation_set as $replace_formation) {
                        if($formation_list['name'] == $replace_formation->name) {
                            $beginDateInSet = $replace_formation->begin_date;
                            $endDateInSet = $replace_formation->end_date;

                            $beginDateInList = $formation_list['begin_date']; //new_begindate
                            $endDateInList = $formation_list['end_date']; //new_enddate

                            if($beginDateInSet < $beginDateInList) {
                                $replace_formation->begin_date = $formation_list['begin_date'];
                            }

                             if($endDateInSet > $endDateInList) {
                                $replace_formation->end_date = $formation_list['end_date'];
                             }
                        }

                    }

                    
                }           
            }

            // echo"<pre> UPDATED_FORMATION =";
            // print_r($formation_set);
            // echo"</pre>";




            // Load the default timescale
            $timescale = parseDefaultTimescale();
            if (!$timescale) {
                throw new RuntimeException("Failed to parse the default timescale.");
            } else {
                echo "<br> <br> Successfully parsed default timescale <br>";
                
                
                echo "end of timescale";
            }

            foreach($formation_set as $form) {
                              
                updateStage($timescale, $form);
             }
           
            
           
            timeScaleMapPackUpload($formation_set); //insert into SQL

        
        } catch (RuntimeException $e) {
            echo $e->getMessage();
        }
        
    }

?>

</body>
</html>