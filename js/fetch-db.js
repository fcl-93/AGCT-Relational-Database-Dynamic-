$().ready(function(){
   $("[data-showHidden]").mouseover(function(e){
	   	$var = $(this).find("p span").text();
        $("[data-showHidden] td span").balloon({ position: "null", content: $var });
    });
});