<?php
session_start();

// Kontrollo sesionin për të siguruar që një përdorues është i kyçur dhe ka rol menaxher
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'manager') {
    header("Location: login.php");
    exit;
}

include 'db.php';

// Merr ID e përdoruesit nga kërkesa POST
$user_id = $_POST['user_id'];
$username = $_POST['username'];
$password = isset($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
$role = $_POST['role'];

// Përditëso përdoruesin në bazën e të dhënave
$sql = "UPDATE users SET username = ?, role = ?" . ($password ? ", password = ?" : "") . " WHERE id = ?";
$stmt = $conn->prepare($sql);
if ($password) {
    $stmt->bind_param("sssi", $username, $role, $password, $user_id);
} else {
    $stmt->bind_param("ssi", $username, $role, $user_id);
}

if ($stmt->execute()) {
    echo "Përdoruesi u përditësua me sukses.";
} else {
    echo "Gabim: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
