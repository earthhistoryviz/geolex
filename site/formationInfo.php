<?php

include_once("SqlConnection.php");
// Get all the formation names to build the regexp searches in the text for automatic link creation

$fmdata = array(
  'name'                                  => array("needlinks" => false),
  'period'                                => array("needlinks" => false),
  'age_interval'                          => array("needlinks" => false),
  'province'                              => array("needlinks" => false),
  'type_locality'                         => array("needlinks" => true),
  'lithology'                             => array("needlinks" => true),
  'lithology_pattern'                     => array("needlinks" => true),
  'lower_contact'                         => array("needlinks" => true),
  'upper_contact'                         => array("needlinks" => true),
  'regional_extent'                       => array("needlinks" => true),
  'geojson'                               => array("needlinks" => true),
  'fossils'                               => array("needlinks" => true),
  'age'                                   => array("needlinks" => false),
  'age_span'                              => array("needlinks" => false),
  'beginning_stage'                       => array("needlinks" => false),
  'frac_upB'                              => array("needlinks" => false),
  'beg_date'                              => array("needlinks" => false),
  'end_stage'                             => array("needlinks" => false),
  'frac_upE'                              => array("needlinks" => false),
  'end_date'                              => array("needlinks" => false),
  'depositional'                          => array("needlinks" => true),
  'depositional_pattern'                  => array("needlinks" => true),
  'additional_info'                       => array("needlinks" => true),
  'compiler'                              => array("needlinks" => false),
);

