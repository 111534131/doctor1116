<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->username) &&
    !empty($data->email) &&
    !empty($data->password)
) {
    // Check if email already exists
    $email_check_query = "SELECT Id FROM Users WHERE Email = ?";
    $stmt = $db->prepare($email_check_query);
    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(array("message" => "Database error on email check."));
        exit();
    }
    $stmt->bind_param("s", $data->email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        http_response_code(409); // Conflict
        echo json_encode(array("message" => "User with this email already exists."));
        $stmt->close();
        $db->close();
        exit();
    }
    $stmt->close();

    // Create the new user
    $user_query = "INSERT INTO Users (Username, Email, PasswordHash, Role) VALUES (?, ?, ?, 'User')";
    $stmt = $db->prepare($user_query);
    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(array("message" => "Database error on user creation."));
        exit();
    }

    // Use PASSWORD_DEFAULT for better security and future compatibility
    $password_hash = password_hash($data->password, PASSWORD_DEFAULT); 

    $stmt->bind_param("sss", $data->username, $data->email, $password_hash);

    if ($stmt->execute()) {
        $new_user_id = $db->insert_id;
        $stmt->close();

        // Also create a patient record for the new user
        $patient_query = "INSERT INTO Patients (UserId, Name, ContactInfo, DateOfBirth) VALUES (?, ?, ?, ?)";
        $patient_stmt = $db->prepare($patient_query);
        if ($patient_stmt === false) {
            http_response_code(500);
            echo json_encode(array("message" => "Database error on patient creation."));
            exit();
        }
        
        $dob = date('Y-m-d H:i:s', strtotime('-30 years')); // Placeholder DOB
        $patient_stmt->bind_param("isss", $new_user_id, $data->username, $data->email, $dob);

        if ($patient_stmt->execute()) {
            http_response_code(201);
            echo json_encode(array("message" => "User was created successfully."));
        } else {
            // This case is tricky, user is created but patient is not.
            // For simplicity, we'll report a server error.
            http_response_code(500);
            echo json_encode(array("message" => "Failed to create patient record for the user."));
        }
        $patient_stmt->close();

    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Unable to create user."));
    }

    $db->close();
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to create user. Data is incomplete."));
}
?>
