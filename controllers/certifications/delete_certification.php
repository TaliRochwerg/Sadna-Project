<?php
session_start();
require_once('../../config/db.php');

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
    $certification_id = $_GET["certification_id"];

    // Escape user inputs to prevent SQL injection
    $certification_id = mysqli_real_escape_string($conn, $certification_id);

    // Delete Certification from the database
    $delete_sql = "DELETE FROM certifications WHERE id = '$certification_id'";

    if ($conn->query($delete_sql) === TRUE) {
        $response = array("status" => "success", "message" => "Certification deleted successfully!");
    } else {
        $response = array("status" => "error", "message" => "Failed to delete Certification: " . $conn->error);
    }

    echo json_encode($response);
    $conn->close();
} else {
    $response = array("status" => "error", "message" => "Invalid request method.");
    echo json_encode($response);
}
?>
