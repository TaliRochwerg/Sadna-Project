<?php
session_start();
require_once('../../config/db.php');

if (!isset($_SESSION['user'])) {
    header('Location: ./login.php');
    exit();
}

$sql = "SELECT a.id, a.attendance_date, c.training_name AS certification_name, s.soldier_name AS soldier_name, a.attended 
        FROM attendance a 
        JOIN certifications c ON a.certification_id = c.id 
        JOIN soldiers s ON a.soldier_id = s.id";

$result = $conn->query($sql);

$events = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $key = $row['attendance_date'] . '-' . $row['certification_name'];
        // Create or overwrite an event with the same date-title key
        $events[$key] = [
            'id' => $row['id'],
            'title' => $row['certification_name'],
            'start' => $row['attendance_date'],
            'backgroundColor' => $row['attended'] ? '#28a745' : '#dc3545',
            'borderColor' => $row['attended'] ? '#28a745' : '#dc3545',
        ];
    }
}

// Reset array keys to ensure JSON encoding doesn't include keys
$events = array_values($events);

header('Content-Type: application/json');
echo json_encode($events);
?>
