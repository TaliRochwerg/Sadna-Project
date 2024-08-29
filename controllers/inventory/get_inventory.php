<?php
session_start();
require_once('../../config/db.php');

// Make sure id is set in the session
if (!isset($_SESSION['user_id'])) {
    $response = array("status" => "error", "message" => "User not logged in.");
    echo json_encode($response);
    exit;
}

// Initialize query parameters
$classFilter = isset($_GET['classFilter']) ? $_GET['classFilter'] : '';
$weaponTypeFilter = isset($_GET['weaponTypeFilter']) ? $_GET['weaponTypeFilter'] : '';

// Fetch inventory records
$data_sql = "
    SELECT 
    iv.id,
    sd.soldier_name,
    sd.personal_number,
    iv.class,
    wp.name AS weapon_name,
    iv.weapon_type,
    iv.item_condition,
    iv.received_date,
    iv.last_test_date,
    iv.sku_number
    FROM inventory AS iv
    INNER JOIN soldiers AS sd ON iv.soldier_id = sd.id
    INNER JOIN weapons AS wp ON iv.weapon_id = wp.id
    WHERE 1=1
";

if (!empty($classFilter)) {
    $data_sql .= " AND iv.class = '" . $conn->real_escape_string($classFilter) . "'";
}

if (!empty($weaponTypeFilter)) {
    $data_sql .= " AND iv.weapon_type = '" . $conn->real_escape_string($weaponTypeFilter) . "'";
}

$result = $conn->query($data_sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id' => $row['id'],
        'soldier_name' => $row['soldier_name'],
        'personal_number' => $row['personal_number'],
        'class' => $row['class'],
        'weapon_name' => $row['weapon_name'],
        'weapon_type' => $row['weapon_type'],
        'sku_number' => $row['sku_number'],
        'item_condition' => $row['item_condition'],
        'received_date' => $row['received_date'],
        'last_test_date' => $row['last_test_date'] // Correctly mapping to the right column
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
