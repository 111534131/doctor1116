<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/database.php';
require_once 'jwt_helper.php';

function send_error($code, $message) {
    http_response_code($code);
    echo json_encode(['message' => $message]);
    exit();
}

// 1. Get the token from the headers
$auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
if (!$auth_header) {
    send_error(401, 'Access denied. No token provided.');
}

$token = null;
if (preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
    $token = $matches[1];
} else {
    send_error(401, 'Access denied. Token format is invalid.');
}

// 2. Validate the token and check the role
try {
    $jwt_key = "a_super_secret_key_that_is_long_enough_to_be_secure_1234567890";
    $decoded_payload = validate_jwt($token, $jwt_key);

    if ($decoded_payload->role !== 'Admin') {
        send_error(403, 'Forbidden. You do not have permission to access this resource.');
    }

    // 3. If authorized, fetch users from the database
    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT Id, Username, Email, Role FROM Users";
    $stmt = $db->prepare($query);
    if ($stmt === false) {
        throw new Exception("Database prepare failed: " . $db->error);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    
    $users_arr = array();
    while ($row = $result->fetch_assoc()) {
        extract($row);
        $user_item = array(
            "id" => $Id,
            "username" => $Username,
            "email" => $Email,
            "role" => $Role
        );
        array_push($users_arr, $user_item);
    }

    http_response_code(200);
    echo json_encode($users_arr);

    $stmt->close();
    $db->close();

} catch (Exception $e) {
    // This will catch errors from validate_jwt (e.g., expired token) and DB errors
    send_error(500, $e->getMessage());
}
?>
