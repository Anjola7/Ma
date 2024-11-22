<?php
// handle_delete_user.php

// Ndërtoni lidhjen me databazën
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Anjola";

$conn = new mysqli($servername, $username, $password, $dbname);

// Kontrolloni nëse lidhja është e suksesshme
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];

    // Përdor një pyetje të sigurt për të fshirë përdoruesin
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo "User deleted successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
