<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$request_method = $_SERVER["REQUEST_METHOD"];

switch($request_method) {
    case 'GET':
        get_doctors($db);
        break;
    default:
        header("HTTP/1.0 405 Method Not Allowed");
        echo json_encode(array("message" => "Method not allowed."));
        break;
}

function get_doctors($db) {
    $query = "SELECT Id, Name, Specialty, ContactInfo, UserId, CancellationPolicyHours FROM doctors";
    
    $stmt = $db->prepare($query);
    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(array("message" => "Unable to prepare statement."));
        $db->close();
        return;
    }

    $stmt->execute();
    $result = $stmt->get_result();
    
    $doctors_arr = array();
    while ($row = $result->fetch_assoc()) {
        // The frontend JS expects camelCase keys, so we create them here.
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
    $db->close();
}
?>
