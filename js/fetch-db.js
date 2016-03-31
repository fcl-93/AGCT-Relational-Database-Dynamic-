$().ready(function(){
   $("[data-showHidden]").mouseover(function(e){
	   	var x = $(this).find("p span").text();
        //$("td [data-showHidden]").balloon({ position: "null", content: x });
        alert(x);
   });
});