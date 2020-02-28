<?php
// This file gives you the $conn variable:
include("../site/SqlConnection.php");

$sql = "DROP TABLE user_info";
$conn->query($sql);
$sql4 = "CREATE TABLE user_info(
  ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  uname Varchar(255),
  pasw Varchar(255),
  admin Varchar(255)
)";
$rootpasw = password_hash("TSCreator", PASSWORD_DEFAULT);
$rootpasw2 = password_hash("TSCreator2", PASSWORD_DEFAULT);
$salt = "SALT";
$sql3 = "INSERT INTO user_info(uname,pasw,admin)
  VALUES 
  ('root', '$rootpasw','True')
";
$sql5 = "INSERT INTO user_info(uname,pasw,admin)
  VALUES 
  ('root2', '$rootpasw','True')
";
if ($conn->query($sql4) && $conn->query($sql3) && $conn->query($sql5) === TRUE) {
  echo "table create successfully<br>";
} else {
  echo "Error creating user_info table: " . $conn->error;
}


