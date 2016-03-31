$().ready(function(){
   $("[data-showHidden]").mouseover(function(e){
	   	$var = $(this).find("p span").text().after("<br/>;");
	   	alert($var);
    });
});