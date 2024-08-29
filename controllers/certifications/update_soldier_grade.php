<?php
session_start();
require_once('../../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $soldier_id = $_POST['soldier_id'];
    $certification_id = $_POST['certification_id'];
    $grade_value = $_POST['soldier_grade'];  // Assuming grade_value is passed

    if (!$soldier_id || !$certification_id || empty($grade_value)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing soldier, certification, or grades data']);
        exit();
    }

    // Start a transaction to ensure both the grade insertion and update succeed
    $conn->begin_transaction();

    try {
        // Check if the grade already exists in the `grades` table for this certification and soldier
        $check_grade_sql = "SELECT id FROM grades WHERE certification_id = ? AND soldier_id = ?";
        $stmt_check_grade = $conn->prepare($check_grade_sql);
        $stmt_check_grade->bind_param('ii', $certification_id, $soldier_id);
        $stmt_check_grade->execute();
        $grade_result = $stmt_check_grade->get_result();

        if ($grade_result->num_rows > 0) {
            // If grade exists, update the grade
            $grade = $grade_result->fetch_assoc();
            $grade_id = $grade['id'];

            $update_grade_sql = "UPDATE grades SET grade = ? WHERE id = ?";
            $stmt_update_grade = $conn->prepare($update_grade_sql);
            $stmt_update_grade->bind_param('ii', $grade_value, $grade_id);

            if (!$stmt_update_grade->execute()) {
                throw new Exception('Failed to update grade');
            }
        } else {
            // If grade does not exist, insert the grade into the `grades` table
            $insert_grade_sql = "INSERT INTO grades (grade, certification_id, soldier_id) VALUES (?, ?, ?)";
            $stmt_insert_grade = $conn->prepare($insert_grade_sql);
            $stmt_insert_grade->bind_param('iii', $grade_value, $certification_id, $soldier_id);

            if (!$stmt_insert_grade->execute()) {
                throw new Exception('Failed to insert grade');
            }
        }

        // Commit the transaction if both operations succeed
        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Grade updated/added successfully']);

    } catch (Exception $e) {
        // Rollback the transaction if any operation fails
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }

    // Close the prepared statements and connection
    $stmt_check_grade->close();
    if (isset($stmt_update_grade)) $stmt_update_grade->close();
    if (isset($stmt_insert_grade)) $stmt_insert_grade->close();
    $conn->close();
}
?>
