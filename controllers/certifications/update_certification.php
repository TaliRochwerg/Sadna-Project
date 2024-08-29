<?php
session_start();
require_once('../../config/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $certification_id = $_POST["certification_id"];
    $training_name = $_POST["training_name"];
    $date_time = $_POST["date_time"];
    $location = $_POST["location"];
    $commander_id = $_POST["commander_id"];
    $description = $_POST["description"];
    $soldiers = $_POST["soldiers"];

    // Escape user inputs to prevent SQL injection
    $training_name = mysqli_real_escape_string($conn, $training_name);
    $date_time = mysqli_real_escape_string($conn, $date_time);
    $location = mysqli_real_escape_string($conn, $location);
    $commander_id = mysqli_real_escape_string($conn, $commander_id);
    $description = mysqli_real_escape_string($conn, $description);

    // Update certification details
    $update_sql = "UPDATE certifications SET 
                   training_name = '$training_name', 
                   description = '$description', 
                   date_time = '$date_time', 
                   location = '$location', 
                   commander_id = '$commander_id' 
                   WHERE id = '$certification_id'";

    if ($conn->query($update_sql) === TRUE) {

        // Remove existing soldiers for the certification
        $delete_soldiers_sql = "DELETE FROM certification_soldiers WHERE certification_id = '$certification_id'";
        $conn->query($delete_soldiers_sql);

        // Insert updated soldiers for the certification
        foreach ($soldiers as $soldier_id) {
            $soldier_id = mysqli_real_escape_string($conn, $soldier_id);
            $soldier_insert_sql = "INSERT INTO certification_soldiers (certification_id, soldier_id) 
                                   VALUES ('$certification_id', '$soldier_id')";
            // Check each insertion success
            if (!$conn->query($soldier_insert_sql)) {
                $response = array("status" => "error", "message" => "Error inserting soldier: " . $conn->error);
                echo json_encode($response);
                $conn->close();
                exit();
            }
        }

        $response = array("status" => "success", "message" => "Certification updated successfully");
        echo json_encode($response);
    } else {
        $response = array("status" => "error", "message" => "Error updating certification: " . $conn->error);
        echo json_encode($response);
    }

    $conn->close();
} else {
    $response = array("status" => "error", "message" => "Invalid request method.");
    echo json_encode($response);
}
?>