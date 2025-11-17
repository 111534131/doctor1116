<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../jwt_helper.php';

function send_json_response($code, $data) {
    http_response_code($code);
    echo json_encode($data);
    exit();
}

// --- Authentication ---
try {
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
    if (!$auth_header) send_json_response(401, ['message' => 'Access denied. No token provided.']);
    $token = preg_match('/Bearer\s(\S+)/', $auth_header, $matches) ? $matches[1] : null;
    if (!$token) send_json_response(401, ['message' => 'Access denied. Token format is invalid.']);
    $jwt_key = "a_super_secret_key_that_is_long_enough_to_be_secure_1234567890";
    $user_payload = validate_jwt($token, $jwt_key);
    if (!in_array($user_payload->role, ['Admin', 'Doctor'])) {
        send_json_response(403, ['message' => 'Forbidden. You do not have permission to access this resource.']);
    }
} catch (Exception $e) {
    send_json_response(401, ['message' => 'Access denied. ' . $e->getMessage()]);
}

// --- Fetch Data ---
try {
    $db = (new Database())->getConnection();
    $stmt = $db->prepare("SELECT Id, Name, ContactInfo, DateOfBirth, UserId FROM Patients ORDER BY Name ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    $patients = [];
    while($row = $result->fetch_assoc()) {
        $patients[] = $row;
    }
    $stmt->close();
    $db->close();
    send_json_response(200, $patients);
} catch (Exception $e) {
    send_json_response(500, ['message' => 'An internal server error occurred.', 'error' => $e->getMessage()]);
}
?>
