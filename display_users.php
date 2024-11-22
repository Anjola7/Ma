<?php
// Detajet e lidhjes me bazën e të dhënave
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Anjola";

// Krijimi i lidhjes
$conn = new mysqli($servername, $username, $password, $dbname);

// Kontrollimi i lidhjes
if ($conn->connect_error) {
    die("Lidhja dështoi: " . $conn->connect_error);
}

// Query për të marrë të dhënat
$sql = "SELECT id, username, password, role FROM users"; // Sigurohu që emri i tabelës është "users"
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Fillimi i tabelës
    echo "<table class='table table-striped'><thead><tr><th>ID</th><th>Username</th><th>Password</th><th>Role</th></tr></thead><tbody>";

    // Shfaq të dhënat
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row["id"] . "</td><td>" . $row["username"] . "</td><td>" . $row["password"] . "</td><td>" . $row["role"] . "</td></tr>";
    }
    echo "</tbody></table>";
} else {
    echo "0 results";
}

// Mbyllja e lidhjes
$conn->close();
?>

