<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
    $title = substr($_SERVER['REQUEST_URI'], 7);
    $title = str_replace(['.php', '_'], ['', ' '], $title); ?>
    <title><?= $title ?></title>
    <style>
        h3 {
            margin: 0px;
        }
    </style>
    <?php
    include("../navBar.php");
?>

<div style="margin-left: 10px; padding-left: 10px;">
<br>
For details, click on the formations below. Stratigraphic columns are only schematic.
</div>
