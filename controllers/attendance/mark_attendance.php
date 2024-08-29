<?php
session_start();
require_once('../../config/db.php');

if (!isset($_SESSION['user'])) {
    header('Location: ../../login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $attendance_date = $_POST["date"];
    $arrived = $_POST["arrived"];
    $note = $_POST["note"];
    $certification_ids = $_POST["certification_id"];

    // Escape user inputs to prevent SQL injection
    $attendance_date = mysqli_real_escape_string($conn, $attendance_date);

    $allSuccess = true;

    foreach ($arrived as $soldier_id => $status) {
        $attended = $status ? 1 : 0;
        $note_text = isset($note[$soldier_id]) ? $note[$soldier_id] : '';
        $certification_id = isset($certification_ids[$soldier_id]) && !empty($certification_ids[$soldier_id]) ? $certification_ids[$soldier_id] : null;

        // Escape each soldier's input
        $soldier_id = mysqli_real_escape_string($conn, $soldier_id);
        $note_text = mysqli_real_escape_string($conn, $note_text);
        if (!is_null($certification_id)) {
            $certification_id = mysqli_real_escape_string($conn, $certification_id);
        }

        // Check if attendance record exists for the soldier and date
        $check_sql = "SELECT id FROM attendance WHERE soldier_id = ? AND attendance_date = ?";
        $check_stmt = $conn->prepare($check_sql);
        if ($check_stmt === false) {
            $allSuccess = false;
            $response = array("status" => "error", "message" => "שגיאה בבדיקת הנוכחות: " . $conn->error);
            echo json_encode($response);
            $conn->close();
            exit();
        }

        $check_stmt->bind_param("is", $soldier_id, $attendance_date);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $check_stmt->close();

        if ($result->num_rows > 0) {
            // Attendance exists, update it
            $row = $result->fetch_assoc();
            $attendance_id = $row['id'];

            $update_sql = "UPDATE attendance 
                           SET attended = ?, note = ?, certification_id = ?
                           WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            if ($update_stmt === false) {
                $allSuccess = false;
                $response = array("status" => "error", "message" => "שגיאה בעדכון הנוכחות: " . $conn->error);
                echo json_encode($response);
                $conn->close();
                exit();
            }

            $update_stmt->bind_param("issi", $attended, $note_text, $certification_id, $attendance_id);
            if (!$update_stmt->execute()) {
                $allSuccess = false;
                $response = array("status" => "error", "message" => "שגיאה בביצוע העדכון: " . $update_stmt->error);
                echo json_encode($response);
                $update_stmt->close();
                $conn->close();
                exit();
            }
            $update_stmt->close();
        } else {
            // Attendance does not exist, insert a new record
            $insert_sql = "INSERT INTO attendance (soldier_id, certification_id, attendance_date, attended, note) 
                           VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            if ($insert_stmt === false) {
                $allSuccess = false;
                $response = array("status" => "error", "message" => "שגיאה בהכנת השאילתה: " . $conn->error);
                echo json_encode($response);
                $conn->close();
                exit();
            }

            $insert_stmt->bind_param("iisss", $soldier_id, $certification_id, $attendance_date, $attended, $note_text);
            if (!$insert_stmt->execute()) {
                $allSuccess = false;
                $response = array("status" => "error", "message" => "שגיאה בביצוע השאילתה: " . $insert_stmt->error);
                echo json_encode($response);
                $insert_stmt->close();
                $conn->close();
                exit();
            }
            $insert_stmt->close();
        }
    }

    if ($allSuccess) {
        $response = array("status" => "success", "message" => "נוכחות סומנה בהצלחה.");
    } else {
        $response = array("status" => "error", "message" => "שגיאה בסימון נוכחות.");
    }

    echo json_encode($response);
    $conn->close();
} else {
    $response = array("status" => "error", "message" => "שיטת בקשה לא תקפה.");
    echo json_encode($response);
}
?>
