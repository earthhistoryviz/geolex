<html>

<head>
<title>Test page</title>
</head>
<body>

<?php

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
    function read_file_docx($filename)
    {   //FUNCTION to read information out of a docx and return it as a String
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


    $content = read_file_docx($filename);
    $splitcontent = explode($splitpattern, $content);
    $skipFirstNFormations = 0;

    var_dump($splitcontent);
    $count = 0;

    $vars = array(
      array("name" => "name",            "matchoffset" => 0, "pattern" => "/([\s\w’]+\s)(Gr|Fm|Group|Formation)/"),
      array("name" => "period",          "matchoffset" => 1, "pattern" => "/Period:\s*(\w+)/"),
      array("name" => "age_interval",    "matchoffset" => 1, "pattern" => "/Age Interval\s*\(Map column\):\s*(.+)Province:/"),
      array("name" => "province",        "matchoffset" => 1, "pattern" => "/Province:\s*(\w+)/"),
      array("name" => "type_locality",   "matchoffset" => 2, "pattern" => "/Type Locality and Naming:(\s*(.+))Lithology and Thickness:/"),
      array("name" => "lithology",       "matchoffset" => 1, "pattern" => "/Lithology and Thickness:(\s*(.+))Relationships and Distribution:/"),
      array("name" => "lower_contact",   "matchoffset" => 1, "pattern" => "/Lower contact:(\s*(.+))Upper contact:/"),
      array("name" => "upper_contact",   "matchoffset" => 1, "pattern" => "/Upper contact:\s*(.+)Regional extent:/"),
      array("name" => "regional_extent", "matchoffset" => 1, "pattern" => "/Regional extent:\s*(.+)Fossils:/"),
      array("name" => "fossils",         "matchoffset" => 1, "pattern" => "/Fossils:\s*(.+)Age:/"),
      array("name" => "age",             "matchoffset" => 1, "pattern" => "/Age:\s*(.+)Depositional setting:/"),
      array("name" => "depositional",    "matchoffset" => 1, "pattern" => "/Depositional setting:\s*(.+)Additional Information/"),
      array("name" => "additional_info", "matchoffset" => 1, "pattern" => "/Additional Information\s*(.+)Compiler/"),
      array("name" => "compiler",        "matchoffset" => 1, "pattern" => "/Compiler\s*(.+)/"),
    );

    foreach ($splitcontent as $ministr) {
echo "ministr = $ministr";
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

        $sql = "INSERT INTO formation(";
        for($i=0; $i<count($vars); $i++) {
          $v = $vars[$i];
          $sql .= $v["name"];
          if ($i < count($vars)-1) {
            $sql .= ",";
          }
        }
        $sql .= ") VALUES (";
        for($i=0; $i<count($vars); $i++) {
          $v = $vars[$i];
          $sql .= "'".$v["value"]."'";
          if ($i < count($vars)-1) {
            $sql .= ",";
          }
        }
        $sql .= ")";
        echo "Sending query: <pre>$sql</pre>";
        echo "The final array of extractions is <pre>"; print_r($vars); echo "</pre>";
        $conn->real_escape_string($sql);	
        if ($conn->query($sql) === TRUE) {
            echo "data inserted ";
        } else {
            echo "Error inserted " . $conn->error;
        }

    }
    echo "Parsing is Complete!";
}

?>
</body>

</html>
