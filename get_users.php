<?php
session_start();

// Kontrollo nëse përdoruesi është i kyçur dhe ka rol admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Përfshij skedarin e lidhjes me bazën e të dhënave
include 'db.php';

// Merr faqen dhe numrin e elementeve për faqe
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = isset($_GET['itemsPerPage']) ? intval($_GET['itemsPerPage']) : 10;
$offset = ($page - 1) * $itemsPerPage;

// Query për të marrë të dhënat e përdoruesve
$users_query = $conn->prepare("SELECT u.id, u.username, u.role, m.username AS manager_name
                               FROM users u
                               LEFT JOIN users m ON u.manager_id = m.id
                               WHERE u.role <> 'admin'
                               LIMIT ?, ?");
$users_query->bind_param("ii", $offset, $itemsPerPage);
$users_query->execute();
$users_result = $users_query->get_result();

// Merr gjithashtu numrin e përgjithshëm të përdoruesve
$total_items_query = "SELECT COUNT(*) AS total FROM users WHERE role <> 'admin'";
$total_items_result = $conn->query($total_items_query);
$total_items = $total_items_result->fetch_assoc()['total'];

// Kontrollo nëse ka rezultate nga kërkesa
if ($users_result) {
    $users = $users_result->fetch_all(MYSQLI_ASSOC);
    echo json_encode([
        'users' => $users,
        'totalItems' => $total_items
    ]);
} else {
    echo json_encode(['users' => [], 'totalItems' => 0]);
}

// Mbyll lidhjen me bazën e të dhënave
$users_query->close();
$conn->close();
?>
