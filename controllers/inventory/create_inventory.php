<?php
session_start();
require_once('../../config/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $soldier_id = $_POST["soldier_id"];
    $class = $_POST["class"];
    $weapon_id = $_POST["weapon_id"];
    $weapon_type = $_POST["weapon_type"];
    $item_condition = $_POST["item_condition"];
    $sku_number = $_POST["sku_number"];
    $received_date = $_POST["received_date"];
    $last_test_date = $_POST["last_test_date"];

    // Escape user inputs to prevent SQL injection
    $soldier_id = mysqli_real_escape_string($conn, $soldier_id);
    $class = mysqli_real_escape_string($conn, $class);
    $weapon_id = mysqli_real_escape_string($conn, $weapon_id);
    $weapon_type = mysqli_real_escape_string($conn, $weapon_type);
    $item_condition = mysqli_real_escape_string($conn, $item_condition);
    $sku_number = mysqli_real_escape_string($conn, $sku_number);
    $received_date = mysqli_real_escape_string($conn, $received_date);
    $last_test_date = mysqli_real_escape_string($conn, $last_test_date);

    // Insert new ניהול מלאי into the database
    $insert_sql = "INSERT INTO inventory (soldier_id, class, sku_number, weapon_id, weapon_type, item_condition, received_date, last_test_date) 
                   VALUES ('$soldier_id', '$class', '$sku_number', '$weapon_id', '$weapon_type', '$item_condition', '$received_date', '$last_test_date')";

    if ($conn->query($insert_sql) === TRUE) {
        $response = array("status" => "success", "message" => "ניהול מלאי created successfully");
        echo json_encode($response);
    } else {
        $response = array("status" => "error", "message" => "Error inserting ניהול מלאי: " . $conn->error);
        echo json_encode($response);
    }

    $conn->close();
} else {
    $response = array("status" => "error", "message" => "Invalid request method.");
    echo json_encode($response);
}
?>