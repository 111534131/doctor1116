<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../config/database.php';
require_once '../jwt_helper.php';

function send_json_response($code, $data) {
    http_response_code($code);
    echo json_encode($data);
    exit();
}

$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'POST':
        handle_post_request();
        break;
    case 'DELETE':
        handle_delete_request();
        break;
    default:
        send_json_response(405, ['message' => 'Method Not Allowed']);
        break;
}

function handle_delete_request() {
    // Get Appointment ID from query parameter
    $appointment_id = $_GET['id'] ?? null;

    if (!is_numeric($appointment_id)) {
        send_json_response(400, ['message' => 'Invalid or missing Appointment ID specified.']);
    }

    // Authenticate the user
    $user_payload = null;
    try {
        $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        if (!$auth_header) send_json_response(401, ['message' => 'Access denied. No token provided.']);
        
        $token = preg_match('/Bearer\s(\S+)/', $auth_header, $matches) ? $matches[1] : null;
        if (!$token) send_json_response(401, ['message' => 'Access denied. Token format is invalid.']);
        
        $jwt_key = "a_super_secret_key_that_is_long_enough_to_be_secure_1234567890";
        $user_payload = validate_jwt($token, $jwt_key);
    } catch (Exception $e) {
        send_json_response(401, ['message' => 'Access denied. ' . $e->getMessage()]);
    }

    try {
        $db = (new Database())->getConnection();

        // Get appointment details for validation
        $stmt = $db->prepare("
            SELECT a.PatientId, a.DoctorId, a.AppointmentTime, d.CancellationPolicyHours
            FROM Appointments a
            JOIN Doctors d ON a.DoctorId = d.Id
            WHERE a.Id = ?
        ");
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();
        $appointment = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$appointment) {
            send_json_response(404, ['message' => 'Appointment not found.']);
        }

        // Authorization check
        $is_authorized = false;
        if ($user_payload->role === 'Admin') {
            $is_authorized = true;
        } elseif ($user_payload->role === 'User') {
            $patient_stmt = $db->prepare("SELECT Id FROM Patients WHERE UserId = ?");
            $patient_stmt->bind_param("i", $user_payload->id);
            $patient_stmt->execute();
            $patient = $patient_stmt->get_result()->fetch_assoc();
            $patient_stmt->close();
            if ($patient && $patient['Id'] == $appointment['PatientId']) {
                $is_authorized = true;
            }
        }
        // A doctor could also be authorized, but that logic is not required by the current frontend.

        if (!$is_authorized) {
            send_json_response(403, ['message' => 'Forbidden. You do not have permission to cancel this appointment.']);
        }

        // Cancellation policy check (Admins can bypass)
        if ($user_payload->role !== 'Admin') {
            $now = new DateTime();
            $appointment_time = new DateTime($appointment['AppointmentTime']);
            $hours_difference = ($appointment_time->getTimestamp() - $now->getTimestamp()) / 3600;

            if ($hours_difference < $appointment['CancellationPolicyHours']) {
                send_json_response(403, ['message' => 'Cancellation failed. The appointment is within the cancellation policy window.']);
            }
        }

        // If all checks pass, delete the appointment
        $delete_stmt = $db->prepare("DELETE FROM Appointments WHERE Id = ?");
        $delete_stmt->bind_param("i", $appointment_id);
        
        if ($delete_stmt->execute()) {
            if ($delete_stmt->affected_rows > 0) {
                send_json_response(200, ['message' => 'Appointment cancelled successfully.']);
            } else {
                send_json_response(404, ['message' => 'Appointment not found or already cancelled.']);
            }
        } else {
            throw new Exception("Failed to cancel appointment: " . $delete_stmt->error);
        }
        
        $delete_stmt->close();
        $db->close();

    } catch (Exception $e) {
        send_json_response(500, ['message' => 'An internal server error occurred during cancellation.', 'error' => $e->getMessage()]);
    }
}

