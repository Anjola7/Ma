<?php
header('Content-Type: application/json');

// Krijo një lidhje me bazën e të dhënave
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Anjola";

$conn = new mysqli($servername, $username, $password, $dbname);

// Kontrollo lidhjen
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Kërkesa SQL për të marrë të dhënat
$sql = "SELECT company, price, change, change_percent FROM stocks";
$result = $conn->query($sql);

$data = array();
if ($result->num_rows > 0) {
    // Output data for each row
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else {
    echo json_encode(array("message" => "No data found"));
}

$conn->close();

// Kthe të dhënat në formatin JSON
echo json_encode($data);
?>
