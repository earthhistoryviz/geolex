<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    include_once("constants.php");
    ?>
    <title>About - <?=$regionName ?> Lexicon</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css"
        integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
</head>

<body>
    <div id="main-container">
        <?php
        include_once("navBar.php");
        ?>
        <br><br>
        <div id="people-container" class="container-fluid">
            <div class="row">
                <?php
                include_once("SimpleXLSX.php");
                $directory = 'aboutPageFiles';
                $xlsx = SimpleXLSX::parse($directory . "/Lexicon_Developers.xlsx");
                if (!$xlsx) {
                    exit("Failed to load file");
                }
                $rows = $xlsx->rows(0);
                $rows = array_slice($rows, 3); //Skip first 3 rows
                $peopleInfo = [];
                foreach ($rows as $row) {
                    $name = $row[0];
                    $role = $row[1];
                    $institution = $row[2];
                    $image_name = $row[3];
                    $imagePath = $directory . '/pictures/' . $image_name;
                    if (empty($image_name) || !file_exists($imagePath)) {
                        $image_name = 'Default.png';
                        $imagePath = $directory . '/pictures/' . $image_name;
                    }
                    $year = $row[4];
                    if (file_exists($imagePath)) {
                        $peopleInfo[] = [
                            'name' => $name,
                            'role' => $role,
                            'institution' => $institution,
                            'year' => $year,
                            'image' => $imagePath
                        ];
                    }
                }
                $htmlContent = '';
                foreach ($peopleInfo as $people) {
                    $htmlContent .= '
                    <div class="col-md-2 pb-4">
                        <div class="card h-100">
                            <img src="'.$people['image'].'" class="card-img-top" alt="'.$people['name'].'">
                            <div class="card-body">
                                <h5 class="card-title">'. htmlspecialchars($people['name']) . '</h5>
                                <p class="card-subtitle">' . htmlspecialchars($people['institution']) . '</p>
                                <p class="card-text">'. htmlspecialchars($people['year']) . '</p>
                                <p class="card-text">'. htmlspecialchars($people['role']) . '</p>
                            </div>
                        </div>
                    </div>
                    ';
                }            
                echo $htmlContent;
                ?>
            </div>
        </div> 
        <?php
        include_once("footer.php");
        ?>
    </div>
    <style>
        .card-img-top {
            max-width: 100%;
            height: auto;  
        }
        #people-container {
            min-height: 100vh;
        }
    </style>
</body>
</html>