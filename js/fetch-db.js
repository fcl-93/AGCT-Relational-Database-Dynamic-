$().ready(function(){
   $("[data-showHidden]").mouseover(function(e){
	var x = $(this).find("span").text();
        $(this).balloon({ position: "null",  contents: x});
        //alert(x);mouseover
   });
});