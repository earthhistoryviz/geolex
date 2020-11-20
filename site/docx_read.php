<?php 
?>
<html>
<head>
<title>Document Reader</title>
</head>
<body>
<?php
include("navBar.php");
include("SearchBar.php");

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
  $nameindex = 0; // The index of the "name" field in the array below:

  $vars = array(
    array("name" => "name",            "matchoffset" => 0, "pattern" => "/([\s\w’]+\s)(Gr|Fm|Group|Formation)/"),
    array("name" => "period",          "matchoffset" => 1, "pattern" => "/Period:\s*(.*)Age Interval/"),
    array("name" => "age_interval",    "matchoffset" => 1, "pattern" => "/Age Interval\s*\(Map column\):\s*(.+)Province:/"),
    array("name" => "province",        "matchoffset" => 1, "pattern" => "/Province:\s*(.*)Type Locality and Naming:/"),
    array("name" => "type_locality",   "matchoffset" => 2, "pattern" => "/Type Locality and Naming:(\s*(.+))Lithology and Thickness:/"),
    array("name" => "lithology",       "matchoffset" => 1, "pattern" => "/Lithology and Thickness:(\s*(.+))Relationships and Distribution:/"),
    array("name" => "lower_contact",   "matchoffset" => 1, "pattern" => "/Lower contact:(\s*(.+))Upper contact:/"),
    array("name" => "upper_contact",   "matchoffset" => 1, "pattern" => "/Upper contact:\s*(.+)Regional (e|E)xtent:/"),
    array("name" => "regional_extent", "matchoffset" => 1, "pattern" => "/Regional extent:\s*(.+)Fossils:/"),
    array("name" => "fossils",         "matchoffset" => 1, "pattern" => "/Fossils:\s*(.+)Age:/"),
    array("name" => "age",             "matchoffset" => 1, "pattern" => "/Age:\s*(.+)Depositional setting:/"),
    array("name" => "depositional",    "matchoffset" => 1, "pattern" => "/Depositional setting:\s*(.+)Additional Information/"),
    array("name" => "additional_info", "matchoffset" => 1, "pattern" => "/Additional Information\s*(.+)Compiler/"),
    array("name" => "compiler",        "matchoffset" => 1, "pattern" => "/Compiler\s*(.+)/")
  );

  foreach ($splitcontent as $ministr) {
    if ($count++ < $skipFirstNFormations) continue;
    // match all the patterns
    for($i=0; $i<count($vars); $i++) {
      $v = $vars[$i];
      //preg_match($formpattern, $ministr, $form)
      //echo "Pattern = " . $v["pattern"] . ", ministr = $ministr";
      preg_match($v["pattern"], $ministr, $matches);
      $vars[$i]["matches"] = $matches;
      $vars[$i]["value"] = trim($matches[$v["matchoffset"]]); // get rid of newlines on the end
      if (preg_match("/\r\n/", $vars[$i]["value"])) { // only add paragraph tags if there are newlines in it (single-line doesn't have them)
        $vars[$i]["value"] = "<p>" . str_replace("\r\n", "</p>\r\n<p>", $vars[$i]["value"]) . "</p>";
      }
      if ($vars[$i]["name"] == "compiler") {
        $vars[$i]["value"] = str_replace("(", "", $vars[$i]["value"]);
        $vars[$i]["value"] = str_replace(")", "", $vars[$i]["value"]);
      }

    }
    //echo "Hii";
    $count = 0;
    // Check if the name is blank, if so, do not insert anything to the database
    if (strlen(trim($vars[$nameindex]["value"])) < 1) {
      echo "\n Found an empty name, ignoring...";
      continue;
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
    if ($conn->query($sql) === TRUE) {
      echo "Inserted Formation: ".$vars[$nameindex]["value"]."<br>";
    } else {
      echo "-------------------------------------------------\n\n<br><br>";
      echo "Error inserting data " . $conn->error;
      //  echo "The sent query was: : <pre>$sql</pre>";
      //  echo "The array of extractions which produced that query was: <pre>";print_r($vars);echo "</pre>";
      echo "-------------------------------------------------\n\n<br><br>";
    }
    echo "Parsing is Complete!";
  }
}
?>

</body>

</html>
