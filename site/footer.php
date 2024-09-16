<?php
include_once('constants.php');
?>
</div> <!-- somethone thought having divs span multiple files was ok. closes mainBody -->
</div> <!-- closes the div that surrounds mainBody -->
<?php
$auth = $_SESSION["loggedIn"];
if (!$auth) { ?>
	<footer class="footer-container">
		<a href="/all-formations">View All Formations in <?=$regionName?></a>
	</footer> <?php
}
