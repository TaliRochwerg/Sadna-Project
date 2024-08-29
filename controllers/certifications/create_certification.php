<?php
session_start();
require_once('../../config/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

    // Check if training_name already exists
    $check_sql = "SELECT * FROM certifications WHERE training_name = '$training_name'";
    $result = $conn->query($check_sql);

    if ($result->num_rows > 0) {
        $response = array("status" => "error", "message" => "שם ההדרכה כבר קיים.");
        echo json_encode($response);
    } else {
        // Generate a unique training_id
        $training_id = 'TRN' . substr(uniqid(), -6);

        // Insert new certification into the database
        $insert_sql = "INSERT INTO certifications (training_id, training_name, description, date_time, location, commander_id) 
                       VALUES ('$training_id', '$training_name', '$description', '$date_time', '$location', '$commander_id')";
        
        // If the insertion is successful, proceed to add the soldiers associated with this certification
        if ($conn->query($insert_sql) === TRUE) {
            $certification_id = $conn->insert_id;
            
            // Loop through each soldier selected in the form and insert them into the 'certification_soldiers' table
            foreach ($soldiers as $soldier_id) {
                $soldier_id = mysqli_real_escape_string($conn, $soldier_id);
                $soldier_insert_sql = "INSERT INTO certification_soldiers (certification_id, soldier_id) 
                                       VALUES ('$certification_id', '$soldier_id')";
                if (!$conn->query($soldier_insert_sql)) {
                    $response = array("status" => "error", "message" => "שגיאה בהוספת חייל: " . $conn->error);
                    echo json_encode($response);
                    $conn->close();
                    exit();
                }
            }

            $response = array("status" => "success", "message" => "האימון נוצרה בהצלחה.");
            echo json_encode($response);
        } else {
            $response = array("status" => "error", "message" => "שגיאה בהוספת האימון: " . $conn->error);
            echo json_encode($response);
        }
    }

    $conn->close();
} else {
    $response = array("status" => "error", "message" => "שיטת בקשה לא תקפה.");
    echo json_encode($response);
}
?>
