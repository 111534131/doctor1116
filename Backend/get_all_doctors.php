<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// --- Database Connection Logic ---
$host = "localhost";
$db_name = "ukn111534131";
$username = "ukn111534131";
$password = "ukn111534131";
$conn = null;

try {
    $conn = new mysqli($host, $username, $password, $db_name);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("message" => "Database connection error.", "error" => $e->getMessage()));
    exit();
}

// --- Get Doctors Logic ---
$query = "SELECT Id, Name, Specialty, ContactInfo, UserId, CancellationPolicyHours FROM doctors";
$stmt = $conn->prepare($query);

if ($stmt === false) {
    http_response_code(500);
    echo json_encode(array("message" => "Unable to prepare statement.", "error" => $conn->error));
    $conn->close();
    exit();
}

$stmt->execute();
$result = $stmt->get_result();

$doctors_arr = array();
while ($row = $result->fetch_assoc()) {
    $doctor_item = array(
        "id" => $row['Id'],
        "name" => $row['Name'],
        "specialty" => $row['Specialty'],
        "contactInfo" => $row['ContactInfo'],
        "userId" => $row['UserId'],
        "cancellationPolicyHours" => $row['CancellationPolicyHours']
    );
    array_push($doctors_arr, $doctor_item);
}

http_response_code(200);
echo json_encode($doctors_arr);

$stmt->close();
$conn->close();
?>
