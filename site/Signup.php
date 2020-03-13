<?php
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
if(isset($_REQUEST['submit_btn']))
{
$uname = $_REQUEST['username'];
$admin = $_REQUEST['level'];
$alevel = fasle;
$pass = $_REQUEST['password'];
$rpass = $_REQUEST['rpassword'];
if( $pass == $rpass) {
    $rootpasw = password_hash($pass, PASSWORD_DEFAULT);
    if($admin == "admin"){
        $alevel = True;
    }
    $sql3 = "INSERT INTO user_info(uname,pasw,admin)
    VALUES
    ('$uname', '$rootpasw',$alevel)";
    if($conn->query($sql3) == TRUE){
        echo "User added successfully";

    }
    else{
        echo"Error adding user";

    }
}
else{
    echo"Passwords do not match";
}
}
?>

<html>
<body>
<title>Add a User</title>
<form method="POST" action='Signup.php'>

    <div class="container">
        <label for="username"><b>Username</b></label>
        <input type="text" placeholder="Enter Username" name="username" required>

        <label for="password"><b>Password</b></label>
        <input type="password" placeholder="Enter Password" name="password" required>
        <label for="rpassword"><b>Repeat Password</b></label>
        <input type="password" placeholder="Enter Password Again" name="rpassword" required>
        <label for="level"><b>User level</b></label>
        <input type="radio" name="level" value="admin" checked> Admin<br>
        <input type="radio" name="level" value="regular"> Other user<br>
        <button type="submit" name ="submit_btn" value ="submit_btn">Sign Up</button>
    </div>
</form>

</html>