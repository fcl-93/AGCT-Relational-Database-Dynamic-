$().ready(function(){
    $("#ent").change(function(){
        var idEnt = $("#ent").val();
        var data = {
            'id': idEnt
        };

        // The variable ajax_url should be the URL of the admin-ajax.php file
        $.post( admin_url( 'getAttr.php' ), data, function(response) {
            console.log( response );
        }, 'json');
        
    });
});
