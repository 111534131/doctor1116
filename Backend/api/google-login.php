<?php
header("Cross-Origin-Opener-Policy: same-origin-allow-popups");

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
require_once 'jwt_helper.php'; // Include our JWT helper functions

// --- Google Client ID from appsettings.json ---
// This should ideally be loaded from a secure config file, not hardcoded.
// For this exercise, we'll use the value from appsettings.json
$google_client_id = '1180508929-1oneb26vknepibg9v0o45ofjtc7tgkcs.apps.googleusercontent.com';

// --- Main Logic ---
$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->credential)) {
    http_response_code(400);
    echo json_encode(['message' => 'Missing Google credential.']);
    exit();
}

$id_token = $data->credential;

try {
    // 1. Verify Google ID Token
    // Use cURL to call Google's tokeninfo endpoint
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/oauth2/v3/tokeninfo?id_token=" . $id_token);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $google_response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        http_response_code(401);
        echo json_encode(['message' => 'Google token verification failed.', 'details' => json_decode($google_response)]);
        exit();
    }

    $google_payload = json_decode($google_response, true);

    // Check if the audience matches our client ID
    if ($google_payload['aud'] !== $google_client_id) {
        http_response_code(401);
        echo json_encode(['message' => 'Google token audience mismatch.']);
        exit();
    }

    $google_email = $google_payload['email'];
    $google_name = $google_payload['name'];
    $google_sub = $google_payload['sub']; // Google's unique user ID

    // 2. Find or Create User in our database
    $stmt = $db->prepare("SELECT Id, Username, Email, PasswordHash, Role, GoogleId FROM Users WHERE GoogleId = ?");
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $db->error);
    }
    $stmt->bind_param("s", $google_sub);
    $stmt->execute();
    $stmt->store_result();

    $user = null;
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $username, $email_from_db, $passwordHash, $role, $googleId);
        $stmt->fetch();
        $user = [
            'Id' => $id, 'Username' => $username, 'Email' => $email_from_db, 
            'PasswordHash' => $passwordHash, 'Role' => $role, 'GoogleId' => $googleId
        ];
    } else {
        // User not found by GoogleId, try by email
        $stmt->close();
        $stmt = $db->prepare("SELECT Id, Username, Email, PasswordHash, Role, GoogleId FROM Users WHERE Email = ?");
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $db->error);
        }
        $stmt->bind_param("s", $google_email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $username, $email_from_db, $passwordHash, $role, $googleId);
            $stmt->fetch();
            $user = [
                'Id' => $id, 'Username' => $username, 'Email' => $email_from_db, 
                'PasswordHash' => $passwordHash, 'Role' => $role, 'GoogleId' => $googleId
            ];
            
            // Link existing email user to Google account
            if (empty($user['GoogleId'])) {
                $update_stmt = $db->prepare("UPDATE Users SET GoogleId = ? WHERE Id = ?");
                if ($update_stmt === false) {
                    throw new Exception("Prepare failed: " . $db->error);
                }
                $update_stmt->bind_param("si", $google_sub, $user['Id']);
                $update_stmt->execute();
                $update_stmt->close();
                $user['GoogleId'] = $google_sub; // Update in memory
            }
        } else {
            // Create a brand new user
            $insert_stmt = $db->prepare("INSERT INTO Users (Username, Email, GoogleId, Role) VALUES (?, ?, ?, 'User')");
            if ($insert_stmt === false) {
                throw new Exception("Prepare failed: " . $db->error);
            }
            $insert_stmt->bind_param("sss", $google_name, $google_email, $google_sub);
            $insert_stmt->execute();
            $new_user_id = $db->insert_id;
            $insert_stmt->close();

            $user = [
                'Id' => $new_user_id,
                'Username' => $google_name,
                'Email' => $google_email,
                'Role' => 'User',
                'GoogleId' => $google_sub
            ];

            // Also create a patient record for the new user
            $patient_stmt = $db->prepare("INSERT INTO Patients (UserId, Name, ContactInfo, DateOfBirth) VALUES (?, ?, ?, ?)");
            if ($patient_stmt === false) {
                throw new Exception("Prepare failed: " . $db->error);
            }
            $dob = date('Y-m-d H:i:s', strtotime('-30 years')); // Placeholder DOB
            $patient_stmt->bind_param("isss", $new_user_id, $google_name, $google_email, $dob);
            $patient_stmt->execute();
            $patient_stmt->close();
        }
    }
    $stmt->close();

    // 3. Generate and return our JWT
    if ($user) {
        $jwt_key = "a_super_secret_key_that_is_long_enough_to_be_secure_1234567890";
        $jwt_issuer = "https://localhost:7188"; // As per appsettings.json
        $jwt_audience = "https://localhost:7188"; // As per appsettings.json

        $token = generate_jwt($user, $jwt_key, $jwt_issuer, $jwt_audience);

        http_response_code(200);
        echo json_encode(['token' => $token]);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Failed to process user after Google login.']);
    }

    $db->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'A server error occurred during Google login.', 'error' => $e->getMessage()]);
}
?>
