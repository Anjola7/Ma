<?php
session_start();

// Kontrollo sesionin për të siguruar që një përdorues është i kyçur dhe ka rol menaxher
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'manager') {
    header("Location: login.php");
    exit;
}

// Përfshij skedarin e lidhjes me bazën e të dhënave
include 'db.php';

// Merr ID e menaxherit nga sesioni
$manager_id = $_SESSION['user_id'];

// Kontrollo nëse është dërguar ID e përdoruesit dhe të dhënat e tjera për modifikim
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    $username = $_POST['username'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    
    // Përgatit kërkesën për përditësimin e përdoruesit
    $sql = "UPDATE users SET username = ?";
    
    // Shto kushtin për password nëse është përditësuar
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql .= ", password = ?";
    }

    $sql .= " WHERE id = ? AND manager_id = ?";
    
    // Përgatit deklaratën
    if ($stmt = $conn->prepare($sql)) {
        // Shto parametra në përgatitjen e deklaratës
        if (!empty($new_password)) {
            $stmt->bind_param("ssii", $username, $hashed_password, $user_id, $manager_id);
        } else {
            $stmt->bind_param("sii", $username, $user_id, $manager_id);
        }

        // Ekzekuto kërkesën
        if ($stmt->execute()) {
            echo "User updated successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
} else {
    echo "Error: User ID not specified.";
}

// Mbyll lidhjen me bazën e të dhënave
$conn->close();
?>
