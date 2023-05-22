<?php 
//include_once('./formationInfo.php'); // contains the fields we use to display formations
//include_once('./mapPackBrowser.php'); // contains the diamond geoJSON code necessary to process geoJSON data here
//include_once('makeReconstruction.php'); // prepares the geoJSON for format required by pygplates and pygmt
//include_once('makeButtons.php');
include_once('navBar.php');
include_once("TimescaleLib.php");






// This part is setting up a user interface so users can search up formations by id (helps in testing process)
?> <form action="macrostratparse.php" method="get" id = 'macrostratsearch'>
      <input type = "search" id="query" name="SearchFm" placeholder="Enter Formation id...">
      <button>Submit</button>
</form> 
    <select id = "searchtype" name = "searchtypelist" form= "macrostratsearch">
        <option value = "formation name"> Formation Name </option> 
        <option value = "formation id"> Formation Id </option>
        <option value = "formation age"> Formation Age </option>
        <option value = "date range"> Date Range </option>
    </select> 
<script>
    /*
    function addAgeBoxes(){
      var dropdown = document.getElementById("searchtype");
      var query = document.getElementById("query");
      var selectedOption = dropdown.options[dropdown.selectedIndex].value;
      var textboxesDiv = document.getElementById("myTextboxes");

      if (selectedOption === "Formation Age" || selectedOption == "formation age") {
        query.style.display = 'block';
        var textbox1 = document.createElement("input");
        textbox1.type = "text";
        textbox1.name = "textbox1";
        textbox1.placeholder = "Top Age";
        textbox1.form = 'macrostratsearch'
        textboxesDiv.appendChild(textbox1);

        var textbox2 = document.createElement("input");
        textbox2.type = "text";
        textbox2.name = "textbox2";
        textbox2.placeholder = 'Bottom Age';
        textboxesDiv.appendChild(textbox2);
      } else {
        textboxesDiv.innerHTML = "";
      }
    }
    */
</script>
<?php 
echo "<pre>";
print_r($_REQUEST);
echo "</pre>";
// initializing how we setup and extract information from the appropriate url with macrostrat 
$id = $_REQUEST["SearchFm"];
$searchType = $_REQUEST["searchtypelist"];

global $lithologyNames;
$lithologyNames = array(
    "dolomite-limestone" => "Dolomitic limestone",
    "lime_mudstone" => "Limestone",
    "sandstone" => "Sandstone",
    "quartz arenite" => "Sandstone",
    "litharenite" => "Coarse-grained sandstone",
    "sand"=> "Sandstone",
    "siltstone" => "Siltstone",
    "silt" => "Siltstone",
    "dolomitic siltstone" => "Dolomite", 
    "shale" => "Claystone",
    "limestone" => "Limestone",
    "dolomite" => "Dolomite",
    "conglomerate" => "Aus conglomerate",
    "carbonate"=> "Limestone",
    "dolomite-mudstone" => "Dolomite",
    "dolostone" => "Dolomite",
    "mudstone" => "Sandy_claystone",
    "sandy-dolomite" => "Sandy limestone",
    "quartzite" => "Sandstone",
    "halite" => "Halite",
    "basalt" => "Lava",
    "rhyolite"=> "Lava",
    "andesite" => "Lava", // lava
    "till" => "Glacial till",
    "loess" => "Siltstone",
    "calcareous ooze" => "Chalk",
    "chalk" => "Chalk",
    "gravel" => "Aus conglomerate", // doesn't have a direct translation in lookup table in dropbox (temporary translation)
    "plutonic" => "Granitic",
    "granite" => "Granitic",
    "clay" => "Claystone",
    "syenite" => "Volcanics",
    "tuff" => "Volcanic_ash", //doesn't have a direct translation in lookup table in dropbox (temporary translation)
    "volcanic" => "Volcanics",
    "metamorphic" => "Gneiss",
    "volcaniclastic" => "Volcanic_ash",
    "migmatite" => "Gneiss",
    "gneiss" => "Gneiss",
    "tonalite" => "Granitic",
    "granodiorite" => "Granitic",
    "monzonite" => "Granitic", //doesn't have a direct translation in lookup table in dropbox (temporary translation)
    "argillite" => "Claystone"
);

