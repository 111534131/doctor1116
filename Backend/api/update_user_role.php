<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../jwt_helper.php';

function send_json_response($code, $data) {
    http_response_code($code);
    echo json_encode($data);
    exit();
}

$request_method = $_SERVER["REQUEST_METHOD"];

// --- Get User ID from query parameter ---
$user_id = $_GET['id'] ?? null;

if (!is_numeric($user_id)) {
    send_json_response(400, ['message' => 'Invalid or missing User ID specified.']);
}

// --- Authentication and Authorization ---
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
    if ($user_payload->id == $user_id) {
        send_json_response(403, ['message' => 'Admins cannot change their own role.']);
    }
} catch (Exception $e) {
    send_json_response(401, ['message' => 'Access denied. ' . $e->getMessage()]);
}

// --- Get Input Data ---
$data = json_decode(file_get_contents("php://input"));
if (!$data || !isset($data->role) || !in_array($data->role, ['Admin', 'Doctor', 'User'])) {
    send_json_response(400, ['message' => 'Invalid data. A valid role (Admin, Doctor, User) is required.']);
}

// --- Database Update ---
try {
    $db = (new Database())->getConnection();
    $stmt = $db->prepare("UPDATE Users SET Role = ? WHERE Id = ?");
    $stmt->bind_param("si", $data->role, $user_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            send_json_response(200, ['message' => 'User role updated successfully.']);
        } else {
            send_json_response(404, ['message' => 'User not found or role is already set to the specified value.']);
        }
    } else {
        throw new Exception("Failed to update user role: " . $stmt->error);
    }
    
    $stmt->close();
    $db->close();

} catch (Exception $e) {
    send_json_response(500, ['message' => 'An internal server error occurred.', 'error' => $e->getMessage()]);
}
?>
