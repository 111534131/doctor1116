<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../../config/database.php';
require_once '../../jwt_helper.php';

function send_json_response($code, $data) {
    http_response_code($code);
    echo json_encode($data);
    exit();
}

// Get User ID from URL
$uri_parts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$user_id = end($uri_parts);

if (!is_numeric($user_id)) {
    send_json_response(400, ['message' => 'Invalid User ID specified.']);
}

// Authenticate the user
try {
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
    if (!$auth_header) {
        send_json_response(401, ['message' => 'Access denied. No token provided.']);
    }

    $token = null;
    if (preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
        $token = $matches[1];
    } else {
        send_json_response(401, ['message' => 'Access denied. Token format is invalid.']);
    }
    
    $jwt_key = "a_super_secret_key_that_is_long_enough_to_be_secure_1234567890";
    $decoded_payload = validate_jwt($token, $jwt_key);

    // Authorization check: User can only access their own patient data, or Admin can access any.
    if ($decoded_payload->role !== 'Admin' && $decoded_payload->id != $user_id) {
        send_json_response(403, ['message' => 'Forbidden. You do not have permission to access this resource.']);
    }

} catch (Exception $e) {
    send_json_response(401, ['message' => 'Access denied. ' . $e->getMessage()]);
}

// Fetch patient data from the database
try {
    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT Id, Name, ContactInfo, DateOfBirth, UserId FROM Patients WHERE UserId = ?";
    
    $stmt = $db->prepare($query);
    if ($stmt === false) {
        throw new Exception("Database prepare failed: " . $db->error);
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $patient = $result->fetch_assoc();
        send_json_response(200, $patient);
    } else {
        send_json_response(404, ['message' => 'Patient not found for the specified User ID.']);
    }

    $stmt->close();
    $db->close();

} catch (Exception $e) {
    send_json_response(500, ['message' => 'An internal server error occurred.', 'error' => $e->getMessage()]);
}
?>