// custom function that usort will use to sort each formation's lithologies 
/*
function cmp($a, $b){
    if ($a == $b){
        return 0;
    }
    return ($a < $b) ? -1 : 1;
}
*/

// this function should take in the lithology percentages and output a pattern to be displayed on the reconstruction 

function getLithPattern($percentages, $environs){
    // extracting necessary information for pattern name determination 
    //$liths = $percentages["properties"]["lith"]; // extracting lithologies for figuring out lithology names
    
    $sym = "";
    //$environs = $percentages["properties"]["environ"]; // extracting environments as that combined with lithologies gets used for names
    //array_multisort(array_column($liths, $liths["prop"]), SORT_ASC, $liths); # sorts lithologies by greatest percentage to least
    usort($percentages, function($a, $b){
        return $b['prop'] <=> $a['prop'];
    });
    /*
    echo "<pre>";
    print_r($percentages);
    echo "</pre>";
    */
    foreach($percentages as $lith){
        
        if($lith["name"] == "sandstone"){
           if($environs[0]["type"] == "dune" || $environs[0]["type"] == "marine" || $environs[0]["type"] == "shore"){
              return "Sandstone";
           } 
           if($environs[0]["type"] == "loess"){
              return "Siltstone";
           }
        }
        if($lith["name"] == "congolomerate"){
           if($environs[0]["type"] == "fluvial"){
              return "Aus conglomerate";
           }
        }
        // corresponds to code 711
        if($lith["name"] == "tuff"){
            return "Volcanic_ash";
        }
        // if not one of the special cases then just use the top lithology name 
        global $lithologyNames;
        $sym = $lithologyNames[$lith["name"]];


        if (isset($sym)){
            return $sym;
        }
        if($lith["type"] == "volcanic"){
            return "Volcanics";
        }
    }
    if (!isset($sym)){
        return "Unknown";
    }
    return $sym;
}


// bascially this function exists to remove the "properties" part of the geojson returned from macrostrat's api and adds the 
// necessary parts in for the pygplates/pygmt script to work 
// $geojsonArray: the geojsons that need to be looped through to remove the properties part 
// $lith: contains the lithology extracted by the unit rather than the column liths
// $environ: contains the environ extracted by the unit rather than the column liths 
function prepareGeojsonProperties($allgeoJSONs, $fmdata, $liths, $environ){
    /*
    for($i = 0; $i < sizeof($allgeoJSONs["features"]); $i++){
        unset($allgeoJSONs["features"][$i]["properties"]);
    }
    */
    //$allLiths = array();
    //$lithologyPatterns = array();

    $ultimateLith = getLithPattern($liths, $environ);
    for($i = 0; $i < sizeof($allgeoJSONs["features"]); $i++){
        unset($allgeoJSONs["features"][$i]["properties"]);
        $processedName = str_replace('%20', '_', $fmdata["name"]);
        $allgeoJSONs["features"][$i]["properties"]["name"] = $processedName;
        $allgeoJSONs["features"][$i]["properties"]["pattern"] = $ultimateLith;
        /*
        $liths = $allgeoJSONs["features"][$i]["properties"]["lith"]; // extracts the lithologies of the particular columns its in 
        unset($allgeoJSONs["features"][$i]["properties"]);
        $allgeoJSONs["features"][$i]["properties"]["name"] = $fmdata["name"];
        //$understandingLith = getLithPattern($allgeoJSONs["features"][$i]["properties"]);
        //echo $understandingLith;
        $allgeoJSONs["features"][$i]["properties"]["pattern"] = getLithPattern( $liths);
        $allLiths = $allLiths + $liths;
        array_push($lithologyPatterns, $allgeoJSONs["features"][$i]["properties"]["pattern"]);
        */
    }
    return $allgeoJSONs; //array($allgeoJSONs, $allLiths);
}

