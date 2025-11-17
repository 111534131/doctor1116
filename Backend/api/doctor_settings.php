<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../../../config/database.php';
require_once '../../../jwt_helper.php';

function send_json_response($code, $data) {
    http_response_code($code);
    echo json_encode($data);
    exit();
}

// --- Authentication ---
$user_payload = null;
try {
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
    if (!$auth_header) send_json_response(401, ['message' => 'Access denied. No token provided.']);
    $token = preg_match('/Bearer\s(\S+)/', $auth_header, $matches) ? $matches[1] : null;
    if (!$token) send_json_response(401, ['message' => 'Access denied. Token format is invalid.']);
    $jwt_key = "a_super_secret_key_that_is_long_enough_to_be_secure_1234567890";
    $user_payload = validate_jwt($token, $jwt_key);
    if ($user_payload->role !== 'Doctor') {
        send_json_response(403, ['message' => 'Forbidden. This resource is only available to doctors.']);
    }
} catch (Exception $e) {
    send_json_response(401, ['message' => 'Access denied. ' . $e->getMessage()]);
}

// --- Get Doctor ID from User ID ---
$doctor_id = null;
try {
    $db = (new Database())->getConnection();
    $stmt = $db->prepare("SELECT Id FROM Doctors WHERE UserId = ?");
    $stmt->bind_param("i", $user_payload->id);
    $stmt->execute();
    $doctor = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($doctor) {
        $doctor_id = $doctor['Id'];
    } else {
        send_json_response(404, ['message' => 'Doctor profile not found for the current user.']);
    }
} catch (Exception $e) {
     send_json_response(500, ['message' => 'Failed to retrieve doctor profile.', 'error' => $e->getMessage()]);
}


$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        handle_get_request($db, $doctor_id);
        break;
    case 'PUT':
        handle_put_request($db, $doctor_id);
        break;
    default:
        $db->close();
        send_json_response(405, ['message' => 'Method Not Allowed']);
        break;
}

function handle_get_request($db, $doctor_id) {
    try {
        $stmt = $db->prepare("SELECT CancellationPolicyHours FROM Doctors WHERE Id = ?");
        $stmt->bind_param("i", $doctor_id);
        $stmt->execute();
        $settings = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $db->close();
        if ($settings) {
            send_json_response(200, $settings);
        } else {
            send_json_response(404, ['message' => 'Settings not found.']);
        }
    } catch (Exception $e) {
        $db->close();
        send_json_response(500, ['message' => 'An internal server error occurred.', 'error' => $e->getMessage()]);
    }
}

function handle_put_request($db, $doctor_id) {
    $data = json_decode(file_get_contents("php://input"));
    if (!$data || !isset($data->cancellationPolicyHours) || !is_numeric($data->cancellationPolicyHours)) {
        $db->close();
        send_json_response(400, ['message' => 'Invalid data. cancellationPolicyHours (numeric) is required.']);
    }

    try {
        $stmt = $db->prepare("UPDATE Doctors SET CancellationPolicyHours = ? WHERE Id = ?");
        $stmt->bind_param("ii", $data->cancellationPolicyHours, $doctor_id);
        
        if ($stmt->execute()) {
            send_json_response(200, ['message' => 'Settings updated successfully.']);
        } else {
            throw new Exception("Failed to update settings: " . $stmt->error);
        }
        $stmt->close();
        $db->close();
    } catch (Exception $e) {
        $db->close();
        send_json_response(500, ['message' => 'An internal server error occurred.', 'error' => $e->getMessage()]);
    }
}
?>
