<?php

header("Content-Type: application/json");
echo <<<EOT
{ 
  "aaron": true,
  "theotherthing": {
    "foo": "bar",
    "bar": 1
  },
  "anarray": [ "seven", 1, 14.56 ]
}
$data = file_get_contents("http://dev.timescalecreator.com:5001/searchFm.php?search=&periodfilter=&provincefilter=");

echo $data;
EOT
?>
