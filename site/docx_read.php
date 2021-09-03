<?php 
?>
<html>
<head>
<title>Document Reader</title>
</head>
<body>
<?php
include_once("navBar.php");
include_once("SearchBar.php");
include_once("cleanupString.php");
include_once("TimescaleLib.php");

function docx_read($filename)
{
  $servername = "localhost";
  $username = "root";
  $password = "";
  $dbname = "myDB";
  $output = '';

  $conn = new mysqli($servername, $username, $password, $dbname);
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  $timescale = parseDefaultTimescale();

  $splitpattern = "*****************"; //Split pattern set by the document to differentiate between each of the formations

  function read_file_docx($filename) {   //FUNCTION to read information out of a docx and return it as a String
    $striped_content = '';
    $content = '';

    if (!$filename || !file_exists($filename)) return false;

    $zip = zip_open($filename);
    if (!$zip || is_numeric($zip)) return false;
    while ($zip_entry = zip_read($zip)) {
      if (zip_entry_open($zip, $zip_entry) == FALSE) continue;
      if (zip_entry_name($zip_entry) != "word/document.xml") continue;
      $content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
      zip_entry_close($zip_entry);
    }// end while
    zip_close($zip);

    $content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
    $content = str_replace('</w:r></w:p>', "\r\n", $content);
    $striped_content = strip_tags($content);
    $text = trim($striped_content);
    return '<p>' . preg_replace('/[\r\n]+/', '</p><p>', $text) . '</p>';
  }

  $command = "mammoth ".$filename;
  exec($command,$content);
  $splitcontent = explode($splitpattern, $content[0]);
  $skipFirstNFormations = 0;
  $count = 0;

  $numvars=0;
  $vars = array(
    array("name" => "name",             "clean" => true,  "matchoffset" => 0, "pattern" => "/([\s\w’'\-]+\s)(Gr|Fm|Group|Formation)/",                      "index" => $numvars++),
    array("name" => "period",           "clean" => true, "matchoffset" => 1, "pattern" => "/Period:\s*(.+)Age Interval(.+):/",                              "index" => $numvars++ ),
    array("name" => "age_interval",                      "matchoffset" => 1, "pattern" => "/Age Interval\s*\(Map column\):\s*(.+)Province:/",               "index" => $numvars++ ),
    array("name" => "province",         "clean" => true, "matchoffset" => 1, "pattern" => "/Province:\s*(.+)Type Locality and Naming:/",                    "index" => $numvars++ ),
    array("name" => "type_locality",                     "matchoffset" => 1, "pattern" => "/Type Locality and Naming:\s*(.+)Lithology and Thickness:/",     "index" => $numvars++,),
    array("name" => "lithology",                         "matchoffset" => 1, "pattern" => "/Lithology and Thickness:\s*(.+)Lithology-pattern:/",            "index" => $numvars++ ),
    array("name" => "lithology_pattern",                 "matchoffset" => 1, "pattern" => "/Lithology-pattern:\s*(.+)Relationships and Distribution:/",     "index" => $numvars++,),
    array("name" => "lower_contact",                     "matchoffset" => 1, "pattern" => "/Lower contact:\s*(.+)Upper contact:/",                          "index" => $numvars++ ),
    array("name" => "upper_contact",                     "matchoffset" => 1, "pattern" => "/Upper contact:\s*(.+)Regional (e|E)xtent:/",                    "index" => $numvars++ ), //if there is a problem parsing here try changing (e | E) to [eE]
    array("name" => "regional_extent",                   "matchoffset" => 1, "pattern" => "/Regional extent:\s*(.+)GeoJSON:/",                              "index" => $numvars++ ),
    array("name" => "geojson",      "cleanjson" => true, "matchoffset" => 1, "pattern" => "/GeoJSON:\s*(.+)Fossils:/",                                      "index" => $numvars++ ),
    array("name" => "fossils",                           "matchoffset" => 1, "pattern" => "/Fossils:\s*(.+)Age:/",                                          "index" => $numvars++ ),
    array("name" => "age",              "clean" => true, "matchoffset" => 1, "pattern" => "/Age:\s*(.+)Age (s|S)pan:/",                                     "index" => $numvars++ ),
    array("name" => "age_span",         "clean" => true, "matchoffset" => 1, "pattern" => "/Age [sS]pan:\s*(.+)Beginning stage:/",                         "index" => $numvars++ ),
    array("name" => "beginning_stage",  "clean" => true, "matchoffset" => 1, "pattern" => "/Beginning stage:\s*(.+)Fraction up in beginning stage:/",       "index" => $numvars++ ),
    array("name" => "frac_upB",         "clean" => true, "matchoffset" => 1, "pattern" => "/Fraction up in beginning stage:\s*(.+)Beginning date \(Ma\):/", "index" => $numvars++ ),
    array("name" => "beg_date",         "clean" => true, "matchoffset" => 1, "pattern" => "/Beginning date \(Ma\):\s*(.+)Ending stage:/",                   "index" => $numvars++ ),
    array("name" => "end_stage",        "clean" => true, "matchoffset" => 1, "pattern" => "/Ending stage:\s*(.+)Fraction up in ending stage:/",             "index" => $numvars++ ),
    array("name" => "frac_upE",         "clean" => true, "matchoffset" => 1, "pattern" => "/Fraction up in ending stage:\s*(.+)Ending date \(Ma\):/",       "index" => $numvars++ ),
    array("name" => "end_date",         "clean" => true, "matchoffset" => 1, "pattern" => "/Ending date \(Ma\):\s*(.+)Depositional setting:/",              "index" => $numvars++ ),
    array("name" => "depositional",                      "matchoffset" => 1, "pattern" => "/Depositional setting:\s*(.+)Depositional-pattern:/",            "index" => $numvars++ ),
    array("name" => "depositional_pattern",              "matchoffset" => 1, "pattern" => "/Depositional-pattern:\s*(.+)Additional Information/",           "index" => $numvars++ ),
    array("name" => "additional_info",                   "matchoffset" => 1, "pattern" => "/Additional Information\s*(.+)Compiler/",                        "index" => $numvars++ ),
    array("name" => "compiler",                          "matchoffset" => 1, "pattern" => "/Compiler\s*(.+)$/",                                              "index" => $numvars++ )
  );
  function vindex($varname, $vars) {
    $found = current(array_filter($vars, function($v,$i) use ($varname) { return $v["name"] == $varname; }, ARRAY_FILTER_USE_BOTH));
    return $found["index"];
  }
  $nameindex = vindex("name", $vars);
  $bstageindex = vindex("beginning_stage", $vars);
  $bfracindex = vindex("frac_upB", $vars);
  $bdateindex = vindex("beg_date", $vars);
  $estageindex = vindex("end_stage", $vars);
  $efracindex = vindex("frac_upE", $vars);
  $edateindex = vindex("end_date", $vars);
  $geojsonindex = vindex("geojson", $vars);
  $fossilsindex = vindex("fossils", $vars);
  $regionalindex = vindex("regional_extent", $vars);

  foreach ($splitcontent as $ministr) {
    if ($count++ < $skipFirstNFormations) continue;
    // match all the patterns
    for($i=0; $i<count($vars); $i++) {
      $v = $vars[$i];
      //preg_match($formpattern, $ministr, $form)
      //echo "Pattern = " . $v["pattern"] . ", ministr = $ministr";
      preg_match($v["pattern"], $ministr, $matches);

      if ($v["name"] == "period" || $v["name"] == "age_interval" || $v["name"] == "province") {
        if (!$matches || count($matches) < 1) {
          echo "ERROR: Did not find any matches for ".$v["name"]." in the #$count formation (".$vars[$nameindex]["value"].").  Did you have a colon not bold?<br/>";
        }
      }

      $vars[$i]["matches"] = $matches;
      // Uncomment to help you debug a particular word doc:
      //if ($i == $regionalindex && preg_match("/Bailong Fm/", $ministr)) {
      //  echo "ministr = <div style=\"width: 1024px\">";print_r(htmlspecialchars($ministr));echo "</div><br><br>";
      //  echo "pattern = ".$v[pattern].", matches = <pre>";print_r(htmlspecialchars($matches));echo"</pre><br><br>";
      //}
      $vars[$i]["value"] = trim($matches[$v["matchoffset"]]); // get rid of newlines on the end
      if (preg_match("/\r\n/", $vars[$i]["value"])) { // only add paragraph tags if there are newlines in it (single-line doesn't have them)
        $vars[$i]["value"] = "<p>" . str_replace("\r\n", "</p>\r\n<p>", $vars[$i]["value"]) . "</p>";
      }
      if ($vars[$i]["name"] == "compiler") {
        // Not sure why these were there, commenting for now
        //$vars[$i]["value"] = str_replace("(", "", $vars[$i]["value"]);
        //$vars[$i]["value"] = str_replace(")", "", $vars[$i]["value"]);
      }
      // echo "Parsed variable before cleaning: <pre>"; print_r($vars[$i]); echo "</pre>, and parsed from string:<br/><pre>"; print_r($ministr); echo "</pre>";
      if (isset($vars[$i]["clean"])) {
        $vars[$i]["value"] = cleanupString($vars[$i]["value"]);
      }      
      if(isset($vars[$i]["cleanjson"])){
       //var_dump(strip_tags($vars[$i]["value"]));
        $origgeoJSON = strip_tags($vars[$i]["value"]);
        $vars[$i]["value"] = cleanupGeojson($vars[$i]["value"]);
      } 
       
    }
    $count = 0;
    // Check if the name is blank, if so, do not insert anything to the database
    if (strlen(trim($vars[$nameindex]["value"])) < 1) {
      echo "\n Found an empty name, ignoring...<br/>";
      continue;
    }

    // Compute ages if possible:
    if ($timescale) {
      // If we have stage and percentage for base:
      $a = computeAgeFromPercentUp($vars[$bstageindex]["value"], $vars[$bfracindex]["value"], $timescale);
      if ($a !== false) {
        echo "Computed base age of ".$vars[$nameindex]["value"]." as $a<br/>";
        $vars[$bdateindex]["value"] = $a;
      }
      $a = computeAgeFromPercentUp($vars[$estageindex]["value"], $vars[$efracindex]["value"], $timescale);
      if ($a !== false) {
        echo "Computed top age of ".$vars[$nameindex]["value"]." as $a<br/>";
        $vars[$edateindex]["value"] = computeAgeFromPercentUp($vars[$estageindex]["value"], $vars[$efracindex]["value"], $timescale);
      }
    }

    $sql20 = "ON DUPLICATE KEY UPDATE ";
    $exists = array();
    //$sql0 = "SHOW COLUMNS FROM formation LIKE";
    for($i=0; $i<count($vars); $i++){
      $v = $vars[$i];
      $sql2 = $sql0.$v["name"];
      if($v["name"]=='name'){
        $result = $conn->query($sql2);
        $exist = (mysqli_num_rows($result))?TRUE:FALSE;
        array_push($exists,$exist);
        $count = $count+1;
      }
    }
    $sql = "INSERT INTO formation(";
    $ct = 0;
    for($i=0; $i<count($vars); $i++) {
      $v = $vars[$i];
      if($exist[$ct]!=TRUE){
        $sql .= $v["name"];
      }
      if($v["name"]=="compiler"){
        $ct = $ct +1;
      }
      if (($i < count($vars)-1)&&$exists[$ct]!=TRUE) {
        $sql .= ",";
      }
    }
    $sql .= ") VALUES (";
    $ct = 0;
    for($i=0; $i<count($vars); $i++) {
      $v = $vars[$i];
      $sql20 .= $v["name"]."="."'".trim($conn->real_escape_string($v["value"]))."'"; 
      $sql .= "'".trim($conn->real_escape_string($v["value"]))."'";
      if ($i < count($vars)-1) {
        $sql .= ",";
        $sql20.=",";
      }
    }
    $sql .= ")";
    $sql = $sql.$sql20;
    
    // CODE WILL PREVENT INVALID GEOJSON DATA FROM BEING PARSED INTO THE DOCUMENT, BUT FIRST THE GEOJSON NEEDS TO BE CLEANED UP
    if($vars[$geojsonindex]["value"] == "null" && !empty($origgeoJSON)){
      if (preg_match("/Fossils:/", $origgeoJSON)) {
        echo "Error: Your Fossils text contains the word \"Fossils:\".  Please change the F to lowercase or remove the colon.";
      } else {
      echo "Error: Invalid geoJSON data. Please recheck for misplaced punctuation or brackets. The invalid geoJSON that was in the document was: <pre>"; htmlspecialchars(print_r($origgeoJSON)); echo "</pre>";
      }
      exit("<br>Formation not parsed. Recheck word document.");
    } 
    
     
    if ($conn->query($sql) === TRUE) {
      echo "Inserted Formation: ".$vars[$nameindex]["value"]."<br>";
    } else {
      echo "-------------------------------------------------\n\n<br><br>";
      echo "Error inserting data " . $conn->error;
      //  echo "The sent query was: : <pre>$sql</pre>";
      echo "The array of extractions which produced that query was: <pre>";print_r($vars);echo "</pre>";
      echo "-------------------------------------------------\n\n<br><br>";
    }
    //echo "The array of extractions which produced that query was: <pre>";print_r($vars);echo "</pre>";
    echo "Parsing is Complete!<br/>";
  }
}
?>

</body>

</html>
