<?php
session_start();

// Kontrollo sesionin për të siguruar që një përdorues është i kyçur dhe ka rol menaxher
if (!isset($_SESSION['username'])) {
    http_response_code(403); // Forbids access
    exit;
}

// Sigurohuni që 'user_id' është vendosur në kërkesë
if (!isset($_POST['user_id'])) {
    http_response_code(400); // Bad Request
    exit;
}

// Përfshij skedarin e lidhjes me bazën e të dhënave
include 'db.php';

$user_id = $_POST['user_id'];

// Query për të fshirë përdoruesin nga baza e të dhënave
$sql = "DELETE FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    echo "User deleted successfully.";
} else {
    http_response_code(500); // Internal Server Error
}

$stmt->close();
$conn->close();
?>
