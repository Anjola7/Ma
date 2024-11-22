<?php
session_start();

// Kontrollo sesionin për të siguruar që një përdorues është i kyçur dhe ka rol manager
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Sigurohuni që 'user_id' është vendosur në sesion përpara përdorimit të tij
if (!isset($_SESSION['user_id'])) {
    echo "User ID is not set in session.";
    exit;
}

// Përfshij skedarin e lidhjes me bazën e të dhënave
include 'db.php';

// Merr user_id e menaxherit nga sesioni
$manager_id = $_SESSION['user_id'];

// Shto përdoruesin e ri nëse forma është dërguar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = $_POST['new_username'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    $insert_sql = "INSERT INTO users (username, password, role, manager_id) VALUES (?, ?, 'user', ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("ssi", $new_username, $new_password, $manager_id);

    if ($insert_stmt->execute()) {
        echo "New user added successfully.";
    } else {
        echo "Error: " . $insert_stmt->error;
    }

    $insert_stmt->close();
}

// Query për të marrë të dhënat e përdoruesve të lidhur me menaxherin aktual
$sql = "SELECT u.username, u.role FROM users u WHERE u.manager_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $manager_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manager Profile</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> (Manager)</h1>
    <h2>Users Linked to You</h2>
    <table border="1">
        <tr>
            <th>Username</th>
            <th>Role</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['username']); ?></td>
            <td><?php echo htmlspecialchars($row['role']); ?></td>
        </tr>
        <?php endwhile; ?>
        <?php if ($result->num_rows === 0): ?>
        <tr>
            <td colspan="2">No users linked to you.</td>
        </tr>
        <?php endif; ?>
    </table>

    <h2>Add New User</h2>
    <form method="post" action="">
        <label for="new_username">Username:</label>
        <input type="text" id="new_username" name="new_username" required><br>

        <label for="new_password">Password:</label>
        <input type="password" id="new_password" name="new_password" required><br>

        <input type="submit" value="Add User">
    </form>

<?php
// Mbyll lidhjen me bazën e të dhënave pasi ke përfunduar përpunimin e të dhënave
$stmt->close();
$conn->close();
?>
</body>
</html>
