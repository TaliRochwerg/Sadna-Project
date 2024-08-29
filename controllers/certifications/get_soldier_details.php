<?php
session_start();
require_once('../../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $soldier_id = $_POST['soldier_id'];
    $certification_id = $_POST['certification_id'];

    if (!$soldier_id || !$certification_id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid soldier or certification ID']);
        exit();
    }

    // Fetch the soldier's grades for this certification
    $sql = "SELECT grades.grade, grades.id as grade_id 
            FROM grades
            WHERE grades.certification_id = ? 
            AND grades.soldier_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $certification_id, $soldier_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $grades = [];
    while ($row = $result->fetch_assoc()) {
        $grades[] = $row;
    }

    if (count($grades) > 0) {
        echo json_encode(['status' => 'success', 'grades' => $grades]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No grades found for this soldier']);
    }

    $stmt->close();
    $conn->close();
}
?>
