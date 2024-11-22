<?php
session_start();

// Kontrollo sesionin për të siguruar që një përdorues është i kyçur dhe ka rolin admin ose manager
if (!isset($_SESSION['username']) || !isset($_POST['user_id'])) {
    header("Location: login.php");
    exit;
}

// Përfshij skedarin e lidhjes me bazën e të dhënave
include 'db.php';

// Merr user_id nga kërkesa POST
$user_id = $_POST['user_id'];

// Kontrollo nëse user_id është valid dhe është e numrit
if (!filter_var($user_id, FILTER_VALIDATE_INT)) {
    echo "Invalid user ID.";
    exit;
}

// Merr rolin e përdoruesit të kyçur nga sesioni
$current_user_role = $_SESSION['role'];

// Kontrollo nëse përdoruesi është admin ose manager dhe ka të drejtë të fshijë përdoruesin
if ($current_user_role !== 'admin' && $current_user_role !== 'manager') {
    echo "Unauthorized access.";
    exit;
}

// Kontrollo nëse përdoruesi është një menaxher dhe mund të fshijë vetëm përdoruesit që janë të lidhur me të
if ($current_user_role === 'manager') {
    // Merr ID e menaxherit të përdoruesit që dëshiron të fshihet
    $sql = "SELECT manager_id FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($manager_id);
    $stmt->fetch();
    $stmt->close();

    // Kontrollo nëse menaxheri aktual është i lidhur me përdoruesin që po përpiqet të fshijë
    if ($manager_id !== $_SESSION['user_id']) {
        echo "Unauthorized access.";
        exit;
    }
}

// Fshi përdoruesin nga databaza
$sql = "DELETE FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    header("Location: manager_profile.php");
    exit;
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
