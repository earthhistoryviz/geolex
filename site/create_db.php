<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "myDB";


// create connection
$conn = new mysqli($servername, $username, $password);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$sql8 = "USE myDB";
if ($conn->query($sql8) === TRUE) {
    echo "\n Database Already Exists...Dropping Tables and Database to rebuild them.<br>";
} else {
    echo "\n Database does not exist, rebuilding from scratch, ignore errors about dropping database " . $conn->error;
}
$sql5 = "DROP TABLE IF EXISTS user_info";
if ($conn->query($sql5) === TRUE) {
    echo "\n Table user_info dropped successfully<br>";
} else {
    echo "\n Error dropping table user_info: " . $conn->error;
}
$sql6 = "DROP TABLE IF EXISTS formation";
if ($conn->query($sql6) === TRUE) {
    echo "Table formation dropped successfully<br>";
} else {
    echo "\n Error dropping table formation: " . $conn->error;
}
$sql7 = "DROP TABLE IF EXISTS timeperiod";
if ($conn->query($sql7) === TRUE) {
    echo "Table timeperiod dropped successfully<br>";
} else {
    echo "\n Error dropping table formation: " . $conn->error;
}
// drop database
$sql = "DROP DATABASE IF EXISTS myDB";
if ($conn->query($sql) === TRUE) {
    echo " \n Database dropped successfully<br>";
} else {
    echo "\n Error dropping database: " . $conn->error;
}

$conn->close();

// Create connection
$conn = new mysqli($servername, $username, $password);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE myDB";
if ($conn->query($sql) === TRUE) {
    echo "\n Database created successfully<br>";
} else {
    echo "\n Error creating database: " . $conn->error;
}

$conn->close();

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);

}
//$sql = "DROP DATABASE IF EXISTS myDB";
//$sql = "CREATE DATABASE myDB";
//$sql = "USE  myDB";

$sql = "CREATE TABLE timeperiod(
	ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name Varchar(255),
	color Varchar(255)

)";
if ($conn->query($sql)===TRUE) {
    echo "table create successfully<br>";
} else {
    echo "\n Error creating timeperiod table: " . $conn->error;
}
$sql4 = "CREATE TABLE user_info(
    ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    uname Varchar(255),
    pasw Varchar(255),
    admin Varchar(255)
)";
$rootpasw = password_hash("TSCreator",PASSWORD_DEFAULT);
$sql3 = "INSERT INTO user_info(uname,pasw,admin)
VALUES
('root', '$rootpasw','True')";
if ($conn->query($sql4)==TRUE && $conn->query($sql3)===TRUE) {
    echo "table create successfully<br>";
} else {
    echo "\n Error creating user_info table: " . $conn->error;
}
$sql2 = "CREATE TABLE formation(
	ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name Varchar(255),
	period Varchar(255),
	age_interval Varchar(255),
	province Varchar(255),
 	type_locality Varchar(40000),
	lithology Text,
	lower_contact Text,
	upper_contact Text,
	regional_extent Text,
	fossils Text,
	age Text,
	depositional Text,
	additional_info Varchar(4000),
	compiler Varchar(255)
)";

if ($conn->query($sql2)===TRUE) {
    echo "table formation created successfully<br>";
} else {
    echo "\n Error creating formation table: " . $conn->error;
}

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
                    $age_inpattern = "/Age Interval\s*\(Map column\): (.+)Province:/";
                    $provincepattern = "/Province:\s*(\w+)/";
                    $typepattern = "/Type Locality and Naming:(\s*(.+))Lithology and Thickness:/";
                    $lithpattern = "/Lithology and Thickness:(\s*(.+))Relationships and Distribution:/";
                    $lowerpattern = "/Lower contact:(\s*(.+))Upper contact:/";
                    $upperpattern = "/Upper contact:\s*(.+)Regional extent:/";
                    $regionalpattern = "/Regional extent:\s*(.+)Fossils:/";
                    $fossilpattern = "/Fossils:\s*(.+)Age:/";
                    $agepattern = "/Age:\s*(.+)Depositional setting:/";
                    $depositionpattern = "/Depositional setting:\s*(.+)Additional Information/";
                    $addpattern ="/Additional Information\s*(.+)Compiler/";
                    $compilerpattern = "/Compiler\s*(.+)/";


                    $servername = "localhost";
                    $username = "root";
                    $password = "";
                    $dbname = "myDB";


                    // Create connection

                    //$sql = "DROP DATABASE IF EXISTS myDB";
                    //$sql = "CREATE DATABASE myDB";
                    //$sql = "USE  myDB";
                    foreach( $splitcontent as $ministr){
                        //echo $ministr;
                        //echo "Hi";
                    preg_match($formpattern,$ministr,$formname);
                    preg_match($periodpattern,$ministr,$period);
                    preg_match($age_inpattern,$ministr,$age_in);
                    preg_match($provincepattern,$ministr,$province);
                    preg_match($typepattern,$ministr,$type);
                    preg_match($lithpattern,$ministr,$lith);
                    preg_match($lowerpattern,$ministr,$lower);
                    preg_match($upperpattern,$ministr,$upper);
                    preg_match($regionalpattern,$ministr,$regional);
                    preg_match($fossilpattern,$ministr,$fossil);
                    preg_match($agepattern,$ministr,$age);
                    preg_match($depositionpattern,$ministr,$depositional);
                    preg_match($addpattern,$ministr,$addinfo);
                    preg_match($compilerpattern,$ministr,$compiler);
                    //echo $ministr;
                    $sformname = $formname[0];
                    $speriod = $period[1];
                    $sage_in = $age_in[1];
                    $sprovince = $province[1];
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
                    //    var_dump($compiler);

                    $sformname = str_replace("</p><p>","",$sformname);
                        $speriod = str_replace("</p><p>","",$speriod);
                        $sage_in = str_replace("</p><p>","",$sage_in);
                        $sprovince = str_replace("</p><p>","",$sprovince);
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
                        //echo $scompiler;

                        echo nl2br("\n$sadd");
                        $sql = "INSERT INTO formation(name,period,age_interval,province,type_locality,lithology,lower_contact,upper_contact,regional_extent,fossils,age,depositional,additional_info,compiler)
                        VALUES(
                        '$sformname',
                        '$speriod',
                        '$sage_in',
                        '$sprovince',
                        '$stype',
                        '$slith',
                        '$slower',
                        '$supper',
                        '$sregional',
                        '$sfossil',
                        '$sage',
                        '$sdepositional',
                        '$sadd',
                        '$scompiler')";
                        if ($conn->query($sql) === TRUE) {
                        echo nl2br("\n data inserted ");
                        } else {
                        echo nl2br("\n Error inserted " . $conn->error);
                        }

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

                    echo "Added Devonian data to the database.!";

            ?>
