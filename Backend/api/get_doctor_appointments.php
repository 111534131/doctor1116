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

// --- Get Date Parameter ---
$date = $_GET['date'] ?? null;
if (!$date) {
    send_json_response(400, ['message' => 'Date parameter is required.']);
}

// --- Fetch Data ---
try {
    $db = (new Database())->getConnection();

    // Get Doctor ID from User ID
    $doctor_id = null;
    $stmt_doc = $db->prepare("SELECT Id FROM Doctors WHERE UserId = ?");
    $stmt_doc->bind_param("i", $user_payload->id);
    $stmt_doc->execute();
    $doctor = $stmt_doc->get_result()->fetch_assoc();
    $stmt_doc->close();
    if ($doctor) {
        $doctor_id = $doctor['Id'];
    } else {
        send_json_response(404, ['message' => 'Doctor profile not found for the current user.']);
    }

    // Get appointments for the doctor on the specified date
    $query = "
        SELECT
            a.Id,
            a.AppointmentTime,
            p.Name AS patientName
        FROM
            Appointments a
        JOIN
            Patients p ON a.PatientId = p.Id
        WHERE
            a.DoctorId = ? AND DATE(a.AppointmentTime) = ?
        ORDER BY
            a.AppointmentTime ASC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->bind_param("is", $doctor_id, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $appointments_arr = [];
    while ($row = $result->fetch_assoc()) {
        $appointments_arr[] = $row;
    }

    $stmt->close();
    $db->close();
    send_json_response(200, $appointments_arr);

} catch (Exception $e) {
    send_json_response(500, ['message' => 'An internal server error occurred.', 'error' => $e->getMessage()]);
}
?>
