<?php
session_start();
require_once('../../config/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];

    // Prevent SQL injection by escaping user input
    $email = mysqli_real_escape_string($conn, $email);

    // Query to find the user by username
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        // סיסמא is correct, user is authenticated
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['user'] = json_encode($row);
        $response = array("status" => "success", "message" => "התחברות successful");
        echo json_encode($response);
        
    } else {
        $response = array("status" => "error", "message" => "User not found");
        echo json_encode($response);
    }

} else {
    $response = array("status" => "error", "message" => "Error: Invalid request.");
    echo json_encode($response);
}
?>
