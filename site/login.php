<?php
include_once("SqlConnection.php");
?>
<?php
if(isset($_REQUEST['submit_btn'])) {

    $uname = $_REQUEST['username'];
    $pass = $_REQUEST['password'];
    $sql = "SELECT uname,pasw from user_info";
    $result = mysqli_query($conn, $sql);
    if(mysqli_num_rows($result) > 0) {
        while ($row = $result->fetch_assoc()) {
            $hsh = $row['pasw'];
            $uname = $row['uname'];
            if(password_verify($pass, $hsh)) {
                session_start();
                $_SESSION['loggedIn'] = true;
                $_SESSION['username'] = $uname;
                session_write_close();
                header('location:adminDash.php');
                exit;
            } else {
                //echo"no match";
            }
        }
        echo "Incorrect Username or Password";
    } else {
        echo "Database empty or does not exist";
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

        .cookie-notice {
            background-color: #ffcc00;
            color: #000000;
            padding: 14px 20px;
            margin: 8px 0;
            text-align: center;
            font-weight: bold;
        }
    </style>
</body>
<title>Admin Login Page</title>
<form method="POST" action='login.php'>

    <div class="container">
        <p class="cookie-notice">
            Cookies are used here to retain your log-in to the Admin functions until you log-out.
            By logging in, you agree to our use of cookies for session management. 
        </p>
        <label for="username"><b>Username</b></label>
        <input type="text" placeholder="Enter Username" name="username" required>

        <label for="password"><b>Password</b></label>
        <input type="password" placeholder="Enter Password" name="password" required>

        <button type="submit" name ="submit_btn" value ="submit_btn">Login</button>
        <label>
            <input type="checkbox" checked="checked" name="remember"> Remember me
        </label>
    </div>
    </form>

</html>
