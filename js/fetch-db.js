$().ready(function(){
   $("[data-showHidden]").mouseover(function(e){
	   	$var = $(this).find("p span").text();
	   	$(this)find("p").balloon();
    });
});