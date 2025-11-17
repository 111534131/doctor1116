<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../../config/database.php';

function send_json_response($code, $data) {
    http_response_code($code);
    echo json_encode($data);
    exit();
}

// Get date from query string
$date = $_GET['date'] ?? null;
if (!$date) {
    send_json_response(400, ['message' => 'Date parameter is required.']);
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // --- Step 1: Fetch all availabilities for the given date ---
    $avail_query = "
        SELECT
            da.DoctorId,
            d.Name,
            d.Specialty,
            da.StartTime,
            da.EndTime
        FROM
            DoctorAvailabilities da
        JOIN
            Doctors d ON da.DoctorId = d.Id
        WHERE
            DATE(da.StartTime) = ?
    ";
    $stmt_avail = $db->prepare($avail_query);
    if ($stmt_avail === false) throw new Exception("Prepare failed (availabilities): " . $db->error);
    $stmt_avail->bind_param("s", $date);
    $stmt_avail->execute();
    $avail_result = $stmt_avail->get_result();

    $availabilities = [];
    while ($row = $avail_result->fetch_assoc()) {
        $availabilities[] = $row;
    }
    $stmt_avail->close();

    // --- Step 2: Fetch all appointments for the given date ---
    $appt_query = "SELECT DoctorId, AppointmentTime FROM Appointments WHERE DATE(AppointmentTime) = ?";
    $stmt_appt = $db->prepare($appt_query);
    if ($stmt_appt === false) throw new Exception("Prepare failed (appointments): " . $db->error);
    $stmt_appt->bind_param("s", $date);
    $stmt_appt->execute();
    $appt_result = $stmt_appt->get_result();

    $booked_slots = [];
    while ($row = $appt_result->fetch_assoc()) {
        $doctor_id = $row['DoctorId'];
        $time = date("H:i", strtotime($row['AppointmentTime']));
        if (!isset($booked_slots[$doctor_id])) {
            $booked_slots[$doctor_id] = [];
        }
        $booked_slots[$doctor_id][] = $time;
    }
    $stmt_appt->close();
    $db->close();

    // --- Step 3: Process the data to find available slots ---
    $doctors_data = [];
    foreach ($availabilities as $avail) {
        $doctor_id = $avail['DoctorId'];

        // If doctor is not yet in our results array, add them
        if (!isset($doctors_data[$doctor_id])) {
            $doctors_data[$doctor_id] = [
                'id' => $doctor_id,
                'name' => $avail['Name'],
                'specialty' => $avail['Specialty'],
                'availableSlots' => []
            ];
        }

        // Generate all possible 30-minute slots within the availability window
        $start_time = new DateTime($avail['StartTime']);
        $end_time = new DateTime($avail['EndTime']);
        $interval = new DateInterval('PT30M'); // 30 minutes interval

        $period = new DatePeriod($start_time, $interval, $end_time);

        foreach ($period as $slot_time) {
            $slot_str = $slot_time->format('H:i');
            
            // Check if the slot is not booked
            $is_booked = in_array($slot_str, $booked_slots[$doctor_id] ?? []);
            
            if (!$is_booked) {
                $doctors_data[$doctor_id]['availableSlots'][] = $slot_str;
            }
        }
    }

    // Remove doctors who have no available slots left
    $final_doctors_list = array_filter(array_values($doctors_data), function($doctor) {
        return count($doctor['availableSlots']) > 0;
    });
    
    // Sort slots for each doctor
    foreach ($final_doctors_list as &$doctor) {
        sort($doctor['availableSlots']);
    }

    send_json_response(200, $final_doctors_list);

} catch (Exception $e) {
    send_json_response(500, ['message' => 'An internal server error occurred.', 'error' => $e->getMessage()]);
}
?>
