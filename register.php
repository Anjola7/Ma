<?php
include 'db.php'; // Sigurohuni që ky skedar përmban kodin për lidhjen me bazën e të dhënave

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $manager_id = $_POST['manager']; // Menaxheri i zgjedhur nga forma

    // Validimi i inputeve
    if (empty($username) || empty($password) || empty($manager_id)) {
        echo "Username, password, and manager selection are required.";
        exit();
    }

    // Enkriptimi i fjalëkalimit
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Debugging output
    // echo "Username: $username, Manager ID: $manager_id <br>";

    // Sigurimi i query-it për të shmangur SQL injection
    $insert_query = "INSERT INTO users (username, password, manager_id, role) VALUES (?, ?, ?, 'user')";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ssi", $username, $hashed_password, $manager_id);

    if ($stmt->execute()) {
        // Merr ID-në e përdoruesit të sapo regjistruar
        $user_id = $stmt->insert_id;

        // Vendos sesionin për përdoruesin e sapo regjistruar
        session_start();
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'user';
        $_SESSION['user_id'] = $user_id;

        // Ridrejto te faqja e profilit të përdoruesit
        header("Location: user_profile.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>

