document.addEventListener("DOMContentLoaded", function() {
    var addUserForm = document.getElementById("addUserForm");

    addUserForm.addEventListener("submit", function(event) {
        event.preventDefault();

        var newUsername = document.getElementById("new_username").value;
        var newPassword = document.getElementById("new_password").value;

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "manager_ajax.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                loadUserList();
                alert(xhr.responseText);
            } else {
                alert("Error: " + xhr.statusText);
            }
        };

        var params = "new_username=" + encodeURIComponent(newUsername) + "&new_password=" + encodeURIComponent(newPassword);
        xhr.send(params);
    });

    function loadUserList() {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "manager_ajax.php", true);
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                var userListContainer = document.getElementById("userListContainer");
                userListContainer.innerHTML = xhr.responseText;
            } else {
                alert("Error: " + xhr.statusText);
            }
        };
        xhr.send();
    }
});
