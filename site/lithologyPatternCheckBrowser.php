<!DOCTYPE html>
<html>
<body>
<?php 
session_start();
#checks to see if user is logged in
if (!$_SESSION["loggedIn"]) {
    echo "ERROR: You must be logged in to access this page.";
    exit(0);
}
include_once("adminDash.php");
$isFixedRegion = true;
include_once("SqlConnection.php");
include_once("cleanupString.php");

?>
<br><br> 

<!-- creates a button with the text Check Lithology Pattern, press button would print the available lithology pattern to screen-->
<!-- <form action="" method="post" enctype="multipart/form-data">
    <input type="submit" value="Check Lithology Pattern" name="submit">
    <br><br>
</form> -->


<?php

    function checkForLithologyError($availableLithologyNames) {
        $all_formation = getAllFormations(); //gets all the formation from sql database
        
        $errorLithology = [];
        
        for($i = 0;  $i < count($all_formation); $i++) {
            $currLithoPattern = cleanupString(strtolower($all_formation[$i]['lithology_pattern'])); //need to clean string of HTML tags
    
            if(!in_array($currLithoPattern, $availableLithologyNames)) {
                $txt =  $all_formation[$i]["name"] . ": " . $currLithoPattern;
                array_push($errorLithology, $txt);
            } 
        }

        return $errorLithology;

    }

    #found online 
    #Program: Printing an Array in a Horizontally Columned HTML Table
    #https://www.oreilly.com/library/view/php-cookbook/1565926811/ch04s27.html
    function print_in_table_format($array, $size) {

        // compute <td> width %ages
        $table_width = 100;
        $width = intval($table_width / $size);
    
        // define how our <tr> and <td> tags appear
        // sprintf() requires us to use %% to get literal %
        $tr = '<tr align="center">';
        $td = "<td width=\"$width%%\">%s</td>";
    
        // open table
        $grid = "<table width=\"$table_width%\">$tr";
    
        // loop through entries and display in rows of size $sized
        // $i keeps track of when we need a new table row
        $i = 0;
        foreach ($array as $e) {
            $grid .= sprintf($td, $e);
            $i++;            
    
            // end of a row
            // close it up and open a new one
            if (!($i % $size)) {
                $grid .= "</tr>$tr";
            }
            
        }
        
    
        // pad out remaining cells with blanks
        while ($i % $size) {
            $grid .= sprintf($td, '&nbsp;');
            $i++;
        }
    
        // add </tr>, if necessary
        $end_tr_len = strlen($tr) * -1;
        if (substr($grid, $end_tr_len) != $tr) {
            $grid .= '</tr>';
        } else {
            $grid = substr($grid, 0, $end_tr_len);
        }
    
        // close table
        $grid .= '</table>';
    
        return $grid;
    }

    function writeToScreen($errorLithology, $availableLithologyNames) {

        echo " <b> Errors or Unsupported Lithology Pattern Names in the database: </b> <br> <br>";
        for($i = 0; $i < count($errorLithology); $i++) {
            $txt = $errorLithology[$i]. "<br>";
            echo $txt;
            flush();
            
        }

        echo "<br> <br> <br>";
        echo" <b> Supported Lithology Pattern Names:  </b> <br>";

        // generate the HTML table
        $grid = print_in_table_format($availableLithologyNames, 5); //prints the available Lithology in a table format, 5 lithology names across

        // and print it out
        print $grid;

        echo "<br> <br> <br>";

    }

    
    try { 
        $pathofLithologyLookUp = "./pygplates/config/TSCreator_litho-pattern_to_GMT-fixed_pattern_code.csv";

        $csvFile = file($pathofLithologyLookUp);

        $availableLithology = [];
        #parses the csv file and has the output as a 2d array 
        foreach ($csvFile as $line) {
            $availableLithology[] = str_getcsv($line);
        }
        
        $availableLithologyNames = [];

        for($i = 2; $i < count($availableLithology); $i++) {    
            array_push($availableLithologyNames, strtolower($availableLithology[$i][0]));
        }

        $errorLithology = checkForLithologyError($availableLithologyNames);
        writeToScreen($errorLithology, $availableLithologyNames);


    } catch (RuntimeException $e) {
        echo $e->getMessage();
    }

    
?>


</body>
</html>