<?php
session_start();
require_once('../../config/db.php');

// Make sure id is set in the session
if (!isset($_SESSION['user_id'])) {
    $response = array("status" => "error", "message" => "User not logged in.");
    echo json_encode($response);
    exit;
}

// Retrieve user_id from session
$user_id = $_SESSION['user_id'];
// Fetch records
$data_sql = "SELECT * FROM orders WHERE user_id = $user_id";
$result = $conn->query($data_sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Prepare data for JSON response
$json_data = [
    "data" => $data,
    "status" => "success"
];

echo json_encode($json_data);

$conn->close();
?>