$().ready(function(){
   $("[data-showHidden]").mouseover(function(e){
	var x = $(this).find("p span").text();
        $("[data-showHidden] [hidden]").balloon({ position: "null",  contents: '<a href="#">Any HTML!</a><br />'
    +'<input type="text" size="40" />'
    +'<input type="submit" value="Search" />'});
        //alert(x);mouseover
   });
});