<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "myDB";
$output = '';

$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$sql4 = "CREATE TABLE user_info(
    ID int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    uname Varchar(255),
    pasw Varchar(255),
    admin Varchar(255)
)";
$res =
$rootpasw = password_hash("TSCreator",PASSWORD_DEFAULT);
$salt = "SALT";
$sql3 = "INSERT INTO user_info(uname,pasw,admin)
VALUES 
('root', '$rootpasw.$salt','True')";
if ($conn->query($sql4)&&$conn->query($sql3) === TRUE) {
    echo "table create successfully<br>";
} else {
    echo "Error creating user_info table: " . $conn->error;
}
?>
<?php
if(isset($_REQUEST['submit_btn']))
{
    $uname = $_REQUEST['username'];
    $pass = $_REQUEST['password'];
    $salt = "SALT";
    $pashash = password_hash($pass,PASSWORD_DEFAULT);
    $chkpass = $pashash.$salt;
    echo '<script type = "text/javascript">alert("Hiiii")</script>';
    $sql = "SELECT uname,pasw from user_info WHERE uname ='$uname' AND pasw='$chkpass'";
    $result = mysqli_query($conn,$sql);
    if(mysqli_num_rows($result)>0) {
        echo "Login successful";
        header('location:adminDash.php');
    }
    else{
        echo '<script type = "text/javascript">alert("Incorrect Username or Password"</script>';
    }
}
?>
<html>
<body>
    <style>

        body {font-family: Arial, Helvetica, sans-serif;}
        form {border: 3px solid #f1f1f1;}

        input[type=text], input[type=password] {
            width: 100%;
            padding: 12px 20px;
            margin: 8px 0;
            display: inline-block;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        button {
            background-color: #3daaaf;
            color: #000000;
            padding: 14px 20px;
            margin: 8px 0;
            border: none;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            opacity: 0.8;
        }

        .container {
            padding: 16px;
        }

        span.psw {
            float: right;
            padding-top: 16px;
        }
    </style>
</body>
<title>Admin Login Page</title>
<form method="POST" action='login.php'>

    <div class="container">
        <label for="username"><b>Username</b></label>
        <input type="text" placeholder="Enter Username" name="username" required>

        <label for="password"><b>Password</b></label>
        <input type="password" placeholder="Enter Password" name="password" required>

        <button type="submit" name ="submit_btn">Login</button>
        <label>
            <input type="checkbox" checked="checked" name="remember"> Remember me
        </label>
    </div>
    </form>

</html>
