<?php
$host = "localhost";
$user = "root";  // change if needed
$pass = "";      // your MySQL password
$dbname = "college_portal111";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
