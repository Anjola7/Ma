<?php
session_start();

include 'db.php';

$manager_id = $_SESSION['user_id'];
$limit = 5; // Numri maksimal i përdoruesve për t'u shfaqur në një faqe

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;

$stmt = $conn->prepare("SELECT username, role FROM users WHERE manager_id = ? LIMIT ?, ?");
$stmt->bind_param("iii", $manager_id, $start, $limit);
$stmt->execute();
$result = $stmt->get_result();

// Tabela për përdoruesit
echo "<table>";
echo "<tr><th>Username</th><th>Role</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
    echo "<td>" . htmlspecialchars($row['role']) . "</td>";
    echo "</tr>";
}
echo "</table>";

$stmt->close();

// Krijimi i linkjeve të faqeve të ndryshme
$total_pages = ceil($conn->query("SELECT COUNT(*) FROM users WHERE manager_id = $manager_id")->fetch_row()[0] / $limit);
echo "<div class='pagination'>";
for ($i = 1; $i <= $total_pages; $i++) {
    echo "<a href='manager_ajax_pagination.php?page=$i'>$i</a> ";
}
echo "</div>";
?>
