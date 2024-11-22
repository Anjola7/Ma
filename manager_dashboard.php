<?php
session_start();

// Kontrollo sesionin për të siguruar që një përdorues është i kyçur dhe ka rol menaxher
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'manager') {
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_username']) && isset($_POST['new_password'])) {
    $new_username = $_POST['new_username'];
    $new_password = $_POST['new_password'];
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    if (!empty($new_username) && !empty($new_password)) {
        $insert_sql = "INSERT INTO users (username, password, role, manager_id) VALUES (?, ?, 'user', ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        
        if ($insert_stmt) {
            $insert_stmt->bind_param("ssi", $new_username, $hashed_password, $manager_id);

            if ($insert_stmt->execute()) {
                echo "New user added successfully.";
            } else {
                echo "Error: " . $insert_stmt->error;
            }

            $insert_stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    } else {
        echo "Error: Username or password is empty.";
    }
}

// Pagination
$limit = 5; // Numri i artikujve për faqe
$page = isset($_GET['page']) ? intval($_GET['page']) : 1; // Numri i faqes së tanishme

$start = ($page - 1) * $limit; // Offset

// Query për të marrë të dhënat e përdoruesve të lidhur me menaxherin aktual duke përdorur LIMIT dhe OFFSET
$sql = "SELECT u.id, u.username, u.role FROM users u WHERE u.manager_id = ? LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $manager_id, $start, $limit);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Profile</title>
    <style>
        /* Your existing CSS styles */
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to right, #d2bcb2, #e2ceb4); /* Gradiente të buta */
            color: #333;
            min-height: 100vh; /* Përdor min-height në vend të height për të lejuar rreshqitjen */
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow-x: hidden; /* Heq rreshqitjen horizontale */
        }

        /* Animacioni fadeIn për kontejnerin */
        .fade-in {
            opacity: 0;
            animation: fadeIn 2s ease-out forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        header {
            width: 100%;
            background: linear-gradient(90deg, #f0f0f0, #dcdcdc); /* Gradiente të buta */
            color: #333;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            margin: 0;
            font-size: 3em; /* Ndryshuar për të pasur një madhësi më të madhe */
            color: #333;
            animation: slideDown 1s ease-in-out;
        }

        @keyframes slideDown {
            0% { transform: translateY(-20px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }

        h2 {
            margin: 20px 0;
            color: #333;
            font-size: 24px;
            border-bottom: 2px solid #ccc; /* Ngjyra më e butë për linjat e ndarjes */
            padding-bottom: 10px;
        }

        table {
            border-collapse: collapse;
            width: 100%; /* Tabela do të mbushë tërë gjerësinë e faqes */
            margin: 20px 0; /* Heq margjinën e anësore për të bërë tabelën të mbushë tërë hapësirën */
            background-color: #f8f9fa; /* Ngjyra e ngjashme me sfondin e formës së login-it */
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            animation: fadeIn 2s ease-out forwards; /* Shto animacionin fadeIn për tabelën */
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 15px;
            text-align: left;
            font-size: 16px; /* Rrit madhësinë e fontit për lehtësi në lexim */
        }

        th {
            background-color: #e9ecef; /* Ngjyra e ngjashme me sfondin e formës së login-it */
            font-weight: bold;
        }

        td {
            background-color: #ffffff; /* Ngjyra e bardhë për qelizat e tabelës për kontrast të mirë */
        }

        form {
            display: inline;
        }

        .form-container {
            width: 80%;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            animation: fadeIn 2s ease-out forwards; /* Shto animacionin fadeIn për formën */
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        input[type="text"], input[type="password"], select {
            width: 100%;
            padding: 12px;
            margin: 5px 0;
            border-radius: 4px;
            border: 1px solid #ddd;
            font-size: 16px;
        }

        input[type="submit"], .btn {
            width: 100%; /* Bëni butonat të mbushin tërë hapësirën e disponueshme */
            padding: 12px;
            background-color: #6c757d; /* Ngjyra e butë e sfondit për butonat */
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 4px;
            transition: background-color 0.3s ease, transform 0.3s ease; /* Shto animacionin pulse për butonat */
            animation: pulse 2s infinite;
        }

        input[type="submit"]:hover, .btn:hover {
            background-color: #5a6268; /* Ngjyra e ndryshuar për hover */
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .radio-group {
            display: flex;
            flex-direction: column;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }

        .pagination button {
            padding: 10px 15px;
            margin: 0 5px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
            cursor: pointer;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .pagination button:hover {
            background-color: #e9ecef;
        }
        /* Modal styles */
.modal {
    display: none; /* Initially hidden */
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgb(0,0,0);
    background-color: rgba(0,0,0,0.4);
    padding-top: 60px;
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    border-radius: 8px;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

    </style>
</head>
<body>
    <header>
        <h1>Manager Profile</h1>
    </header>
<!-- Modal for modifying user -->
<div id="modifyModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Modify User</h2>
        <form id="modifyForm" method="POST" action="modify_user.php">
            <input type="hidden" name="user_id" id="modal_user_id">
            <div class="form-group">
                <label for="modal_username">Username:</label>
                <input type="text" id="modal_username" name="username" required>
            </div>
            <div class="form-group">
                <label for="modal_old_password">Old Password:</label>
                <input type="password" id="modal_old_password" name="old_password">
            </div>
            <div class="form-group">
                <label for="modal_new_password">New Password:</label>
                <input type="password" id="modal_new_password" name="new_password">
            </div>
          
            <input type="submit" value="Save Changes">
        </form>
    </div>
</div>

    <div class="form-container fade-in">
        <h2>Add New User</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="new_username">Username:</label>
                <input type="text" id="new_username" name="new_username" required>
            </div>
            <div class="form-group">
                <label for="new_password">Password:</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <input type="submit" value="Add User">
        </form>
    </div>

    <div class="fade-in">
        <h2>Manage Users</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                      
                        <td>
                            <form class="modify-form" method="POST" action="modify_user.php">
                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                <button type="submit" class="btn">Modify</button>
                            </form>
                            <form class="delete-form" method="POST" action="delete1_user.php">
                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                <button type="submit" class="btn" onclick="return confirm('Are you sure you want to delete this user?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination">
        <?php
        // Numri total i përdoruesve për të llogaritur numrin e faqeve
        $total_result = $conn->query("SELECT COUNT(*) AS total FROM users WHERE manager_id = $manager_id");
        $total_rows = $total_result->fetch_assoc()['total'];
        $total_pages = ceil($total_rows / $limit);

        // Butonat e paginimit
        for ($i = 1; $i <= $total_pages; $i++):
            if ($i === $page):
                echo "<button disabled>$i</button>";
            else:
                echo "<a href=\"?page=$i\"><button>$i</button></a>";
            endif;
        endfor;
        ?>
    </div>\
    <script>
    // Merr modalin dhe butonat
    var modal = document.getElementById("modifyModal");
    var btns = document.querySelectorAll(".modify-form button");
    var span = document.getElementsByClassName("close")[0];

    // Funksioni për të hapur modalin dhe plotësuar të dhënat
    btns.forEach(function(btn) {
        btn.onclick = function(event) {
            event.preventDefault();
            var user_id = this.closest("form").querySelector("input[name='user_id']").value;
            var username = this.closest("tr").querySelector("td:nth-child(2)").innerText;

            document.getElementById("modal_user_id").value = user_id;
            document.getElementById("modal_username").value = username;
            modal.style.display = "block";
        };
    });

    // Funksioni për të mbyllur modalin
    span.onclick = function() {
        modal.style.display = "none";
    };

    // Mbyll modalin kur klikohet jashtë
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    };
</script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
