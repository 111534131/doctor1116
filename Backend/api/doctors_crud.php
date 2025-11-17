<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../jwt_helper.php';

function send_json_response($code, $data) {
    http_response_code($code);
    echo json_encode($data);
    exit();
}

$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'POST':
        handle_post_request();
        break;
    case 'DELETE':
        handle_delete_request();
        break;
    default:
        send_json_response(405, ['message' => 'Method Not Allowed']);
        break;
}

function handle_delete_request() {
    // Get Doctor ID from query parameter
    $doctor_id = $_GET['id'] ?? null;

    if (!is_numeric($doctor_id)) {
        send_json_response(400, ['message' => 'Invalid or missing Doctor ID specified.']);
    }

    // Authenticate and authorize the user
    try {
        $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        if (!$auth_header) send_json_response(401, ['message' => 'Access denied. No token provided.']);
        
        $token = preg_match('/Bearer\s(\S+)/', $auth_header, $matches) ? $matches[1] : null;
        if (!$token) send_json_response(401, ['message' => 'Access denied. Token format is invalid.']);
        
        $jwt_key = "a_super_secret_key_that_is_long_enough_to_be_secure_1234567890";
        $user_payload = validate_jwt($token, $jwt_key);

        if ($user_payload->role !== 'Admin') {
            send_json_response(403, ['message' => 'Forbidden. You do not have permission to perform this action.']);
        }
    } catch (Exception $e) {
        send_json_response(401, ['message' => 'Access denied. ' . $e->getMessage()]);
    }

    try {
        $db = (new Database())->getConnection();
        $stmt = $db->prepare("DELETE FROM Doctors WHERE Id = ?");
        $stmt->bind_param("i", $doctor_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                send_json_response(200, ['message' => 'Doctor deleted successfully.']);
            } else {
                send_json_response(404, ['message' => 'Doctor not found.']);
            }
        } else {
            throw new Exception("Failed to delete doctor: " . $stmt->error);
        }
        
        $stmt->close();
        $db->close();

    } catch (Exception $e) {
        send_json_response(500, ['message' => 'An internal server error occurred.', 'error' => $e->getMessage()]);
    }
}

function handle_post_request() {
    // Authenticate and authorize the user
    try {
        $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        if (!$auth_header) send_json_response(401, ['message' => 'Access denied. No token provided.']);
        
        $token = preg_match('/Bearer\s(\S+)/', $auth_header, $matches) ? $matches[1] : null;
        if (!$token) send_json_response(401, ['message' => 'Access denied. Token format is invalid.']);
        
        $jwt_key = "a_super_secret_key_that_is_long_enough_to_be_secure_1234567890";
        $user_payload = validate_jwt($token, $jwt_key);

        if ($user_payload->role !== 'Admin') {
            send_json_response(403, ['message' => 'Forbidden. You do not have permission to perform this action.']);
        }
    } catch (Exception $e) {
        send_json_response(401, ['message' => 'Access denied. ' . $e->getMessage()]);
    }

    // Get input data
    $data = json_decode(file_get_contents("php://input"));
    if (!$data || !isset($data->name) || !isset($data->specialty) || !isset($data->userId)) {
        send_json_response(400, ['message' => 'Incomplete data. name, specialty, and userId are required.']);
    }

    try {
        $db = (new Database())->getConnection();

        $stmt = $db->prepare("INSERT INTO Doctors (Name, Specialty, UserId) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $data->name, $data->specialty, $data->userId);
        
        if ($stmt->execute()) {
            $new_doctor_id = $stmt->insert_id;
            $new_doctor = [
                'id' => $new_doctor_id,
                'name' => $data->name,
                'specialty' => $data->specialty,
                'userId' => $data->userId
            ];
            send_json_response(201, $new_doctor);
        } else {
            // Check for duplicate entry for UserId
            if ($db->errno === 1062) {
                 send_json_response(409, ['message' => 'This user is already assigned to another doctor profile.']);
            }
            throw new Exception("Failed to create doctor: " . $stmt->error);
        }
        
        $stmt->close();
        $db->close();

    } catch (Exception $e) {
        send_json_response(500, ['message' => 'An internal server error occurred.', 'error' => $e->getMessage()]);
    }
}
?>