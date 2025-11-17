<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, DELETE, GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../jwt_helper.php';

function send_json_response($code, $data) {
    http_response_code($code);
    echo json_encode($data);
    exit();
}

$request_method = $_SERVER["REQUEST_METHOD"];

// --- Authentication ---
$user_payload = null;
try {
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
    if (!$auth_header) send_json_response(401, ['message' => 'Access denied. No token provided.']);
    $token = preg_match('/Bearer\s(\S+)/', $auth_header, $matches) ? $matches[1] : null;
    if (!$token) send_json_response(401, ['message' => 'Access denied. Token format is invalid.']);
    $jwt_key = "a_super_secret_key_that_is_long_enough_to_be_secure_1234567890";
    $user_payload = validate_jwt($token, $jwt_key);
    if (!in_array($user_payload->role, ['Admin', 'Doctor'])) {
        send_json_response(403, ['message' => 'Forbidden. You do not have permission to perform this action.']);
    }
} catch (Exception $e) {
    send_json_response(401, ['message' => 'Access denied. ' . $e->getMessage()]);
}

// --- Routing based on method and GET parameters ---
switch ($request_method) {
    case 'POST':
        $doctor_id = $_GET['doctorId'] ?? null;
        if (!is_numeric($doctor_id)) {
            send_json_response(400, ['message' => 'Invalid or missing doctorId for POST request.']);
        }
        handle_post_request($user_payload, (int)$doctor_id);
        break;
    case 'DELETE':
        $availability_id = $_GET['id'] ?? null;
        if (!is_numeric($availability_id)) {
            send_json_response(400, ['message' => 'Invalid or missing availability ID for DELETE request.']);
        }
        handle_delete_request($user_payload, (int)$availability_id);
        break;
    case 'GET':
        $doctor_id = $_GET['doctorId'] ?? null; // For GET /doctors/{id}/availability
        if ($doctor_id) {
            if (!is_numeric($doctor_id)) {
                send_json_response(400, ['message' => 'Invalid doctorId for GET request.']);
            }
            handle_get_request($user_payload, (int)$doctor_id);
        } else {
            send_json_response(400, ['message' => 'Missing doctorId for GET request.']);
        }
        break;
    default:
        send_json_response(405, ['message' => 'Method Not Allowed']);
        break;
}


function handle_get_request($user_payload, $doctor_id) {
    $date = $_GET['date'] ?? null;
    try {
        $db = (new Database())->getConnection();
        $query = "SELECT Id, StartTime, EndTime FROM DoctorAvailabilities WHERE DoctorId = ?";
        $params = [$doctor_id];
        $types = "i";

        if ($date) {
            $query .= " AND DATE(StartTime) = ?";
            $params[] = $date;
            $types .= "s";
        }
        
        $stmt = $db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $availabilities = [];
        while($row = $result->fetch_assoc()) {
            $availabilities[] = $row;
        }
        $stmt->close();
        $db->close();
        send_json_response(200, $availabilities);
    } catch (Exception $e) {
        send_json_response(500, ['message' => 'An internal server error occurred.', 'error' => $e->getMessage()]);
    }
}


function handle_post_request($user_payload, $doctor_id) {
    $data = json_decode(file_get_contents("php://input"));
    if (!$data || !isset($data->startTime) || !isset($data->endTime)) {
        send_json_response(400, ['message' => 'Incomplete data. startTime and endTime are required.']);
    }

    try {
        $db = (new Database())->getConnection();
        // Authorization
        if ($user_payload->role === 'Doctor') {
            $doc_stmt = $db->prepare("SELECT Id FROM Doctors WHERE UserId = ?");
            $doc_stmt->bind_param("i", $user_payload->id);
            $doc_stmt->execute();
            $doctor = $doc_stmt->get_result()->fetch_assoc();
            $doc_stmt->close();
            if (!$doctor || $doctor['Id'] != $doctor_id) {
                send_json_response(403, ['message' => 'Forbidden. You can only add availability for yourself.']);
            }
        }

        $stmt = $db->prepare("INSERT INTO DoctorAvailabilities (DoctorId, StartTime, EndTime) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $doctor_id, $data->startTime, $data->endTime);
        
        if ($stmt->execute()) {
            send_json_response(201, ['message' => 'Availability added successfully.']);
        } else {
            throw new Exception("Failed to add availability: " . $stmt->error);
        }
        $stmt->close();
        $db->close();
    } catch (Exception $e) {
        send_json_response(500, ['message' => 'An internal server error occurred.', 'error' => $e->getMessage()]);
    }
}

function handle_delete_request($user_payload, $availability_id) {
     try {
        $db = (new Database())->getConnection();
        
        // Authorization
        if ($user_payload->role === 'Doctor') {
            $avail_stmt = $db->prepare("SELECT DoctorId FROM DoctorAvailabilities WHERE Id = ?");
            $avail_stmt->bind_param("i", $availability_id);
            $avail_stmt->execute();
            $availability = $avail_stmt->get_result()->fetch_assoc();
            $avail_stmt->close();
            if (!$availability) send_json_response(404, ['message' => 'Availability not found.']);

            $doc_stmt = $db->prepare("SELECT Id FROM Doctors WHERE UserId = ?");
            $doc_stmt->bind_param("i", $user_payload->id);
            $doc_stmt->execute();
            $doctor = $doc_stmt->get_result()->fetch_assoc();
            $doc_stmt->close();

            if (!$doctor || $doctor['Id'] != $availability['DoctorId']) {
                send_json_response(403, ['message' => 'Forbidden. You can only delete your own availability.']);
            }
        }

        $stmt = $db->prepare("DELETE FROM DoctorAvailabilities WHERE Id = ?");
        $stmt->bind_param("i", $availability_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                send_json_response(200, ['message' => 'Availability deleted successfully.']);
            } else {
                send_json_response(404, ['message' => 'Availability not found.']);
            }
        } else {
            throw new Exception("Failed to delete availability: " . $stmt->error);
        }
        $stmt->close();
        $db->close();
    } catch (Exception $e) {
        send_json_response(500, ['message' => 'An internal server error occurred.', 'error' => $e->getMessage()]);
    }
}
?>
