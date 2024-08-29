<?php
session_start();
require_once('../../config/db.php');

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["certification_id"])) {
    $certification_id = $_GET["certification_id"];

    // Fetch soldiers for the selected certification
    $soldiers_sql = "SELECT s.id, s.soldier_name 
                     FROM certification_soldiers cs
                     JOIN soldiers s ON cs.soldier_id = s.id
                     WHERE cs.certification_id = ?";
                     
    $stmt = $conn->prepare($soldiers_sql);
    $stmt->bind_param("i", $certification_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $soldiers = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $soldiers[] = $row;
        }
    }

    $response = array("status" => "success", "soldiers" => $soldiers);
    echo json_encode($response);
} else {
    $response = array("status" => "error", "message" => "Invalid request.");
    echo json_encode($response);
}
?>