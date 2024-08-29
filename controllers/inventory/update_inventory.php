<?php
session_start();
require_once('../../config/db.php');

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inventory_id = $_POST["inventory_id"];
    $soldier_id = $_POST["soldier_id"];
    $class = $_POST["class"];
    $weapon_id = $_POST["weapon_id"];
    $weapon_type = $_POST["weapon_type"];
    $item_condition = $_POST["item_condition"];
    $sku_number = $_POST["sku_number"];
    $received_date = $_POST["received_date"];
    $last_test_date = $_POST["last_test_date"];

    // Escape user inputs to prevent SQL injection
    $inventory_id = mysqli_real_escape_string($conn, $inventory_id);
    $soldier_id = mysqli_real_escape_string($conn, $soldier_id);
    $class = mysqli_real_escape_string($conn, $class);
    $weapon_id = mysqli_real_escape_string($conn, $weapon_id);
    $weapon_type = mysqli_real_escape_string($conn, $weapon_type);
    $item_condition = mysqli_real_escape_string($conn, $item_condition);
    $sku_number = mysqli_real_escape_string($conn, $sku_number);
    $received_date = mysqli_real_escape_string($conn, $received_date);
    $last_test_date = mysqli_real_escape_string($conn, $last_test_date);

    // Update ניהול מלאי in the database
    $update_sql = "UPDATE inventory SET 
                       soldier_id = '$soldier_id', 
                       class = '$class', 
                       weapon_id = '$weapon_id', 
                       weapon_type = '$weapon_type', 
                       item_condition = '$item_condition',
                       sku_number = '$sku_number',  
                       received_date = '$received_date', 
                       last_test_date = '$last_test_date' 
                   WHERE id = '$inventory_id'";

    if ($conn->query($update_sql) === TRUE) {
        $response = array("status" => "success", "message" => "ניהול מלאי updated successfully!");
    } else {
        $response = array("status" => "error", "message" => "Failed to update inventory: " . $conn->error);
    }

    echo json_encode($response);
    $conn->close();
} else {
    $response = array("status" => "error", "message" => "Invalid request method.");
    echo json_encode($response);
}
?>
