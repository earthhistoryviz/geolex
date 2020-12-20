<html>
<head>

</head>
<body>
<hr>
<div class="mainBody aside-right" id="conts">
    <div class="center">
<table style = margin-right:10px;>
    <tr>
        <th>UserName</th>
        <th>Admin</th>
    </tr>
<?php
//include("navBar.php");
include_once("SqlConnection.php");
$sql = "SELECT * FROM user_info ";
$result = mysqli_query($conn, $sql);
while($row = mysqli_fetch_array($result)) {
    $user = $row['uname'];
    $adm = $row['admin'];
        echo "<tr>";
        echo"                             ";
        echo "<td>" . $user ."</td>";
       echo "                             ";
       echo "<td>" . $adm . "</td>";
        echo "</tr>";

}
?>
</table>
        <button onclick = "window.location.href = '/Signup.php'">Add a user </button>
</body>
</html>