function fillSingleFormationInfo($formationData){
    // if multiple formations are returned alter the part where the zero index is used 
    // to other numbers
    if (isset($formationData["success"]["data"]["features"][0]["properties"]["Fm"])){
      $fmdata["name"] = $formationData["success"]["data"]["features"][0]["properties"]["Fm"]. " Fm";
    } else if (isset($formationData["success"]["data"]["features"][0]["properties"]["Gp"])) {
      $fmdata["name"] = $formationData["success"]["data"]["features"][0]["properties"]["Gp"]. " Gp";
    }

//parsing in the bottom age 
    if (isset($formationData["success"]["data"]["features"][0]["properties"]["b_age"])){
      $fmdata["beg_date"] = $formationData["success"]["data"]["features"][0]["properties"]["b_age"];
    }

// parsing in the top age
    if (isset($formationData["success"]["data"]["features"][0]["properties"]["t_age"])) {
      $fmdata["end_date"] = $formationData["success"]["data"]["features"][0]["properties"]["t_age"];
    }
    return $fmdata;
}

// This function is for extracting the column representing a particular formation when searching by Formation Name ONLY 
function extractGeoJSON($indivFormation, $fmdata){
    $colId = $indivFormation['col_id'];
    $lith = $indivFormation['lith'];
    $environ = $indivFormation['environ'];
    $url = "https://macrostrat.org/api/columns?col_id=".$colId. "&format=geojson";
    $colgeoJSON = json_decode(file_get_contents($url), true);
    $colgeoJSON = prepareGeojsonProperties($colgeoJSON['success']['data'], $fmdata, $lith, $environ);
    return array($colgeoJSON["features"][0], $lith); // returns the geojson and lithology pattern
}

// depending on the search type the url we extract information from will be different 
// for example if search type is by single formation id it'll have format of https://macrostrat.org/api/units?unit_id=___&format=geojson&response=long
// while if search type is by formation name we'll extract information from two links with following formats: 
// https://macrostrat.org/api/units?strat_name=____&response=long
// https://macrostrat.org/api/columns?col_id=____&format=geojson

