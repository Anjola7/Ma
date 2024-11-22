<?php
session_start();

// Kontrolli i sesionit dhe shfaqja e përmbajtjes së faqes sipas roli të përdoruesit
if (isset($_SESSION['role'])) {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "Anjola";

    // Krijimi i lidhjes
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Kontrollimi i lidhjes
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if ($_SESSION['role'] === 'admin') {
        // Query për të marrë të gjithë përdoruesit nga baza e të dhënave dhe shfaqja e tyre në tabelë
        $sql = "SELECT id, username FROM users"; // Sigurohu që emri i tabelës është "users"
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Fillimi i tabelës
            echo "<table border='1'><tr><th>ID</th><th>Username</th></tr>";

            // Shfaq të dhënat
            while ($row = $result->fetch_assoc()) {
                echo "<tr><td>" . $row["id"] . "</td><td>" . $row["username"] . "</td></tr>";
            }
            echo "</table>";
        } else {
            echo "0 results";
        }
    } elseif ($_SESSION['role'] === 'manager') {
        // Query për të marrë përdoruesit që janë të lidhur me menaxherin nga baza e të dhënave dhe shfaqja e tyre në tabelë
        $manager_id = $_SESSION['user_id']; // Assumed that the manager's ID is stored in the session
        $sql = "SELECT id, username FROM users WHERE manager_id = $manager_id"; // Sigurohu që ka një fushë "manager_id" në tabelën "users"
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Fillimi i tabelës
            echo "<table border='1'><tr><th>ID</th><th>Username</th></tr>";

            // Shfaq të dhënat
            while ($row = $result->fetch_assoc()) {
                echo "<tr><td>" . $row["id"] . "</td><td>" . $row["username"] . "</td></tr>";
            }
            echo "</table>";
        } else {
            echo "0 results";
        }
    } else {
        // Query për të marrë të dhënat e përdoruesit të loguar nga baza e të dhënave dhe shfaqja e tyre
        $user_id = $_SESSION['user_id']; // Assumed that the user's ID is stored in the session
        $sql = "SELECT id, username FROM users WHERE id = $user_id";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Fillimi i tabelës
            echo "<table border='1'><tr><th>ID</th><th>Username</th></tr>";

            // Shfaq të dhënat
            while ($row = $result->fetch_assoc()) {
                echo "<tr><td>" . $row["id"] . "</td><td>" . $row["username"] . "</td></tr>";
            }
            echo "</table>";
        } else {
            echo "0 results";
        }
    }

    // Mbyllja e lidhjes
    $conn->close();
} else {
    // Nëse sesioni nuk është vendosur, përcjellja tek faqja e login-it
    header("Location: login.php");
    exit();
}
?>
