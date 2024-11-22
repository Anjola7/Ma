<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    echo "Error: Unauthorized";
    exit;
}

$user_id = $_POST['user_id'];
$username = $_POST['username'];
$old_password = $_POST['old_password'];
$new_password = $_POST['new_password'];
$role = $_POST['role'];
$manager_id = $_POST['manager_id'];

// Kontrollo fjalëkalimin e vjetër
$user_query = "SELECT password FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || !password_verify($old_password, $user['password'])) {
    echo "Error: Incorrect old password";
    exit;
}

// Përditëso përdoruesin
$update_query = "UPDATE users SET username = ?, role = ?, manager_id = ? WHERE id = ?";
$params = [$username, $role, $manager_id, $user_id];
if (!empty($new_password)) {
    $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $update_query = "UPDATE users SET username = ?, password = ?, role = ?, manager_id = ? WHERE id = ?";
    $params = [$username, $new_password_hashed, $role, $manager_id, $user_id];
}

$stmt = $conn->prepare($update_query);
$stmt->bind_param(str_repeat("s", count($params) - 1) . "i", ...$params);
if ($stmt->execute()) {
    echo "User updated successfully";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
