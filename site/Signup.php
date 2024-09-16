<?php
session_start();
#checks to see if user is logged in
if (!$_SESSION["loggedIn"]) {
    echo "ERROR: You must be logged in to access this page.";
    exit(0);
}
include_once("adminDash.php");
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "myDB";
$output = '';
$REMAKE_TABLE = false;
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if(isset($_REQUEST['submit_btn'])) {
    $uname = $_REQUEST['username'];
    $admin = $_REQUEST['level'];
    $alevel = false;
    $pass = $_REQUEST['password'];
    $rpass = $_REQUEST['rpassword'];
    if($pass == $rpass) {
        $rootpasw = password_hash($pass, PASSWORD_DEFAULT);
        if($admin == "admin") {
            $alevel = true;
        }
        $sql3 = "INSERT INTO user_info(uname,pasw,admin)
        VALUES
        ('$uname', '$rootpasw',$alevel)";
        if($conn->query($sql3) == true) {
            echo "User added successfully";
        } else {
            echo"Error adding user";
        }
    } else {
        echo"Passwords do not match";
    }
} ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add a User</title>
<style>

    .acontainer {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        height: 100vh;
        margin: 0;
        background-color: white;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    label {
        margin-top: 10px;
        display: block;
        color: #333;
    }
    input[type="text"], input[type="password"] {
        width: 100%;
        padding: 8px;
        margin: 5px 0 20px 0;
        display: inline-block;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }
    input[type="radio"] {
        margin: 0 10px 0 5px;
    }
    button {
        background-color: #4CAF50;
        color: white;
        padding: 14px 20px;
        margin: 8px 0;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        width: 100%;
    }
    button:hover {
        background-color: #45a049;
    }
</style>
</head>
<body>

<div class="acontainer">
    <h2>Add a User</h2>
    <br>
    <form method="POST" action="Signup.php">
        <label for="username"><b>Username</b></label>
        <input type="text" placeholder="Enter Username" name="username" required>
        
        <label for="password"><b>Password</b></label>
        <input type="password" placeholder="Enter Password" name="password" required>
        
        <label for="rpassword"><b>Repeat Password</b></label>
        <input type="password" placeholder="Enter Password Again" name="rpassword" required>
        
        <label for="level"><b>User Level</b></label>
        <input type="radio" name="level" value="admin" checked> Admin<br>
        <input type="radio" name="level" value="regular"> Other user<br>
        
        <button type="submit" name="submit_btn" value="submit_btn">Sign Up</button>
    </form>
</div>

</body>
</html>
