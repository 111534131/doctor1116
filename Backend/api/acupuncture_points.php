<?php
// Set response headers for CORS and JSON content type
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Include database and model files
include_once '../config/database.php';
include_once '../Models/AcupuncturePoint.php';

// Instantiate database to get a connection
$database = new Database();
$db = $database->getConnection();

// SQL query to get all acupuncture points
$query = "SELECT Id, Name, BodyPart, `Function`, Harm, CoordX, CoordY FROM acupuncturepoints ORDER BY Name";

// Prepare and execute the query
$stmt = $db->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$num = $result->num_rows;

// Check if any records were found
if ($num > 0) {
    $points_arr = array();

    // Fetch table rows
    while ($row = $result->fetch_assoc()) {
        // Note: 'Function' is a reserved keyword in PHP, but it's fine as an array key.
        // The column name `Function` is also quoted in the SQL query to avoid issues.
        $point_item = array(
            "Id" => $row['Id'],
            "Name" => $row['Name'],
            "BodyPart" => $row['BodyPart'],
            "Function" => $row['Function'],
            "Harm" => $row['Harm'],
            "CoordX" => $row['CoordX'],
            "CoordY" => $row['CoordY']
        );
        array_push($points_arr, $point_item);
    }

    // Set response code - 200 OK
    http_response_code(200);

    // Output the data in JSON format
    echo json_encode($points_arr);
} else {
    // Set response code - 404 Not Found
    http_response_code(404);

    // Inform the user that no points were found
    echo json_encode(
        array("message" => "No acupuncture points found.")
    );
}

// Close the connection
$stmt->close();
$db->close();
?>
