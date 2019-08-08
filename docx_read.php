<html>

<head>
<title>Test page</title>
</head>
<body>

<?php
read_file_docx("test.docx");
function read_file_docx($filename){
    //$filename = 'test.docx';
    $striped_content = '';
    $content = '';

    if(!$filename || !file_exists($filename)) return false;

    $zip = zip_open($filename);

    if (!$zip || is_numeric($zip)) return false;

    while ($zip_entry = zip_read($zip)) {

        if (zip_entry_open($zip, $zip_entry) == FALSE) continue;

        if (zip_entry_name($zip_entry) != "word/document.xml") continue;

        $content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

        zip_entry_close($zip_entry);
    }// end while

    zip_close($zip);

    //echo $content;
    //echo "<hr>";
    //file_put_contents('1.xml', $content);

    $content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
    $content = str_replace('</w:r></w:p>', "\r\n", $content);
    $striped_content = strip_tags($content);

    return $striped_content;
}
function convert($text) {
    $text = trim($text);
    return '<p>' . preg_replace('/[\r\n]+/', '</p><p>', $text) . '</p>';
}
$content = read_file_docx("test.docx");
echo convert($content);
//$filename = "filepath";// or /var/www/html/file.docx

//$content = read_file_docx($filename);
//if($content !== false) {

 //   echo nl2br($content);
//}
//else {
  //  echo 'Couldn\'t the file. Please check that file.';
//}



?>
</body>

</html>