// $searchType: indicates whether we'll extract information by formation id or by formation name
// $id: indicates the specific formation id (a number) or formation name (a name without fm after it)
// returns: geojson to be sent directly to pygplates/pygmt script, array fmdata (contains name of the formation, 
// top age, bottom age, and other info), and array lith which contains percentages that are responsible for determining 
// the lithology pattern a formation gets filled with
function setUpSearch($searchType, $id, $fmdata){
    if($searchType == "Formation Id" || $searchType == "formation id"){
        $url = "https://macrostrat.org/api/units?unit_id=". $id. "&format=geojson&response=long";
        $formationData = json_decode(file_get_contents($url), true);
        // want to quickly terminate if there is no geoJSOn associated with a particular id 
        if(empty($formationData["success"]["data"]["features"])){
            return array("No formations and reconstructions available for id number ". $id);
        }
        $lith = $formationData["success"]["data"]["features"][0]["properties"]["lith"];
        $environ = $formationData["success"]["data"]["features"][0]["properties"]["environ"];
        $fmdata = fillSingleFormationInfo($formationData, $fmdata); // fills in the fmdata array necessary for reconstructions/other functions
        $stratId = $formationData["success"]["data"]["features"][0]["properties"]["strat_name_id"]; // extracts strat id so we can get the areal extent with column ids
        $arealExtentUrl = "https://macrostrat.org/api/columns?strat_name_id=". $stratId. "&format=geojson_bare&response=long";
        $arealExtent = json_decode(file_get_contents($arealExtentUrl), true);
        $arealExtentCleaned = prepareGeojsonProperties($arealExtent, $fmdata, $lith, $environ);
        return array($arealExtentCleaned, $fmdata, $lith);

    } else if ($searchType == "Formation Name" || $searchType == "formation name") {
        $id_urlProcessed = str_replace(' ', '_', $id); // if formation has a
        $columnGeoJSONs = array();
        $url = "https://macrostrat.org/api/units?strat_name=". $id_urlProcessed."&response=long"; // searching by strat_name looks laterally across all columns to retreive formation names/
        $formationData = json_decode(file_get_contents($url), true); // performs the GET request for everything meeting formation name criteria
        // following part will collect all of the bottom ages and take the median of them 
        if($formationData["error"]){
            return array("Formation ". $id. " not found");
        }
        $bottomAges = array();
        for($i = 0; $i < sizeof($formationData["success"]["data"]); $i++){
            array_push($bottomAges, $formationData["success"]["data"][$i]["b_age"]);
        }
        sort($bottomAges);
        $length = count($bottomAges);
        if($length % 2 == 0){
            $fmdata['beg_date'] = ($bottomAges[($length/2)-1] + $bottomAges[$length/2])/2;
        } else {
            $fmdata['beg_date'] = $bottomAges[floor($length/2)];
        }
        $fmdata['name'] = $id_urlProcessed;

        // creating the list of geojson features to be reconstructed 
        $allFormations = $formationData["success"]["data"]; // all the formations to loop through and compile column geojsons and lithologies for 
        $geoJSONCollection = array(
            "type" => "FeatureCollection",
            "features" => array()
        );
        $allLiths = array();
        for($i = 0; $i < sizeof($allFormations); $i++){
            #$allgeoJSONs .= implode(',', extractGeoJSON($allFormations[$i]));
            $postprep = extractGeoJSON($allFormations[$i], $fmdata);
            $allgeoJSONs = $postprep[0];
            $lith_used = $postprep[1];
            array_push($geoJSONCollection["features"], $allgeoJSONs);
            array_push($allLiths, $lith_used);
        }
        //$postprep = prepareGeojsonProperties($geoJSONCollection, $fmdata);
        //$geoJSONCollection = $postprep[0];
        //$allLiths = $postprep[1];
        return array($geoJSONCollection, $fmdata, $allLiths);
        
    } else if ($searchType == 'Formation Age' || $searchType == 'formation age'){
        // access all of the formation data meeting the proper age specifications
        $url = "https://macrostrat.org/api/units?age_top=".$id."&age_bottom=".$id. "&response=long";
        $formationData = json_decode(file_get_contents($url), True);
        $formationData = $formationData['success']['data'];
        $totalFormations = sizeof($formationData);
        // start creating the basic geojson collection to eventually be reconstructed by pygplates and pygmt 
        $geoJSONCollection = array(
            "type" => "FeatureCollection",
            "features" => array()
        );
        $allLiths = array();
        // remove formations that don't have names 
        for($i = 0; $i < $totalFormations; $i++){
            if($formationData[$i]['unit_name'] == 'unnamed' || $formationData[$i]['unit_name'] == 'Unnamed'){
                unset($formationData[$i]);
                continue;
            }
            $fmdata['name'] = $formationData[$i]['unit_name'];
            $fmdata['beg_date'] = $id;
            $formationProcessed = extractGeoJSON($formationData[$i], $fmdata);
            $formationgeoJSON = $formationProcessed[0];
            $lith_used = $formationProcessed[1];
            array_push($geoJSONCollection["features"], $formationgeoJSON);
            array_push($allLiths, $lith_used);
        }
        return array($geoJSONCollection, $fmdata, $allLiths);
        
    }
    else if ($searchType == "Date Range" || $searchType == "date range"){
        $age_bottom = $id + 10;
        $url = "https://macrostrat.org/api/units?age_top=".$id."&age_bottom=".$age_bottom. "&response=long";
        $formationData = json_decode(file_get_contents($url), True);
        $formationData = $formationData['success']['data'];
        $totalFormations = sizeof($formationData);
        echo $totalFormations;
        // start creating the basic geojson collection to eventually be reconstructed by pygplates and pygmt 
        $geoJSONCollection = array(
            "type" => "FeatureCollection",
            "features" => array()
        );
        $allLiths = array();
        // remove formations that don't have names 
        for($i = 0; $i < $totalFormations; $i++){
            if($formationData[$i]['unit_name'] == 'unnamed' || $formationData[$i]['unit_name'] == 'Unnamed'){
                unset($formationData[$i]);
                continue;
            }
            $fmdata['name'] = $formationData[$i]['unit_name'];
            $fmdata['beg_date'] = $age_bottom;
            $formationProcessed = extractGeoJSON($formationData[$i], $fmdata);
            $formationgeoJSON = $formationProcessed[0];
            $lith_used = $formationProcessed[1];
            array_push($geoJSONCollection["features"], $formationgeoJSON);
            array_push($allLiths, $lith_used);
        }
        return array($geoJSONCollection, $fmdata, $allLiths);
    }
    else {
        $url = "Not Found";
        return $url;
    }
}

