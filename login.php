<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query për të marrë përdoruesin me username-in e dhënë
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Verifikimi i suksesit të hyrjes
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['user_id'] = $row['id']; // Sigurohuni që 'id' është emri i kolonës së ID-së së përdoruesit

            // Kontrolli i rolit dhe ridrejtimi në faqen përkatëse
            if ($_SESSION['role'] == 'admin') {
                // Nëse është admin, ridrejto tek faqja e adminit
                header("Location: admin_dashboard.php");
            } elseif ($_SESSION['role'] == 'manager') {
                // Nëse është menaxher, ridrejto tek faqja e menaxherit
                header("Location: manager_dashboard.php");
            } else {
                // Nëse është user, ridrejto tek faqja e profilit të përdoruesit
                header("Location: user_profile.php");
            }
            exit(); // Mos harroni të dilni nga skripti pasi të keni përcjellë në faqen e re
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found.";
    }

    $stmt->close();
    $conn->close();
}
?>
