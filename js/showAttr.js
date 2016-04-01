$().ready(function(){
    $("#ent").change(function(e){
        e.preventDefault(); 
        var url = $("#ent").val();
        $.ajax({
 
        // The URL for the request
        url: url
}).done(function( html ) {
    html.select(function(){
        var dados = $(this).("#resultado").text();
        alert(dados);
    });
});

    });
});
