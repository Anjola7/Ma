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

// Query për të marrë vetëm username dhe password nga tabela
$sql = "SELECT username, password FROM emri_i_tabeles";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Krijo tabelën HTML dhe rreshtin e kokës
    echo "<table border='1'>
    <tr>
        <th>Username</th>
        <th>Password</th>
    </tr>";

    // Shfaq të dhënat
    while($row = $result->fetch_assoc()) {
        echo "<tr>
            <td>" . $row["username"] . "</td>
            <td>" . $row["password"] . "</td>
        </tr>";
    }
    echo "</table>";
} else {
    echo "0 rezultate";
}

// Mbyllja e lidhjes
$conn->close();
?>
 