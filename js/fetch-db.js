$().ready(function(){
   $("[data-showHidden]").mouseover(function(e){
	var x = $(this).find("p span").text();
        $(this).balloon({ position: "top", content: x }, css:{zIndex: "32767"});
        //alert(x);mouseover
   });
});