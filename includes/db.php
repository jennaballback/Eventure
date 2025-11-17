<?php
// database connection file
$host = "localhost";
$username = "root";       // default for XAMPP
$password = "";           // default = empty
$database = "event_planner";  // your DB name

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>


 Every PHP file that needs database access will include this: <?php include 'includes/db.php'; ?>