$().ready(function(){
   $("[data-showHidden]").mouseover(function(e){
	   	$var = $(this).find("span");
	   	alert($var);
    });
});