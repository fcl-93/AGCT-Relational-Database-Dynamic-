$().ready(function(){
    $("#ent").change(function(e){
        e.preventDefault(); 
        var url = $("#ent").val();
        $.ajax({
 
        // The URL for the request
        url: url
}).done(function( html ) {
        var x = $(html).find("#results").text();
            //alert(x);
            x = x.replace(/\n/g, '<br>');
            $("#showAttr").html("<p>"+x+"</p>").fadeIn(9999);
            });
        });


});
