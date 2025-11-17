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

// Authenticate the user
$user_id = null;
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
    
    $user_id = $decoded_payload->id;

} catch (Exception $e) {
    send_json_response(401, ['message' => 'Access denied. ' . $e->getMessage()]);
}

// Fetch appointments from the database
try {
    $database = new Database();
    $db = $database->getConnection();

    // Step 1: Get the Patient ID from the User ID
    $patient_id = null;
    $stmt_patient = $db->prepare("SELECT Id FROM Patients WHERE UserId = ?");
    if ($stmt_patient === false) {
        throw new Exception("Database prepare failed (patient): " . $db->error);
    }
    $stmt_patient->bind_param("i", $user_id);
    $stmt_patient->execute();
    $result_patient = $stmt_patient->get_result();
    
    if ($result_patient->num_rows > 0) {
        $patient_row = $result_patient->fetch_assoc();
        $patient_id = $patient_row['Id'];
    } else {
        // If user has no patient profile, they have no appointments. Return empty array.
        send_json_response(200, []);
    }
    $stmt_patient->close();

    // Step 2: Get appointments for the Patient ID
    $query = "
        SELECT
            a.Id,
            a.AppointmentTime,
            d.Name AS doctorName,
            d.CancellationPolicyHours
        FROM
            Appointments a
        JOIN
            Doctors d ON a.DoctorId = d.Id
        WHERE
            a.PatientId = ?
        ORDER BY
            a.AppointmentTime DESC
    ";
    
    $stmt = $db->prepare($query);
    if ($stmt === false) {
        throw new Exception("Database prepare failed (appointments): " . $db->error);
    }

    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $appointments_arr = [];
    while ($row = $result->fetch_assoc()) {
        $appointments_arr[] = [
            'id' => $row['Id'],
            'appointmentTime' => $row['AppointmentTime'],
            'doctorName' => $row['doctorName'],
            'cancellationPolicyHours' => $row['CancellationPolicyHours']
        ];
    }

    send_json_response(200, $appointments_arr);

    $stmt->close();
    $db->close();

} catch (Exception $e) {
    send_json_response(500, ['message' => 'An internal server error occurred.', 'error' => $e->getMessage()]);
}
?>
