$().ready(function(){
   $("[data-showHidden]").click(function(e){
	   	var x = $(this).find("p span").text();
        $(this).balloon({ position: "top", content: x });
        //alert(x);mouseover
   });
});