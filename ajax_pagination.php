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
    $new_username = isset($_POST['new_username']) ? $_POST['new_username'] : null;
    $new_password = isset($_POST['new_password']) ? password_hash($_POST['new_password'], PASSWORD_DEFAULT) : null;

    if ($new_username && $new_password) {
        $insert_sql = "INSERT INTO users (username, password, role, manager_id) VALUES (?, ?, 'user', ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ssi", $new_username, $new_password, $manager_id);

        if ($insert_stmt->execute()) {
            echo "New user added successfully.";
        } else {
            echo "Error: " . $insert_stmt->error;
        }

        $insert_stmt->close();
    } else {
        echo "Error: Username or password is empty.";
    }
}

// Pagination
$limit = 5; // Numri i artikujve për faqe
$page = isset($_GET['page']) ? $_GET['page'] : 1; // Numri i faqes së tanishme

$start = ($page - 1) * $limit; // Offset

// Query për të marrë të dhënat e përdoruesve të lidhur me menaxherin aktual duke përdorur LIMIT dhe OFFSET
$sql = "SELECT u.username, u.role FROM users u WHERE u.manager_id = ? LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $manager_id, $start, $limit);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manager Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
        }
        header {
            background-color: #4CAF50;
            color: white;
            padding: 20px 0;
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            border-collapse: collapse;
            width: 80%;
            margin: 0 auto;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .form-container {
            width: 80%;
            margin: 0 auto;
            margin-bottom: 20px;
            background-color: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"], input[type="password"], input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .pagination {
            text-align: center;
            margin-bottom: 20px;
        }
        .pagination a {
            display: inline-block;
            padding: 8px 16px;
            text-decoration: none;
            color: #000;
            background-color: #f2f2f2;
            border: 1px solid #ddd;
            transition: background-color 0.3s;
        }
        .pagination a.active {
            background-color: #4CAF50;
            color: white;
            border: 1px solid #4CAF50;
        }
        .pagination a:hover:not(.active) {background-color: #ddd;}
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> (Manager)</h1>
    </header>

    <!-- Table Container -->
    <div class="table-container">
        <h2>Users Linked to You</h2>
        <table>
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
    </div>

    <!-- Pagination -->
    <div class="pagination">
        <?php
        // Gjenero butonat për pagination
        $sql = "SELECT COUNT(*) AS total FROM users WHERE manager_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $manager_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $total_pages = ceil($row["total"] / $limit);

        // Butonat për të kaluar nga një faqe në tjetrën
        for ($i = 1; $i <= $total_pages; $i++) {
            echo "<a href='manager_dashboard.php?page=".$i."' class='".($page == $i ? 'active' : '')."'>".$i."</a> ";
        }
        ?>
    </div>

    <!-- Form Container -->
    <div class="form-container">
        <h2>Add New User</h2>
        <form id="addUserForm" method="post" action="">
            <div class="form-group">
                <label for="new_username">Username:</label>
                <input type="text" id="new_username" name="new_username" required>
            </div>
            <div class="form-group">
                <label for="new_password">Password:</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <input type="submit" value="Add User">
            </div>
        </form>
    </div>

    <?php
    // Mbyll lidhjen me bazën e të dhënave pasi ke përfunduar përpunimin e të dhënave
    $stmt->close();
    $conn->close();
    ?>
</body>
</html>
