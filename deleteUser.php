<?php
session_start();

// Kontrollo nëse përdoruesi është i kyçur dhe ka rol menaxher
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'manager') {
    header("Location: login.php");
    exit;
}

// Përfshi skedarin e lidhjes me bazën e të dhënave
include 'db.php';

// Kontrollo nëse është dërguar ID e përdoruesit për fshirje
if (isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);

    // Krijo një kërkesë SQL për të fshirë përdoruesin
    $sql = "DELETE FROM users WHERE id = ?";
    
    // Përdor një përgatitje të deklaratës për të shmangur SQL Injection
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        
        // Ekzekuto kërkesën
        if ($stmt->execute()) {
            // Redirect në faqen e menaxhimit pas fshirjes me sukses
            header("Location: admin_dashboard.php");
            exit;
        } else {
            echo "Gabim gjatë fshirjes së përdoruesit: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Gabim në përgatitjen e kërkesës: " . $conn->error;
    }
} else {
    echo "ID e përdoruesit nuk është e specifikuar.";
}

// Mbyll lidhjen me bazën e të dhënave
$conn->close();
?>
