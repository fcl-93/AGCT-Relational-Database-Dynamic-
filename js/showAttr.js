$().ready(function(){
    $("#ent").change(function(){
        var idEnt = $("#ent").val();
        $.ajax({
 
    // The URL for the request
    url: "/getAttr",
 
    // The data to send (will be converted to a query string)
    data: {
        id: idEnt
    },
 
    // Whether this is a POST or GET request
    type: "GET",
 
    // The type of data we expect back
    dataType : "json",
    
    success: function(data) {
        alert("Sucesso");
    },
    error: function() {
        alert("Erro"):
    }

});
        //alert("O id e : " + id);
    });
});
