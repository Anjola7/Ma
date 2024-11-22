<?php
session_start();
include 'db.php';

// Kontrollo nëse përdoruesi është i regjistruar dhe ka rol 'admin'
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Merr ID-në e përdoruesit nga parametrat GET
$user_id = $_GET['user_id'];

// Merr të dhënat e përdoruesit
$user_query = "SELECT id, username, role, manager_id FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Merr listën e menaxherëve
$managers_query = "SELECT id, username FROM users WHERE role = 'manager'";
$managers_result = $conn->query($managers_query);
$managers = $managers_result->fetch_all(MYSQLI_ASSOC);

// Krijo përgjigjen JSON
$response = [
    'id' => $user['id'],
    'username' => $user['username'],
    'role' => $user['role'],
    'manager_id' => $user['manager_id'],
    'managers' => $managers
];

// Dërgo përgjigjen JSON
echo json_encode($response);

// Mbyll lidhjet me bazën e të dhënave
$stmt->close();
$conn->close();
?>
