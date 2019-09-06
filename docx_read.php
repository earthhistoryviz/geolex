<html>

<head>
<title>Test page</title>
</head>
<body>

<?php
$splitpattern = "*****************"; //Split pattern set by the document to differentiate between each of the formations
//read_file_docx("test.docx");
function read_file_docx($filename){  //FUNCTION to read information out of a docx and return it as a String
    $filename = 'test.docx';
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
    $text = trim($striped_content);
   // $contents = strip_tags($content, '<w:p><w:u><w:i><w:b>');
   // $contents = preg_replace("/(<(\/?)w:(.)[^>]*>)\1*/", "<$2$3>", $contents);
    //echo $content;
    return '<p>' . preg_replace('/[\r\n]+/', '</p><p>', $text) . '</p>';
   // require_once 'bootstrap.php';
 //   $objReader = new \PhpOffice\PhpWord\IOFactory::createReader("Word2007");
    //$phpWord = $objReader->load($filename);
    //$section = $phpWord->getSection(0);
    //var_dump($section);
    //return $section;
}

    //require_once 'bootstrap.php';
    //$objReader = new \PhpOffice\PhpWord\IOFactory::createReader("Word2007");
   // $phpWord = $objReader->load($filename);
   // $section = $phpWord->getSection(0);
   // var_dump($section);
//function convert($text) { //FUNCTION to supplement the conversion of 
   // $text = trim($text);
  //  return '<p>' . preg_replace('/[\r\n]+/', '</p><p>', $text) . '</p>';
//}
$content = read_file_docx("test.docx");
$splitcontent = explode($splitpattern,$content);
$x = 0;
$y = 0;
$formpattern = "/[\w’]+\s(Gr|Fm)/";
$periodpattern = "/Period:\s*(\w+)/";
$age_inpattern = "/Age Interval\s*\(Map column\): (\w+)/";
$provincepattern = "/Province:\s*(\w+)/";
$typepattern = "/Type Locality and Naming:(\s*(.+))Lithology and Thickness:/";
$lithpattern = "/Lithology and Thickness:(\s*(.+))Relationships and Distribution:/";
$lowerpattern = "/Lower contact:(\s*(.+))Upper contact:/";
$upperpattern = "/Upper contact:\s*(.+)Lower contact:/";
$regionalpattern = "/Regional extent:\s*(.+)Fossils:/";
$fossilpattern = "/Fossils:\s*(.+)Age:/";
$agepattern = "/Age:\s*(.+)Depositional setting:/";
$depositionpattern = "/Depositional setting:\s*(.+)Additional Information/";
$addpattern ="/Additional Information\s*(.+)Compiler/";
$compilerpattern = "/Compiler\s*(.+)/";



foreach( $splitcontent as $ministr){	
//	echo $ministr;
	preg_match($formpattern,$ministr,$formname);
	preg_match($periodpattern,$ministr,$period);
	preg_Match($age_inpattern,$ministr,$age_in);
	preg_Match($provincepattern,$ministr,$province);
  preg_Match($typepattern,$ministr,$type);
  preg_Match($lithpattern,$ministr,$lith);
  preg_Match($lowerpattern,$ministr,$lower);
	preg_Match($upperpattern,$ministr,$upper);
	preg_Match($regionalpattern,$ministr,$regional);
	preg_Match($fossilpattern,$ministr,$fossil);
  preg_Match($agepattern,$ministr,$age);
	preg_Match($depositionpattern,$ministr,$depositional);
	preg_Match($addpattern,$ministr,$addinfo);
	preg_Match($compilerpattern,$ministr,$compiler);
 //echo $ministr;
  
  $stype  = $type[2];
  $slith  = $lith[1];
  $slower = $lower[1];
  $supper = $upper[1];
  $sregional = $regional[1];
  $sfossil = $fossil[1];
  $sage = $age[1];
  $sdepositional = $depositional[1];
  $sadd = $addinfo[1];
  $scompiler = $compiler[1];
  if($x>$y){
  //var_dump($type);
//		var_dump($compiler);

    $stype = str_replace("</p><p>","",$stype);
    $slith = str_replace("</p><p>","",$slith);
    $slower = str_replace("</p><p>","",$slower);
    $supper = str_replace("</p><p>","",$supper);
    $sregional = str_replace("</p><p>","",$sregional);
    $sfossil = str_replace("</p><p>","",$sfossil);
    $sage = str_replace("</p><p>","",$sage);
    $sdepositional = str_replace("</p><p>","",$sdepositional);
    $sadd = str_replace("</p><p>","",$sadd);
  
    $scompiler = str_replace("</p><p>","",$scompiler);
    $scompiler = str_replace("(","",$scompiler);
    $scompiler = str_replace(")","",$scompiler);
    echo $scompiler;
}
	$x = $x +1;
}
//$filename = "filepath";// or /var/www/html/file.docx

//$content = read_file_docx($filename);
//if($content !== false) {

 //   echo nl2br($content);
//    return '<p>' . preg_replace('/[\r\n]+/', '</p><p>', $text) . '</p>';
//}
//else {
  //  echo 'Couldn\'t the file. Please check that file.';
//}



?>
</body>

</html>
