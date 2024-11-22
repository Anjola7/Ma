<?php
session_start();


// Kontrollo sesionin për të siguruar që një përdorues është i kyçur
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Kontrolli i rolit të përdoruesit për të parandaluar përdoruesit e zakonshëm të qasen në këtë faqe
if ($_SESSION['role'] === 'user') {
    echo "You are not authorized to access this page.";
    exit;
}

// Përfshij skedarin e lidhjes me bazën e të dhënave
include 'db.php';

// Nëse forma është paraqitur me POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Për menaxherët dhe adminët, kontrollo për menaxherin e zgjedhur
    $manager_id = null;
    if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'manager') {
        $manager_id = $_POST['manager_id'];
    }
    
    // Përgatitni fjalëkalimin për ruajtje në bazën e të dhënave
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Shto përdoruesin në bazën e të dhënave
    $sql = "INSERT INTO users (username, password, role, manager_id) VALUES ('$username', '$hashed_password', 'user', '$manager_id')";
    if ($conn->query($sql) === TRUE) {
        echo "User created successfully.";
    } else {
        echo "Error creating user: " . $conn->error;
    }
}

// Query për të marrë menaxherët nëse ky përdorues është admin ose menaxher
if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'manager') {
    $managers = array();
    $manager_query = "SELECT id, username FROM users WHERE role='manager'";
    $manager_result = $conn->query($manager_query);
    if ($manager_result->num_rows > 0) {
        while($manager_row = $manager_result->fetch_assoc()) {
            $managers[] = $manager_row;
        }
    }
}

// Mbyll lidhjen me bazën e të dhënave
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create User</title>
</head>
<body>
    <h2>Create User</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="username">Username:</label><br>
        <input type="text" id="username" name="username"><br>
        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password"><br>
        
        <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'manager') : ?>
        <!-- Dropdown për zgjedhjen e menaxherit -->
        <label for="manager_id">Select Manager:</label><br>
        <select id="manager_id" name="manager_id">
            <?php foreach ($managers as $manager) : ?>
            <option value="<?php echo $manager['id']; ?>"><?php echo $manager['username']; ?></option>
            <?php endforeach; ?>
        </select><br>
        <?php endif; ?>
        
        <input type="submit" name="create_user" value="Create User">
    </form>
</body>
</html>
