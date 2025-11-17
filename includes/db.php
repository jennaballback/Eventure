<?php
// database connection file

$host = "localhost";
$username = "root";        // default for XAMPP
$password = "";            // default is empty
$database = "event_planner"; // your DB name

// create connection
$conn = mysqli_connect($host, $username, $password, $database);

// check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Any PHP file that needs database access will include this file:
// include 'includes/db.php';
?>