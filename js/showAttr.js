$().ready(function(){
    $("#ent").change(function(e){
        e.preventDefault(); 
        var url = $("#ent").val();
        $.ajax({
 
        // The URL for the request
        url: url
}).done(function( html ) {
        $(html).ready(function(){
            var prhases = $("#results").text();
            alert(phrases);
        });
});

});
