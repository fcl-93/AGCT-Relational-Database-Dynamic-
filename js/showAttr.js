$().ready(function(){
    $("#ent").change(function(e){
        e.preventDefault(); 
        var url = $("#ent").val();
        $.ajax({
 
        // The URL for the request
        url: url,
}).done(function( html ) {
    alert(html)
});
        //alert("O id e : " + id);
    });
});
