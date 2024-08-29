<?php
session_start();
require_once('../../config/db.php');

if (!isset($_SESSION['user'])) {
    header('Location: ../../login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $certification_id = $_GET["certification_id"];
    $attendance_date = $_GET["date"];

    // Escape user inputs to prevent SQL injection
    $certification_id = mysqli_real_escape_string($conn, $certification_id);
    $attendance_date = mysqli_real_escape_string($conn, $attendance_date);

    // Fetch attendance records
    $sql = "
        SELECT a.soldier_id, s.soldier_name, a.attended, a.note
        FROM attendance a
        JOIN soldiers s ON a.soldier_id = s.id
        WHERE a.certification_id = '$certification_id' AND a.attendance_date = '$attendance_date'
    ";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $attendance = [];
        while ($row = $result->fetch_assoc()) {
            $attendance[] = [
                'soldier_id' => $row['soldier_id'],
                'soldier_name' => $row['soldier_name'],
                'attended' => $row['attended'],
                'note' => $row['note']
            ];
        }
        $response = array("status" => "success", "attendance" => $attendance);
    } else {
        $response = array("status" => "error", "message" => "אין נתוני נוכחות להצגה.");
    }

    echo json_encode($response);
    $conn->close();
} else {
    $response = array("status" => "error", "message" => "שיטת בקשה לא תקפה.");
    echo json_encode($response);
}
?>