function handle_post_request() {
    // Authenticate the user
    $user_payload = null;
    try {
        $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        if (!$auth_header) send_json_response(401, ['message' => 'Access denied. No token provided.']);
        
        $token = preg_match('/Bearer\s(\S+)/', $auth_header, $matches) ? $matches[1] : null;
        if (!$token) send_json_response(401, ['message' => 'Access denied. Token format is invalid.']);
        
        $jwt_key = "a_super_secret_key_that_is_long_enough_to_be_secure_1234567890";
        $user_payload = validate_jwt($token, $jwt_key);
    } catch (Exception $e) {
        send_json_response(401, ['message' => 'Access denied. ' . $e->getMessage()]);
    }

    // Get input data
    $data = json_decode(file_get_contents("php://input"));
    if (!$data || !isset($data->doctorId) || !isset($data->patientId) || !isset($data->appointmentTime)) {
        send_json_response(400, ['message' => 'Incomplete data. doctorId, patientId, and appointmentTime are required.']);
    }

    // Authorization: A user can book for themselves, or a doctor/admin can book for any patient.
    if ($user_payload->role === 'User') {
        try {
            $db_temp = (new Database())->getConnection();
            $stmt_patient = $db_temp->prepare("SELECT Id FROM Patients WHERE UserId = ?");
            $stmt_patient->bind_param("i", $user_payload->id);
            $stmt_patient->execute();
            $patient_result = $stmt_patient->get_result()->fetch_assoc();
            $stmt_patient->close();
            $db_temp->close();
            if (!$patient_result || $patient_result['Id'] != $data->patientId) {
                send_json_response(403, ['message' => 'Forbidden. You can only book appointments for yourself.']);
            }
        } catch (Exception $e) {
            send_json_response(500, ['message' => 'Authorization check failed.', 'error' => $e->getMessage()]);
        }
    }

    // --- CRITICAL VALIDATION: Check if the slot is actually available ---
    try {
        $db = (new Database())->getConnection();
        $appointment_time = $data->appointmentTime;
        $doctor_id = $data->doctorId;

        // 1. Check if the doctor is generally available at that time
        $avail_stmt = $db->prepare("SELECT COUNT(*) as count FROM DoctorAvailabilities WHERE DoctorId = ? AND ? BETWEEN StartTime AND EndTime");
        $avail_stmt->bind_param("is", $doctor_id, $appointment_time);
        $avail_stmt->execute();
        $avail_result = $avail_stmt->get_result()->fetch_assoc();
        $avail_stmt->close();

        if ($avail_result['count'] == 0) {
            send_json_response(409, ['message' => 'Booking failed. The doctor is not available at this time.']);
        }

        // 2. Check if the specific slot is already booked
        $appt_stmt = $db->prepare("SELECT COUNT(*) as count FROM Appointments WHERE DoctorId = ? AND AppointmentTime = ?");
        $appt_stmt->bind_param("is", $doctor_id, $appointment_time);
        $appt_stmt->execute();
        $appt_result = $appt_stmt->get_result()->fetch_assoc();
        $appt_stmt->close();

        if ($appt_result['count'] > 0) {
            send_json_response(409, ['message' => 'Booking failed. This time slot is no longer available.']);
        }

        // --- If validation passes, insert the appointment ---
        $insert_stmt = $db->prepare("INSERT INTO Appointments (PatientId, DoctorId, AppointmentTime) VALUES (?, ?, ?)");
        $insert_stmt->bind_param("iis", $data->patientId, $data->doctorId, $data->appointmentTime);
        
        if ($insert_stmt->execute()) {
            send_json_response(201, ['message' => 'Appointment created successfully.']);
        } else {
            throw new Exception("Failed to create appointment: " . $insert_stmt->error);
        }
        
        $insert_stmt->close();
        $db->close();

    } catch (Exception $e) {
        send_json_response(500, ['message' => 'An internal server error occurred during booking.', 'error' => $e->getMessage()]);
    }
}
?>