function createReconstruction($searchType, $allgeoJSONs, $fmdata){
    if($searchType == "Formation Id" || $searchType == "formation id"){
        //$outdirhash = 'pottsville';
        $outdirhash = $fmdata['name'];
        $outdirname_php = './pygplates/livedata/default/' .$outdirhash; // we want to use this when wri
        $outdirname = './livedata/default/'. $outdirhash; // reconstruction files think "current directory" is the pygplates one 
        // create the output directory with the appropriate recon.geojson file 
        // code taken from general.php 
        if (!file_exists($outdirname_php)) {
            $initial_creation_outdir = true;
            mkdir($outdirname_php, 0777, true);
        }
        $reconfilename = "$outdirname_php/recon.geojson";
        file_put_contents($reconfilename, json_encode($allgeoJSONs));
        $finalPath = $outdirname_php. "/final_image.png";
        $cmd = "cd pygplates && ./ScoteseModel.py ".$fmdata['beg_date']." $outdirname";
        $hello = exec($cmd, $output, $ending);
        return $finalPath; 
    }
    else if ($searchType == "Formation Name" || $searchType == "formation name") {
        $outdirhash = str_replace(' ', '%20', $id); 
        $outdirhash = $fmdata['name'];
        $outdirname_php = './pygplates/livedata/default/' .$outdirhash; // we want to use this when wri
        $outdirname = './livedata/default/'. $outdirhash; // reconstruction files think "current directory" is the pygplates one
        // create the output directory with the appropriate recon.geojson file 
        // code taken from general.php 
        if (!file_exists($outdirname_php)) {
            $initial_creation_outdir = true;
            mkdir($outdirname_php, 0777, true);
        }
        $reconfilename = "$outdirname_php/recon.geojson";
        $writtenGeoJSON = json_decode(json_encode($allgeoJSONs), True);
        file_put_contents($reconfilename, json_encode($allgeoJSONs));
        $finalPath = $outdirname_php. "/final_image.png";
        //$fmdata['beg_date'] = 317.5;
        $cmd = "cd pygplates && ./ScoteseModel.py ".$fmdata['beg_date']." $outdirname";
        $hello = exec($cmd, $output, $ending);
        return $finalPath; 
    }
    else if ($searchType == "formation age" || $searchType == "date range"){
        $outdirhash = $fmdata['beg_date'];
        //$outdirhash = $fmdata['name'];
        $outdirname_php = './pygplates/livedata/default/' .$outdirhash; // we want to use this when wri
        $outdirname = './livedata/default/'. $outdirhash; // reconstruction files think "current directory" is the pygplates one
        // create the output directory with the appropriate recon.geojson file 
        // code taken from general.php 
        if (!file_exists($outdirname_php)) {
            $initial_creation_outdir = true;
            mkdir($outdirname_php, 0777, true);
        }
        $reconfilename = "$outdirname_php/recon.geojson";
        $writtenGeoJSON = json_decode(json_encode($allgeoJSONs), True);
        file_put_contents($reconfilename, json_encode($allgeoJSONs));
        $finalPath = $outdirname_php. "/final_image.png";
        $cmd = "cd pygplates && ./master_run_pygplates_pygmt.py ".$fmdata['beg_date']. " $outdirname ". "2>&1";
        //$hello = passthru($cmd, $output, $ending);
        //$hello = exec($cmd, $output, $ending);
        $hello = shell_exec($cmd);
        echo "<pre>";
        print_r($hello);
        echo "</pre>";
        return $finalPath; 
    }
}

