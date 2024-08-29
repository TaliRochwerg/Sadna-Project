<?php
$servername = "localhost";
$username = "isfrenkelma";
$password = "Meri_4Se=P";
$dbname = "isfrenkelma_compass";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
