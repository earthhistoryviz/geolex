<?php
session_start();
#checks to see if user is logged in
if (!$_SESSION["loggedIn"]) {
    echo "ERROR: You must be logged in to access this page.";
    exit(0);
}
include("adminDash.php");
include_once("SqlConnection.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Management</title>
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f4f4f4;
    }
    .mainBody {
        width: 50%;
    }
    .center {
        background-color: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    table {
        border-collapse: collapse;
        width: 100%;
        margin: 0 auto;
    }
    th, td {
        text-align: left;
        padding: 8px;
    }
    tr:nth-child(even) {
        background-color: #f2f2f2;
    }
    th {
        background-color: #4CAF50;
        color: white;
    }
    button {
        background-color: #4CAF50;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        margin-top: 20px;
    }
    button:hover {
        background-color: #45a049;
    }
</style>
</head>
<body>
<div class="mainBody aside-right" id="conts">
    <div class="center">
        <table>
            <tr>
                <th>UserName</th>
                <th>Admin</th>
            </tr>
            <?php
            $sql = "SELECT * FROM user_info";
$result = mysqli_query($conn, $sql);
while($row = mysqli_fetch_array($result)) {
    $user = $row['uname'];
    $adm = $row['admin'];
    echo "<tr>";
    echo "<td>" . htmlspecialchars($user) . "</td>";
    echo "<td>" . htmlspecialchars($adm) . "</td>";
    echo "</tr>";
}
?>
        </table>
        <button onclick="window.location.href='/Signup.php'">Add a user</button>
    </div>
</div>
</body>
</html>