if (!$macrostrat) {
    $sql = "SELECT name FROM formation";
    $result = mysqli_query($conn, $sql);
    $nameregexes = array();
    function insertSorted(&$array, $element, $compareFunc)
    {
        $low = 0;
        $high = count($array);

        while ($low < $high) {
            $mid = floor(($low + $high) / 2);
            if ($compareFunc($element, $array[$mid]) < 0) {
                $high = $mid;
            } else {
                $low = $mid + 1;
            }
        }

        array_splice($array, $low, 0, [$element]);
    }

    // Comparison function for sorting by string length (descending)
    function compareByNameLength($a, $b)
    {
        return strlen($b['name']) - strlen($a['name']);
    }

    while ($row = mysqli_fetch_array($result)) {
        $rname = $row["name"];
        $rnameRegex = preg_replace('/\s+/', '\s*', $rname);
        // turn name into regular expression allowing arbitrary number of spaces between words
        $newElement = array(
          "name" => $rname,
          "regex" => "/\b($rnameRegex)\b/i"
          //"superceeded_by" => array(), // nameregex that should supercede this name if it also matches (Bao Loc Fm -> Deo Bao Loc Fm: if Deo matches, then use it instead)
        );
        insertSorted($nameregexes, $newElement, 'compareByNameLength');
    }
    $sql = "SELECT * FROM formation WHERE name LIKE '%$formation%'"; //old query that won't work with Kali vs. Warkali formations or characters needing to be escaped

    $searchname = mysqli_real_escape_string($conn, $formation);
    $sql = "SELECT * FROM formation WHERE name= '$searchname'";
    if (preg_match("/’/", $searchname)) {
        $sql .= " OR name = \"".preg_replace("/’/", "'", $searchname)."\"";
    }

    $result = mysqli_query($conn, $sql);

    // This function finds formations or fossils in a string and replaces them with hyperlinked versions.
    // It ensures that each formation is linked only once and prioritizes longer formations over shorter overlapping ones.
    function findAndMakeLinks($str, $regexes, $baseUrl)
    {
        // This could potentially be optimized but the page loads fast enough
        // This code collects all regex matches and figures out the end positions of the matches. It then sorts them by the end and only considers the first instance since any
        // other instance represents a shorter string match (since the array $nameregex is sorted from longest to smallest).
        // An example is Deo Bao Loc Fm and Bao Loc Fm. The regex for Bao Loc Fm will match both formations, but the end position is the same. Since $nameregexes is sorted
        // from longest to smallest, we will match Deo Bao Loc Fm first. When we later filter Bao Loc Fm will be dropped since the last position is the same and it comes later
        // Then we use the filtered matches to replace links starting from the end of the string
        $allMatches = [];

        // Collect all matches with their start and end positions
        foreach ($regexes as $n) {
            if (preg_match_all($n["regex"], $str, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as $match) {
                    $replacement = "<a href=\"$baseUrl" . $n["name"] . "\">" . $n["name"] . "</a>";
                    $allMatches[] = [
                      'start' => $match[1],
                      'end' => $match[1] + strlen($match[0]),
                      'replacement' => $replacement
                    ];
                }
            }
        }

        // Sort matches by end position in descending order
        usort($allMatches, function ($a, $b) {
            return $b['end'] - $a['end'];
        });

        // Keep only the first instance of each end position
        $uniqueEnds = [];
        $filteredMatches = [];
        foreach ($allMatches as $match) {
            $endPos = $match['end'];
            if (!isset($uniqueEnds[$endPos])) {
                $uniqueEnds[$endPos] = true;
                $filteredMatches[] = $match;
            }
        }

        // Replace matches in the string
        foreach ($filteredMatches as $match) {
            $str = substr_replace($str, $match['replacement'], $match['start'], $match['end'] - $match['start']);
        }

        return $str;
    }

    $found = false;
    while ($row = mysqli_fetch_array($result)) {
        $found = true;
        // Fill in each of the variables that we're going to send to the browser
        foreach($fmdata as $varname => $varvalue) {
            $rowval = $row[$varname];
            $fmdata[$varname]["raw"] = trim($rowval);
            $fmdata[$varname]["display"] = trim($rowval);
            if ($varvalue["needlinks"]) {
                $fmdata[$varname]["display"] = findAndMakeLinks($rowval, $nameregexes, "/formations/");
            }
        }
    }

    // Initialize the fossil data
    $fossilSites = array("brachiopod", "echinoderm", "porifera", "graptolite", "ammonoid", "charophyte"); // Add more fossil groups as needed
    $allGenusHashmap = array();
    foreach ($fossilSites as $site) {
        // Construct the API URL for the current fossil site
        $apiUrl = "https://{$site}.treatise.geolex.org/searchAPI.php?genusOnly=true";
        $jsonData = json_decode(file_get_contents($apiUrl), true);
        $dataArray = $jsonData["data"];
        foreach ($dataArray as $key => $value) {
            $allNames = [$key];
            if (isset($value['Synonyms'])) {
                $allNames[] = $value['Synonyms'];
            }
            $escapedNames = array_map(function ($name) {
                return preg_quote($name, '/');
            }, $allNames);
            $regexPattern = "/\b(" . implode('|', $escapedNames) . ")\b/i";
            $allGenusHashmap[] = array(
                "name" => $key,
                "regex" => $regexPattern,
                "site" => $site
            );
        }
    }

    // Check if fossil data exists and process it
    if (isset($fmdata['fossils']['display'])) {
        // Modify the findAndMakeLinks function call to use the correct site for each genus
        $displayText = $fmdata['fossils']['display'];
        
        // Loop through each genus in the hashmap and update the display text with appropriate links
        foreach ($allGenusHashmap as $genus) {
            $siteLink = "https://{$genus['site']}.treatise.geolex.org/displayInfo.php?genera=";
            $displayText = findAndMakeLinks($displayText, array($genus), $siteLink);
        }
        $fmdata['fossils']['display'] = $displayText;
    }

} else {
    // for macrostrat. Note: Functionality for fossil searching does not exist, since macrostrat API does not have fossils
    // The fossil shown currently on macrostrat formations was added with another API by Aditya
    // You can't search for fossils in macrostrat formations, and it also doesn't have links to fossil pages
    $found = false;
    $formationEncoded = urlencode(trim(explode(" ", $formation)[0]));
    $colEncoded = urlencode($col_id);
    $url = "https://macrostrat.org/api/units?strat_name=$formationEncoded&col_id=$colEncoded&response=long";
    $raw = file_get_contents($url);
    $result = json_decode($raw, true);
    if (isset($result["success"])) {
        $found = true;
        $result = $result["success"]["data"][0];
        $fmdata["name"]["display"] = $formation;
        include_once("TimescaleLib.php");
        $timescale = parseDefaultTimescale();
        $topAge = (float) $result["t_age"];
        $bottomAge = (float) $result["b_age"];
        $midAge = ($bottomAge + $topAge) / 2;
        foreach($timescale as $time) {
            if ($midAge <= $time["base"] && $midAge >= $time["top"]) {
                $fmdata["period"]["display"] = $time["period"];
            }
            if ($bottomAge <= $time["base"] && $bottomAge >= $time["top"]) {
                $fmdata["beginning_stage"]["display"] = $time["stage"];
            }
            if ($topAge <= $time["base"] && $topAge >= $time["top"]) {
                $fmdata["end_stage"]["display"] = $time["stage"];
            }
        }
        $fmdata["beg_date"]["display"] = $bottomAge;
        $fmdata["end_date"]["display"] = $topAge;
        $fmdata["age_interval"]["display"] = $result["b_int_name"];
        $fmdata["frac_upB"]["display"] = $result["b_prop"];
        $fmdata["frac_upE"]["display"] = $result["t_prop"];
        if ($result["b_int_name"] != $result["t_int_name"]) {
            $fmdata["age_interval"]["display"] .= " to " . $result["t_int_name"];
        }
        $url = "http://localhost/macrostratAPI.php?searchquery=$formationEncoded";
        $raw = file_get_contents($url);
        $localResult = json_decode($raw, true);
        $formationInCol = null;
        foreach($localResult as $formations) {
            if ($formations['col_id'] == $col_id) {
                $formationInCol = $formations;
                break;
            }
        }
        if ($formationInCol) {
            $fmdata["province"]["display"] = $formationInCol["region"];
            $fmdata["lithology_pattern"]["display"] = $formationInCol["lithology_pattern"];
        }
        $unitIdsAbove = $result["units_above"];
        $namesAbove = [];
        foreach($unitIdsAbove as $unitId) {
            $url = "https://macrostrat.org/api/units?unit_id=$unitId";
            $raw = file_get_contents($url);
            $unit = json_decode($raw, true);
            if (isset($unit["success"])) {
                $namesAbove[] = $unit["success"]["data"][0]["unit_name"];
            }
        }
        $unitIdsBelow = $result["units_below"];
        $namesBelow = [];
        foreach($unitIdsBelow as $unitId) {
            $url = "https://macrostrat.org/api/units?unit_id=$unitId";
            $raw = file_get_contents($url);
            $unit = json_decode($raw, true);
            if (isset($unit["success"])) {
                $namesBelow[] = $unit["success"]["data"][0]["unit_name"];
            }
        }
        $fmdata["lower_contact"]["display"] = implode(", ", $namesBelow);
        $fmdata["upper_contact"]["display"] = implode(", ", $namesAbove);
        $fmdata["regional_extent"]["display"] = $result["Gp"];
        $fmdata["geojson"]["display"] = json_encode($formationInCol["geojson"]);
        $fmdata["compiler"]["display"] = "Macrostrat";
        $unitId = $result["unit_id"];
        $url = "https://macrostrat.org/api/fossils?unit_id=$unitId";
        $raw = file_get_contents($url);
        $fossilResult = json_decode($raw, true);
        $taxonomy = [];
        if (isset($fossilResult["success"])) {
            $fossilResult = $fossilResult["success"]["data"];
            foreach($fossilResult as $fossil) {
                $cltnId = $fossil["cltn_id"];
                $url = "https://paleobiodb.org/data1.2/occs/list.json?coll_id=$cltnId";
                $raw = file_get_contents($url);
                $pddbResult = json_decode($raw, true);
                foreach($pddbResult["records"] as $record) {
                    $entry = '';
                    if (!empty($record['idn'])) {
                        $entry .= $record['idn'];
                    }
                    if (!empty($record['tna'])) {
                        if (!empty($entry)) {
                            $entry .= " (" . $record['tna'] . ")";
                        } else {
                            $entry = $record['tna'];
                        }
                    }
                    $taxonomy[] = $entry;
                }
            }
        }
        if (!empty($taxonomy)) {
            $fmdata["fossils"]["display"] = implode(", ", $taxonomy);
        } else {
            $fmdata["fossils"]["display"] = "Unkown";
        }
    }
}
