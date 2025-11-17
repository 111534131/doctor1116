<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../jwt_helper.php';

function send_json_response($code, $data) {
    http_response_code($code);
    echo json_encode($data);
    exit();
}

$request_method = $_SERVER["REQUEST_METHOD"];

// --- Get ID for PUT requests from $_GET ---
$record_id = $_GET['id'] ?? null;
if ($request_method === 'PUT' && !is_numeric($record_id)) {
    send_json_response(400, ['message' => 'Record ID is required for PUT request.']);
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
    if (!in_array($user_payload->role, ['Admin', 'Doctor'])) {
        send_json_response(403, ['message' => 'Forbidden. You do not have permission to access this resource.']);
    }
} catch (Exception $e) {
    send_json_response(401, ['message' => 'Access denied. ' . $e->getMessage()]);
}


switch ($request_method) {
    case 'GET':
        handle_get_request($user_payload);
        break;
    case 'POST':
        handle_post_request($user_payload);
        break;
    case 'PUT':
        handle_put_request($user_payload, (int)$record_id);
        break;
    default:
        send_json_response(405, ['message' => 'Method Not Allowed']);
        break;
}

function handle_get_request($user_payload) {
    try {
        $db = (new Database())->getConnection();
        // For doctors, only fetch records they have created. Admins can see all.
        $query = "
            SELECT mr.Id, mr.PatientId, mr.DoctorId, mr.RecordDate, mr.Diagnosis, mr.Treatment, mr.Notes,
                   p.Name as PatientName, p.DateOfBirth as PatientDateOfBirth
            FROM MedicalRecords mr
            JOIN Patients p ON mr.PatientId = p.Id
        ";
        if ($user_payload->role === 'Doctor') {
            $doctor_id_stmt = $db->prepare("SELECT Id FROM Doctors WHERE UserId = ?");
            $doctor_id_stmt->bind_param("i", $user_payload->id);
            $doctor_id_stmt->execute();
            $doctor = $doctor_id_stmt->get_result()->fetch_assoc();
            $doctor_id_stmt->close();
            if ($doctor) {
                $query .= " WHERE mr.DoctorId = ?";
                $stmt = $db->prepare($query);
                $stmt->bind_param("i", $doctor['Id']);
            } else { // Doctor has no doctor profile, so no records
                send_json_response(200, []);
            }
        } else { // Admin
            $stmt = $db->prepare($query);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $records = [];
        while ($row = $result->fetch_assoc()) {
            // Frontend expects a nested patient object
            $records[] = [
                'id' => $row['Id'],
                'patientId' => $row['PatientId'],
                'doctorId' => $row['DoctorId'],
                'recordDate' => $row['RecordDate'],
                'diagnosis' => $row['Diagnosis'],
                'treatment' => $row['Treatment'],
                'notes' => $row['Notes'],
                'patient' => [
                    'id' => $row['PatientId'],
                    'name' => $row['PatientName'],
                    'dateOfBirth' => $row['PatientDateOfBirth']
                ]
            ];
        }
        $stmt->close();
        $db->close();
        send_json_response(200, $records);
    } catch (Exception $e) {
        send_json_response(500, ['message' => 'An internal server error occurred.', 'error' => $e->getMessage()]);
    }
}

function handle_post_request($user_payload) {
    $data = json_decode(file_get_contents("php://input"));
    if (!$data || !isset($data->patientId) || !isset($data->doctorId)) {
        send_json_response(400, ['message' => 'Incomplete data. patientId and doctorId are required.']);
    }

    try {
        $db = (new Database())->getConnection();
        // Authorization: Doctor can only create records for themselves.
        if ($user_payload->role === 'Doctor') {
             $doctor_id_stmt = $db->prepare("SELECT Id FROM Doctors WHERE UserId = ?");
             $doctor_id_stmt->bind_param("i", $user_payload->id);
             $doctor_id_stmt->execute();
             $doctor = $doctor_id_stmt->get_result()->fetch_assoc();
             $doctor_id_stmt->close();
             if (!$doctor || $doctor['Id'] != $data->doctorId) {
                 send_json_response(403, ['message' => 'Forbidden. You can only create records for yourself.']);
             }
        }

        $stmt = $db->prepare("INSERT INTO MedicalRecords (PatientId, DoctorId, RecordDate, Diagnosis, Treatment, Notes) VALUES (?, ?, NOW(), ?, ?, ?)");
        $stmt->bind_param("iisss", $data->patientId, $data->doctorId, $data->diagnosis, $data->treatment, $data->notes);
        
        if ($stmt->execute()) {
            send_json_response(201, ['message' => 'Medical record created successfully.']);
        } else {
            throw new Exception("Failed to create record: " . $stmt->error);
        }
        $stmt->close();
        $db->close();
    } catch (Exception $e) {
        send_json_response(500, ['message' => 'An internal server error occurred.', 'error' => $e->getMessage()]);
    }
}

function handle_put_request($user_payload, $record_id) {
    $data = json_decode(file_get_contents("php://input"));
     if (!$data || !isset($data->patientId) || !isset($data->doctorId)) {
        send_json_response(400, ['message' => 'Incomplete data.']);
    }

    try {
        $db = (new Database())->getConnection();
        // Authorization: Doctor can only update records they created.
        if ($user_payload->role === 'Doctor') {
             $record_stmt = $db->prepare("SELECT DoctorId FROM MedicalRecords WHERE Id = ?");
             $record_stmt->bind_param("i", $record_id);
             $record_stmt->execute();
             $record = $record_stmt->get_result()->fetch_assoc();
             $record_stmt->close();
             if (!$record) send_json_response(404, ['message' => 'Record not found.']);

             $doctor_id_stmt = $db->prepare("SELECT Id FROM Doctors WHERE UserId = ?");
             $doctor_id_stmt->bind_param("i", $user_payload->id);
             $doctor_id_stmt->execute();
             $doctor = $doctor_id_stmt->get_result()->fetch_assoc();
             $doctor_id_stmt->close();

             if (!$doctor || $doctor['Id'] != $record['DoctorId']) {
                 send_json_response(403, ['message' => 'Forbidden. You can only update your own records.']);
             }
        }

        $stmt = $db->prepare("UPDATE MedicalRecords SET Diagnosis = ?, Treatment = ?, Notes = ? WHERE Id = ?");
        $stmt->bind_param("sssi", $data->diagnosis, $data->treatment, $data->notes, $record_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                send_json_response(200, ['message' => 'Medical record updated successfully.']);
            } else {
                send_json_response(200, ['message' => 'No changes were made to the record.']);
            }
        } else {
            throw new Exception("Failed to update record: " . $stmt->error);
        }
        $stmt->close();
        $db->close();
    } catch (Exception $e) {
        send_json_response(500, ['message' => 'An internal server error occurred.', 'error' => $e->getMessage()]);
    }
}
?>
