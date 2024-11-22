<?php
session_start();

// Kontrollo sesionin për të siguruar që një përdorues është i kyçur dhe ka rol admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Përfshij skedarin e lidhjes me bazën e të dhënave
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Shtimi i përdoruesve të rinj
    if (isset($_POST['new_username']) && isset($_POST['new_password']) && isset($_POST['manager_id'])) {
        $username = $_POST['new_username'];
        $password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
        $manager_id = $_POST['manager_id'];

        $stmt = $conn->prepare("INSERT INTO users (username, password, role, manager_id) VALUES (?, ?, 'user', ?)");
        $stmt->bind_param("ssi", $username, $password, $manager_id);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "User added successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Invalid input"]);
    }
} else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $items_per_page = 10; // Numri i përdoruesve për faqe

    // Kontrollo për faqen aktuale nga kërkesa GET
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $offset = ($page - 1) * $items_per_page;

    // Query për të marrë të dhënat e përdoruesve me pagination
    $sql = "SELECT users.id, users.username, users.role, managers.username AS manager_name
            FROM users 
            LEFT JOIN users AS managers ON users.manager_id = managers.id 
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $items_per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $userList = array();
    while ($row = $result->fetch_assoc()) {
        $userList[] = $row;
    }

    // Merr numrin total të përdoruesve
    $count_sql = "SELECT COUNT(*) AS total FROM users";
    $count_result = $conn->query($count_sql);
    $total_users = $count_result->fetch_assoc()['total'];

    $response = array(
        'users' => $userList,
        'total' => $total_users,
        'page' => $page,
        'items_per_page' => $items_per_page
    );

    // Kthe një listë JSON për përdorimin në JavaScript
    echo json_encode($response);

    $stmt->close();
    $conn->close();
}
?>
