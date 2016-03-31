$().ready(function(){
   $("[data-showHidden]").mouseover(function(e){
	   	var x = $(this).find("p span").text();
        $(this).balloon({ position: "null", content: x });
    });
});