<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['id'];

// Fetch the current role of the user
$user_query = "SELECT role FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param('i', $userId);
$stmt->execute();
$stmt->bind_result($role);
$stmt->fetch();
$stmt->close();

if ($role === 'user') {
    $newRole = 'manager';
} elseif ($role === 'manager') {
    $newRole = 'admin';
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid role']);
    exit;
}

// Update the user's role in the database
$update_query = "UPDATE users SET role = ? WHERE id = ?";
$stmt = $conn->prepare($update_query);
$stmt->bind_param('si', $newRole, $userId);
$success = $stmt->execute();
$stmt->close();
$conn->close();

echo json_encode(['success' => $success]);
?>
