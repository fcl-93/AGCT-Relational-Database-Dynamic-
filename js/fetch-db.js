$().ready(function(){
   $("[data-showHidden]").mouseover(function(e){
	   	$var = $(this).find("span").text();
	   	alert($var);
    });
});