// this function will take in the fmdata array and depending on what model is selected, it will 
// set up the appropriate model reconstruction by putting the md5 hashed directory under the right 
// reconstruction folder (i.e. default, marcilly, or scotese) and by writing the geojson to the appropriate
// place
function coordinateReconstructionModel($fmdata, $geojson){
    $geojson = json_encode($geojson);
    if ($_REQUEST["generateImage"]) {
        if($_REQUEST["generateImage"] == 1 && $_REQUEST["selectModel"] == "Marcilly"){
           $toBeHashed = $reconForm.$fmdata["beg_date"]["display"].$_REQUEST["selectModel"];
        }
        else if($_REQUEST["generateImage"] == 1 && $_REQUEST["selectModel"] == "Default"){    
            $toBeHashed = $reconForm.$fmdata["beg_date"]["display"];
        }
        else if($_REQUEST["generateImage"] == 1 && $_REQUEST["selectModel"] == "Scotese"){
          $toBeHashed = $reconForm.$fmdata["beg_date"]["display"].$_REQUEST["selectModel"];
        }
        $toBeHashed .= $_REQUEST["formation"]; //adds the formation name to the hash
        $outdirhash = md5($toBeHashed)."newest"; // md5 hashing for the output directory name 
    }
    echo $outdirhash;
    switch($_REQUEST["selectModel"]) {
        case  "Default": $outdirname = "livedata/default/$outdirhash"; break;
        case "Marcilly": $outdirname = "livedata/marcilly/$outdirhash"; break;
        case  "Scotese": $outdirname = "livedata/scotese/$outdirhash"; break;
        default:         $outdirname = "livedata/unknown/$outdirhash"; 
      }
  
      // and php is running one level up:
      $outdirname_php = "pygplates/$outdirname";
      //echo $outdirname_php;
      $initial_creation_outdir = false; // did we have to make the output hash directory name?
      if (!file_exists($outdirname_php)) {
        $initial_creation_outdir = true;
        //echo "Creating a new folder!!!";
        mkdir($outdirname_php, 0777, true);
      }
      
      $reconfilename = "$outdirname_php/recon.geojson";
      if (!file_exists($reconfilename)) {
        file_put_contents($reconfilename, $geojson);
      }
}


$reconstructionInfo = setUpSearch($searchType, $id, $fmdata);

// if the macrostrat api can't return any information, terminate the script there and don't render any image of any kind
if(sizeof($reconstructionInfo) == 1){
    exit($reconstructionInfo[0]);

}

include_once('./formationInfo.php');
$allgeoJSONs = $reconstructionInfo[0];
$fmdata_extract = $reconstructionInfo[1];
// the fmdata array gets imported from formationInfo.php and in order to create the reconstruction we need 
// $fmdata['name']['display'] and $fmdata['beg_date']['display']
$fmdata['name']['display'] = $fmdata_extract['name'];
$fmdata['beg_date']['display'] = $fmdata_extract['beg_date'];
//coordinateReconstructionModel($fmdata, $allgeoJSONs);
//include_once('./makeButtons.php'); // necessary for allowing user to select reconstruction model type and generating reconstruction 

$formationLith = $reconstructionInfo[2];


$finalPath = createReconstruction($searchType, $allgeoJSONs, $fmdata_extract);
?>
<!--<h1 style='text-align: center'> </h1> -->
    <h1 style='text-align:center'> <?=$id. " Reconstruction"?> </h1>
    <img src= <?=$finalPath ?> width = 1500>
<? 
