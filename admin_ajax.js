document.addEventListener('DOMContentLoaded', function() {
    var currentPage = 1;
    var itemsPerPage = 10;

    loadUsers(currentPage);

    document.getElementById('userListBody').addEventListener('click', function(event) {
        if (event.target.classList.contains('modify-button')) {
            const userId = event.target.dataset.userId;
            openModifyModal(userId);
        } else if (event.target.classList.contains('delete-button')) {
            const userId = event.target.dataset.userId;
            deleteUser(userId);
        }
    });

    document.getElementById("addUserForm").addEventListener("submit", function(event) {
        event.preventDefault(); // Parandalo ngarkimin e formës së re

        // Merr vlerat nga forma
        var newUsername = document.getElementById("new_username").value;
        var newPassword = document.getElementById("new_password").value;
        var managerId = document.querySelector('input[name="manager_id"]:checked').value;

        // Krijimi i objektit XMLHttpRequest
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "handle_ajax.php", true); // Vendosja e metodes dhe adresës së skedarit PHP
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        // Vendos event listener për përfundimin e kërkesës AJAX
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                // Përgjigja është suksess
                loadUsers(currentPage); // Rifresko listën e përdoruesve
                alert(xhr.responseText); // Trego mesazhin e suksesit
            } else {
                // Përgjigja është një gabim
                alert("Error: " + xhr.statusText);
            }
        };

        // Konvertimi i të dhënave në formatin e duhur të POST-it
        var params = "new_username=" + encodeURIComponent(newUsername) + "&new_password=" + encodeURIComponent(newPassword) + "&manager_id=" + encodeURIComponent(managerId);

        // Dërgimi i kërkesës AJAX
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
                userListBody.innerHTML = ""; // Pastroni përmbajtjen e tabelës
    
                users.forEach(function(user) {
                    var row = document.createElement("tr");
                    row.innerHTML = "<td>" + user.id + "</td>" +
                                    "<td>" + user.username + "</td>" +
                                    "<td>" + user.role + "</td>" +
                                    "<td>" + user.manager_name + "</td>" +
                                    "<td><button class='modify-button' data-user-id='" + user.id + "'>Modify</button></td>" +
                                    "<td><button class='delete-button' data-user-id='" + user.id + "'>Delete</button></td>";
                    userListBody.appendChild(row);
                });
    
                document.getElementById("pageInfo").textContent = "Page " + page;
                document.getElementById("prevPage").disabled = (page <= 1);
                document.getElementById("nextPage").disabled = (page >= totalPages);
            } else {
                alert("Error: " + xhr.statusText);
            }
        };
        xhr.send();
    }

    function deleteUser(userId) {
        if (confirm("Are you sure you want to delete this user?")) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "handle_delete_user.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    loadUsers(currentPage);
                    alert("User deleted successfully.");
                } else {
                    alert("Error: " + xhr.statusText);
                }
            };
            var params = "user_id=" + encodeURIComponent(userId);
            xhr.send(params);
        }
    }

    var modifyUserModal = document.getElementById("modifyUserModal");
    var modifyUserForm = document.getElementById("modifyUserForm");
    var span = document.getElementsByClassName("close")[0];
    
    span.onclick = function() {
        modifyUserModal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == modifyUserModal) {
            modifyUserModal.style.display = "none";
        }
    }

    modifyUserForm.addEventListener("submit", function(event) {
        event.preventDefault();
        
        var userId = document.getElementById("modify_user_id").value;
        var username = document.getElementById("modify_username").value;
        var password = document.getElementById("modify_password").value;
        var role = document.querySelector('input[name="role"]:checked').value;
        
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "handle_modify_user.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                loadUsers(currentPage);
                modifyUserModal.style.display = "none";
            } else {
                alert("Error: " + xhr.statusText);
            }
        };
        
        var params = "user_id=" + encodeURIComponent(userId) + 
                     "&username=" + encodeURIComponent(username) + 
                     "&password=" + encodeURIComponent(password) + 
                     "&role=" + encodeURIComponent(role);
        xhr.send(params);
    });

    function openModifyModal(userId) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "get_user.php?user_id=" + userId, true);
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                var user = JSON.parse(xhr.responseText);
                document.getElementById("modify_user_id").value = user.id;
                document.getElementById("modify_username").value = user.username;
                document.getElementById("modify_password").value = "";
                document.getElementById("modify_old_password").value = "";
                document.querySelector('input[name="role"][value="' + user.role + '"]').checked = true;
    
                var managerSelect = document.getElementById("modify_manager");
                managerSelect.innerHTML = "";
    
                if (user.role === 'user') {
                    // Only show managers if the role is user
                    user.managers.forEach(function(manager) {
                        var option = document.createElement("option");
                        option.value = manager.id;
                        option.textContent = manager.username;
                        if (user.manager_id === manager.id) {
                            option.selected = true;
                        }
                        managerSelect.appendChild(option);
                    });
                    managerSelect.disabled = false;
                } else {
                    // Disable manager selection if the role is not user
                    managerSelect.disabled = true;
                    var option = document.createElement("option");
                    option.textContent = "No managers available";
                    managerSelect.appendChild(option);
                }
                
                document.getElementById("modifyUserModal").style.display = "block";
            } else {
                alert("Error: " + xhr.statusText);
            }
        };
        xhr.send();
    }
});
