<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
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
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
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
    <h1>User Management</h1>
    
    <!-- Modal for modifying user -->
    <div id="modifyUserModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeModal">&times;</span>
            <h2>Modify User</h2>
            <form id="modifyUserForm">
                <input type="hidden" id="modify_user_id" name="modify_user_id">
                <label for="modify_username">Username:</label>
                <input type="text" id="modify_username" name="modify_username" required>
                <br>
                <label for="modify_manager_id">Manager:</label>
                <select id="modify_manager_id" name="modify_manager_id" required>
                    <!-- Manager options will be loaded dynamically -->
                </select>
                <br>
                <label for="modify_role">Role:</label>
                <select id="modify_role" name="modify_role" required>
                    <option value="user">User</option>
                    <option value="manager">Manager</option>
                    <option value="admin">Admin</option>
                </select>
                <br>
                <button type="submit">Save Changes</button>
            </form>
        </div>
    </div>

    <!-- Add New User Form -->
    <h2>Add New User</h2>
    <form id="addUserForm">
        <label for="new_username">Username:</label>
        <input type="text" id="new_username" name="new_username" required>
        <br>
        <label for="new_password">Password:</label>
        <input type="password" id="new_password" name="new_password" required>
        <br>
        <label>Manager:</label>
        <?php
        // Query to get list of managers
        $sql = "SELECT id, username FROM users WHERE role = 'manager'";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            echo '<input type="radio" id="manager_' . $row['id'] . '" name="manager_id" value="' . $row['id'] . '" required>';
            echo '<label for="manager_' . $row['id'] . '">' . $row['username'] . '</label><br>';
        }
        ?>
        <button type="submit">Add User</button>
    </form>

    <!-- User List Table -->
    <h2>User List</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Role</th>
                <th>Manager</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="userListBody">
            <!-- User data will be loaded here -->
        </tbody>
    </table>
    <button id="prevPage">Previous</button>
    <span id="pageInfo">Page 1</span>
    <button id="nextPage">Next</button>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var currentPage = 1;
            var itemsPerPage = 10;
            var modifyUserId = null; // Store the ID of the user to be modified

            loadUsers(currentPage);

            document.getElementById('userListBody').addEventListener('click', function(event) {
                if (event.target.classList.contains('modify-button')) {
                    const userId = event.target.dataset.userId;
                    openModifyModal(userId);
                }
            });

            document.getElementById("addUserForm").addEventListener("submit", function(event) {
                event.preventDefault(); // Prevent form submission

                // Get values from the form
                var newUsername = document.getElementById("new_username").value;
                var newPassword = document.getElementById("new_password").value;
                var managerId = document.querySelector('input[name="manager_id"]:checked').value;

                // Create XMLHttpRequest object
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "handle_ajax.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                // Handle AJAX response
                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            loadUsers(currentPage);
                            alert(response.message);
                        } else {
                            alert("Error: " + response.message);
                        }
                    } else {
                        alert("Error: " + xhr.statusText);
                    }
                };

                // Prepare POST data
                var params = "new_username=" + encodeURIComponent(newUsername) + "&new_password=" + encodeURIComponent(newPassword) + "&manager_id=" + encodeURIComponent(managerId);

                // Send AJAX request
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

            // Function to load users based on page number
            function loadUsers(page) {
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "handle_ajax.php?page=" + page, true);
                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        var response = JSON.parse(xhr.responseText);
                        displayUserList(response.users);
                        updatePagination(response.page, response.total, response.items_per_page);
                    } else {
                        alert("Error: " + xhr.statusText);
                    }
                };
                xhr.send();
            }

            // Function to display users in the table
            function displayUserList(users) {
                var tbody = document.getElementById("userListBody");
                tbody.innerHTML = "";

                users.forEach(function(user) {
                    var tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${user.id}</td>
                        <td>${user.username}</td>
                        <td>${user.role}</td>
                        <td>${user.manager_name ? user.manager_name : "None"}</td>
                        <td>
                            <button class="modify-button" data-user-id="${user.id}">Modify</button>
                            <form action='' method='POST' style='display: inline;'>
                                <input type='hidden' name='user_id' value='${user.id}'>
                                <input type='submit' name='delete_user' value='Delete'>
                            </form>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            // Function to update pagination buttons
            function updatePagination(page, totalItems, itemsPerPage) {
                var prevPageBtn = document.getElementById("prevPage");
                var nextPageBtn = document.getElementById("nextPage");
                var pageInfo = document.getElementById("pageInfo");

                pageInfo.textContent = "Page " + page;

                if (page === 1) {
                    prevPageBtn.disabled = true;
                } else {
                    prevPageBtn.disabled = false;
                }

                var totalPages = Math.ceil(totalItems / itemsPerPage);
                if (page === totalPages || totalPages === 0) {
                    nextPageBtn.disabled = true;
                } else {
                    nextPageBtn.disabled = false;
                }
            }

            // Function to open modify user modal
            function openModifyModal(userId) {
                modifyUserId = userId;

                // Fetch user details for the selected user
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "handle_ajax.php?get_user_id=" + userId, true);
                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        var user = JSON.parse(xhr.responseText);
                        populateModifyModal(user);
                        document.getElementById("modifyUserModal").style.display = "block";
                    } else {
                        alert("Error: " + xhr.statusText);
                    }
                };
                xhr.send();
            }

            // Function to populate modify user modal with user data
            function populateModifyModal(user) {
                document.getElementById("modify_user_id").value = user.id;
                document.getElementById("modify_username").value = user.username;

                var modifyManagerSelect = document.getElementById("modify_manager_id");
                modifyManagerSelect.innerHTML = "";

                // Populate manager options
                <?php
                $sql = "SELECT id, username FROM users WHERE role = 'manager'";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    echo 'var option = document.createElement("option");';
                    echo 'option.value = "' . $row['id'] . '";';
                    echo 'option.textContent = "' . $row['username'] . '";';
                    echo 'modifyManagerSelect.appendChild(option);';
                }
                ?>

                modifyManagerSelect.value = user.manager_id;

                document.getElementById("modify_role").value = user.role;
            }

            // Close the modify user modal
            document.getElementById("closeModal").addEventListener("click", function() {
                document.getElementById("modifyUserModal").style.display = "none";
            });

            // Handle form submission for modifying user
            document.getElementById("modifyUserForm").addEventListener("submit", function(event) {
                event.preventDefault();

                var modifyUserId = document.getElementById("modify_user_id").value;
                var modifyUsername = document.getElementById("modify_username").value;
                var modifyManagerId = document.getElementById("modify_manager_id").value;
                var modifyRole = document.getElementById("modify_role").value;

                var xhr = new XMLHttpRequest();
                xhr.open("POST", "handle_ajax.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            loadUsers(currentPage);
                            document.getElementById("modifyUserModal").style.display = "none";
                            alert(response.message);
                        } else {
                            alert("Error: " + response.message);
                        }
                    } else {
                        alert("Error: " + xhr.statusText);
                    }
                };

                var params = "modify_user_id=" + encodeURIComponent(modifyUserId) + "&modify_username=" + encodeURIComponent(modifyUsername) + "&modify_manager_id=" + encodeURIComponent(modifyManagerId) + "&modify_role=" + encodeURIComponent(modifyRole);

                xhr.send(params);
            });
        });
    </script>
</body>
</html>
