<?php
session_start();
require_once('../../config/db.php');

// Make sure id is set in the session
if (!isset($_SESSION['user_id'])) {
    $response = array("status" => "error", "message" => "User not logged in.");
    echo json_encode($response);
    exit;
}

// Fetch certifications records linked to the user (commander)
$data_sql = "
SELECT 
    c.id,
    c.training_id,
    c.training_name,
    c.description,
    c.date_time,
    c.location,
    c.commander_id,
    u.username AS commander_username,
    COUNT(s.id) AS soldier_count
FROM certifications AS c
LEFT JOIN users AS u ON c.commander_id = u.id
LEFT JOIN certification_soldiers AS cs ON c.id = cs.certification_id
LEFT JOIN soldiers AS s ON cs.soldier_id = s.id
GROUP BY c.id, 
         c.training_id, 
         c.training_name, 
         c.description, 
         c.date_time, 
         c.location, 
         c.commander_id, 
         u.username;
";

$result = $conn->query($data_sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id' => $row['id'],
        'training_id' => $row['training_id'],
        'training_name' => $row['training_name'],
        'description' => $row['description'],
        'date_time' => $row['date_time'],
        'location' => $row['location'],
        'commander_username' => $row['commander_username'],
        'commander_id' => $row['commander_id'],
        'soldier_count' => $row['soldier_count']
    ];
}

// Prepare data for JSON response
$json_data = [
    "data" => $data,
    "status" => "success"
];

echo json_encode($json_data);

$conn->close();
?>
