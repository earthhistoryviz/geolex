<!DOCTYPE>
<html>
<?php
include("navBar.php");
include("SearchBar.php");
$formationName = $_REQUEST;
?>

<head>
	<title>No Match</title>
</head>

<body>
	<h2>Nothing found for "<?=$formationName[formation]?>". Please search again.</h2>
</body>
</html>
