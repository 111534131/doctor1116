<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    exit(0);
}

header('Content-Type: application/json; charset=utf-8');

require_once '../config/database.php';
require_once 'jwt_helper.php';

// --- Main Logic ---
$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->email) || !isset($data->password)) {
    http_response_code(400);
    echo json_encode(['message' => 'Incomplete login data.']);
    exit();
}

$email = $data->email;
$password = $data->password;

try {
    $stmt = $db->prepare("SELECT Id, Username, Email, PasswordHash, Role FROM Users WHERE Email = ?");
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $db->error);
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result(); // Store the result set

    if ($stmt->num_rows === 1) {
        // Bind the result variables
        $stmt->bind_result($id, $username, $email_from_db, $passwordHash, $role);
        $stmt->fetch();

        $user = [
            'Id' => $id,
            'Username' => $username,
            'Email' => $email_from_db,
            'PasswordHash' => $passwordHash,
            'Role' => $role
        ];

        if (password_verify($password, $user['PasswordHash'])) {
            // Password is correct, generate JWT
            $jwt_key = "a_super_secret_key_that_is_long_enough_to_be_secure_1234567890";
            $jwt_issuer = "https://localhost:7188"; // As per appsettings.json
            $jwt_audience = "https://localhost:7188"; // As per appsettings.json

            $token = generate_jwt($user, $jwt_key, $jwt_issuer, $jwt_audience);

            http_response_code(200);
            echo json_encode(['token' => $token]);
        } else {
            // Wrong password
            http_response_code(401);
            echo json_encode(['message' => 'Login failed. Invalid credentials.']);
        }
    } else {
        // User not found
        http_response_code(401);
        echo json_encode(['message' => 'Login failed. User not found.']);
    }

    $stmt->close();
    $db->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'A server error occurred.', 'error' => $e->getMessage()]);
}
?>
