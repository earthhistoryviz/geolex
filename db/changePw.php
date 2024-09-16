<?php

// This file gives you the $conn variable:
include("../site/SqlConnection.php");

if ($argc != 2) {
    echo "USAGE: $argv[0] <newpassword>";
    exit;
}

$newpw = password_hash($argv[1], PASSWORD_DEFAULT);
$salt = "SALT";
$sql = "UPDATE user_info
            SET pasw='$newpw'
          WHERE uname='root' OR uname='root2'";
if ($conn->query($sql) === true) {
    echo "Password updated successfully\n";
} else {
    echo "ERROR: updating password: " . $conn->error . "\n";
}
