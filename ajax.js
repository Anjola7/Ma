document.addEventListener("DOMContentLoaded", function() {
    loadUserList(); // Ngarko listën e parë të përdoruesve në fillim

    // Event listener për formën për shtimin e përdoruesit
    var addUserForm = document.getElementById("addUserForm");
    addUserForm.addEventListener("submit", function(event) {
        event.preventDefault(); // Parandalo ngarkimin e formës së re

        var newUsername = document.getElementById("new_username").value;
        var newPassword = document.getElementById("new_password").value;

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "manager_ajax.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                loadUserList(); // Rifresko listën e përdoruesve pas shtimit të ri
                alert(xhr.responseText); // Trego mesazhin e suksesit
            } else {
                alert("Error: " + xhr.statusText);
            }
        };

        var params = "new_username=" + encodeURIComponent(newUsername) + "&new_password=" + encodeURIComponent(newPassword);
        xhr.send(params);
    });

    // Funksioni për ngarkimin e listës së përdoruesve
    function loadUserList() {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "manager_ajax.php", true);
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                var userListContainer = document.getElementById("userListContainer");
                userListContainer.innerHTML = xhr.responseText; // Rifresko përmbajtjen e kontejnerit të listës së përdoruesve
            } else {
                alert("Error: " + xhr.statusText);
            }
        };
        xhr.send();
    }
});
