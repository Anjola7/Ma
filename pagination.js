$(document).ready(function(){
    function loadPage(page){
        $.ajax({
            url: 'ajax_pagination.php',
            type: 'post',
            data: {page: page},
            success: function(response){
                $(".table-container").html(response);
            }
        });
    }
    
    $(document).on('click', '.pagination li a', function(e){
        e.preventDefault();
        var page = $(this).attr('data-page');
        loadPage(page);
    });
    
    loadPage();
});
