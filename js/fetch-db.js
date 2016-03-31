$().ready(function(){
   $("[data-showHidden]").mouseover(function(e){
	   	$var = $(this).find("p span").text();
    });
    $("[data-showHidden]").balloon();
});