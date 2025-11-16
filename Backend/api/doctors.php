<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// --- Step 1: Test Database Connection Only ---

$host = "localhost";
$db_name = "ukn111534131";
$username = "ukn111534131";
$password = "ukn111534131";

try {
    $conn = new mysqli($host, $username, $password, $db_name);
    if ($conn->connect_error) {
        throw new Exception($conn->connect_error);
    }
    $conn->set_charset("utf8");
    
    http_response_code(200);
    echo json_encode(array("status" => "success", "message" => "Database connection successful!"));

    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("status" => "error", "message" => "Database connection failed.", "error_details" => $e->getMessage()));
}
?>