<?php
include 'db.php';

// Merr të gjithë menaxherët (përdoruesit me rol 'manager')
$query = "SELECT id, username FROM users WHERE role = 'manager'";
$result = $conn->query($query);

$managers = array();

if ($result) {
    $managers = $result->fetch_all(MYSQLI_ASSOC);
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($managers);
?>
