<?php
session_start();
require_once('../../config/db.php');

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
    $inventory_id = $_GET["inventory_id"];

    // Escape user inputs to prevent SQL injection
    $inventory_id = mysqli_real_escape_string($conn, $inventory_id);

    // Delete ניהול מלאי from the database
    $delete_sql = "DELETE FROM inventory 
                   WHERE id = '$inventory_id'";

    if ($conn->query($delete_sql) === TRUE) {
        $response = array("status" => "success", "message" => "ניהול מלאי deleted successfully!");
    } else {
        $response = array("status" => "error", "message" => "Failed to delete inventory: " . $conn->error);
    }

    echo json_encode($response);
    $conn->close();
} else {
    $response = array("status" => "error", "message" => "Invalid request method.");
    echo json_encode($response);
}
?>
