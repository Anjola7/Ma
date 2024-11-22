<?php
session_start();

// Kontrollo sesionin për të siguruar që një përdorues është i kyçur dhe ka rol admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Përfshij skedarin e lidhjes me bazën e të dhënave
include 'db.php';

// Inicializo variablin $managers si array bosh
$managers = array();

// Query për të marrë të dhënat e menaxherëve
$managers_query = "SELECT id, username FROM users WHERE role = 'manager'";
$managers_result = $conn->query($managers_query);

// Kontrollo nëse ka rezultate nga kërkesa
if ($managers_result) {
    $managers = $managers_result->fetch_all(MYSQLI_ASSOC);
}

// Mbyll lidhjen me bazën e të dhënave
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
 
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
    width: 80%;
    margin: 20px auto;
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
}

th {
    background-color: #e9ecef; /* Ngjyra e ngjashme me sfondin e formës së login-it */
    font-weight: bold;
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

input[type="text"], input[type="password"], input[type="submit"], select {
    width: 100%;
    padding: 12px;
    margin: 5px 0;
    border-radius: 4px;
    border: 1px solid #ddd;
    font-size: 16px;
}

input[type="submit"] {
    background-color: #6c757d; /* Ngjyra e butë e sfondit për butonat */
    color: white;
    border: none;
    cursor: pointer;
    font-size: 16px;
    border-radius: 4px;
    transition: background-color 0.3s ease, transform 0.3s ease; /* Shto animacionin pulse për butonat */
    animation: pulse 2s infinite;
}

input[type="submit"]:hover {
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
    transition: background-color 0.3s ease;
}

.pagination button:hover {
    background-color: #e2e2e2;
}

.pagination button:disabled {
    cursor: not-allowed;
    background-color: #e0e0e0;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4);
}

.modal-content {
    background-color: #fff;
    margin: 10% auto;
    padding: 20px;
    border: 1px solid #ddd;
    width: 50%;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    position: relative;
    animation: fadeIn 2s ease-out forwards; /* Shto animacionin fadeIn për modalin */
}

.close {
    color: #aaa;
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 24px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

.modal h2 {
    margin-top: 0;
    font-size: 22px;
    color: #333;
}

.modal input[type="submit"] {
    width: auto;
    padding: 10px 15px;
}

/* Shto disa dekorime në sfond */
.circle {
    position: absolute;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.15);
    animation: move 8s infinite ease-in-out;
}

.circle.small {
    width: 100px;
    height: 100px;
    bottom: 20%;
    left: 10%;
    animation-duration: 6s;
}

.circle.medium {
    width: 150px;
    height: 150px;
    top: 20%;
    right: 15%;
}

.circle.large {
    width: 200px;
    height: 200px;
    bottom: 5%;
    right: 5%;
    animation-duration: 10s;
}

@keyframes move {
    0% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
    100% { transform: translateY(0); }
}


    </style>
</head>
<body>
    <header>
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> (Admin)</h1>
    </header>

    <h2>User List</h2>
    <table id="userList">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Role</th>
                <th>Manager</th>
                <th>Actions</th>
               
        </tr>
            </tr>
        </thead>
        <tbody id="userListBody">
            <!-- Përdoruesit do të ngarkohen këtu përmes AJAX -->
        </tbody>
    </table>

    <div class="pagination">
        <button id="prevPage" disabled>Previous</button>
        <span id="pageInfo">Page 1</span>
        <button id="nextPage" disabled>Next</button>
    </div>

    <h2>Create New User</h2>
    <div class="form-container">
        <form id="addUserForm">
            <div class="form-group">
                <label for="new_username">Username:</label>
                <input type="text" id="new_username" name="new_username" required>
            </div>
            <div class="form-group">
                <label for="new_password">Password:</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label>Manager:</label>
                <div class="radio-group">
                    <?php
                    foreach ($managers as $manager) {
                        echo "<input type='radio' id='manager" . $manager['id'] . "' name='manager_id' value='" . $manager['id'] . "' required>";
                        echo "<label for='manager" . $manager['id'] . "'>" . $manager['username'] . "</label>";
                    }
                    ?>
                </div>
            </div>
            <div class="form-group">
                <input type="submit" value="Create User">
            </div>
        </form>
    </div>

   
   <!-- Modal for Modify User -->
<div id="modifyUserModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Modify User</h2>
        <form id="modifyUserForm">
            <input type="hidden" id="modify_user_id" name="user_id">
            <div class="form-group">
                <label for="modify_username">Username:</label>
                <input type="text" id="modify_username" name="username" required>
            </div>
            <div class="form-group">
                <label for="modify_old_password">Old Password:</label>
                <input type="password" id="modify_old_password" name="old_password" required>
            </div>
            <div class="form-group">
                <label for="modify_password">New Password:</label>
                <input type="password" id="modify_password" name="password">
            </div>
            <div class="form-group">
                <label>Role:</label>
                <input type="radio" id="modify_role_admin" name="role" value="admin" required>
                <label for="modify_role_admin">Admin</label>
                <input type="radio" id="modify_role_manager" name="role" value="manager" required>
                <label for="modify_role_manager">Manager</label>
                <input type="radio" id="modify_role_user" name="role" value="user" required>
                <label for="modify_role_user">User</label>
            </div>
            <div class="form-group">
                <label for="modify_manager">Manager:</label>
                <select id="modify_manager" name="manager_id">
                    <!-- Options will be populated dynamically -->
                </select>
            </div>
            <input type="submit" value="Save Changes">
        </form>
    </div>
</div>

  <script>
document.addEventListener('DOMContentLoaded', function() {
    var currentPage = 1;
    var itemsPerPage = 10;

    loadUsers(currentPage);

    document.getElementById('userListBody').addEventListener('click', function(event) {
        if (event.target.classList.contains('modify-button')) {
            const userId = event.target.dataset.userId;
            openModifyModal(userId);
        }
    });

    document.getElementById("addUserForm").addEventListener("submit", function(event) {
        event.preventDefault();

        var newUsername = document.getElementById("new_username").value;
        var newPassword = document.getElementById("new_password").value;
        var managerId = document.querySelector('input[name="manager_id"]:checked').value;

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "handle_ajax.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                loadUsers(currentPage);
                alert(xhr.responseText);
            } else {
                alert("Error: " + xhr.statusText);
            }
        };

        var params = "new_username=" + encodeURIComponent(newUsername) + "&new_password=" + encodeURIComponent(newPassword) + "&manager_id=" + encodeURIComponent(managerId);
        xhr.send(params);
    });

    document.getElementById("prevPage").addEventListener("click", function() {
        if (currentPage > 1) {
            currentPage--;
            loadUsers(currentPage);
        }
    });

    document.getElementById("nextPage").addEventListener("click", function() {
        currentPage++;
        loadUsers(currentPage);
    });

    function loadUsers(page) {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "get_users.php?page=" + page + "&itemsPerPage=" + itemsPerPage, true);
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            var response = JSON.parse(xhr.responseText);
            var users = response.users;
            var totalItems = response.totalItems;
            var totalPages = Math.ceil(totalItems / itemsPerPage);

            var userListBody = document.getElementById("userListBody");
            userListBody.innerHTML = "";

            users.forEach(function(user) {
                var row = document.createElement("tr");
                row.innerHTML = "<td>" + user.id + "</td>" +
                                "<td>" + user.username + "</td>" +
                                "<td>" + user.role + "</td>" +
                                "<td>" + user.manager_name + "</td>" +
                                "<td>" +
                                    "<button class='modify-button' data-user-id='" + user.id + "'>Modify</button> " +
                                    "<button class='delete-button' data-user-id='" + user.id + "'>Delete</button>" +
                                "</td>";
                userListBody.appendChild(row);
            });

            document.getElementById("pageInfo").textContent = "Page " + page;
            document.getElementById("prevPage").disabled = (page <= 1);
            document.getElementById("nextPage").disabled = (page >= totalPages);
            document.getElementById('userListBody').addEventListener('click', function(event) {
    if (event.target.classList.contains('delete-button')) {
        const userId = event.target.dataset.userId;
        deleteUser(userId);
    }
});

        }
    };
    xhr.send();
}
function deleteUser(userId) {
    if (confirm("Are you sure you want to delete this user?")) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "delete_user.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                loadUsers(currentPage);  // Rifresko listën e përdoruesve
                alert(xhr.responseText);
            } else {
                alert("Error: " + xhr.statusText);
            }
        };

        var params = "user_id=" + encodeURIComponent(userId);
        xhr.send(params);
    }
}

    document.querySelector(".close").addEventListener("click", function() {
        document.getElementById("modifyUserModal").style.display = "none";
    });

    window.onclick = function(event) {
        if (event.target == document.getElementById("modifyUserModal")) {
            document.getElementById("modifyUserModal").style.display = "none";
        }
    };

    document.getElementById("modifyUserForm").addEventListener("submit", function(event) {
        event.preventDefault();

        var userId = document.getElementById("modify_user_id").value;
        var username = document.getElementById("modify_username").value;
        var oldPassword = document.getElementById("modify_old_password").value;
        var newPassword = document.getElementById("modify_password").value;
        var role = document.querySelector('input[name="role"]:checked').value;
        var managerId = document.getElementById("modify_manager").value;

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "handle_modify_user.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                loadUsers(currentPage);
                document.getElementById("modifyUserModal").style.display = "none";
                alert(xhr.responseText);
            } else {
                alert("Error: " + xhr.statusText);
            }
        };

        var params = "user_id=" + encodeURIComponent(userId) + 
                     "&username=" + encodeURIComponent(username) + 
                     "&old_password=" + encodeURIComponent(oldPassword) + 
                     "&new_password=" + encodeURIComponent(newPassword) + 
                     "&role=" + encodeURIComponent(role) + 
                     "&manager_id=" + encodeURIComponent(managerId);
        xhr.send(params);
    });
});
function openModifyModal(userId) {
    // Merr të dhënat e përdoruesit për modifikim përmes AJAX
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "get_user.php?user_id=" + userId, true);
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            var user = JSON.parse(xhr.responseText);

            // Vendos të dhënat në formën e modalit
            document.getElementById("modify_user_id").value = user.id;
            document.getElementById("modify_username").value = user.username;
            document.querySelector('input[name="role"][value="' + user.role + '"]').checked = true;

            // Popullo dropdown për menaxherin
            var modifyManagerSelect = document.getElementById("modify_manager");
            modifyManagerSelect.innerHTML = "";
            <?php foreach ($managers as $manager): ?>
            var option = document.createElement("option");
            option.value = "<?php echo $manager['id']; ?>";
            option.textContent = "<?php echo $manager['username']; ?>";
            if (user.manager_id == option.value) {
                option.selected = true;
            }
            modifyManagerSelect.appendChild(option);
            <?php endforeach; ?>

            // Hap modalin
            document.getElementById("modifyUserModal").style.display = "block";
        } else {
            alert("Error loading user data.");
        }
    };
    xhr.send();
}

// Mbyll modalin kur klikoni butonin close
document.querySelector('.close').addEventListener('click', function() {
    document.getElementById("modifyUserModal").style.display = "none";
});


</script>

</body>
</html>
