$().ready(function(){
    $("#ent").change(function(e){
        e.preventDefault(); 
        var url = $("#ent").val();
        $.ajax({
 
        // The URL for the request
        url: url
}).done(function( response ) {
    $("results").html(response);
    });
});

    });
});
