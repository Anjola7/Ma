
document.addEventListener("DOMContentLoaded", function() {
    loadUserList(1); // Ngarko faqen e parë të listës së përdoruesve

    // Funksioni për ngarkimin e listës së përdoruesve
    function loadUserList(page) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "manager_ajax_pagination.php?page=" + page, true);
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                // Përgjigja është suksess
                document.getElementById("userListContainer").innerHTML = xhr.responseText;
            } else {
                // Përgjigja është një gabim
                console.error("Error: " + xhr.statusText);
            }
        };
        xhr.send();
    }
});
