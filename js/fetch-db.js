$().ready(function(){
   $("[data-showHidden]").mouseover(function(e){
	var x = $(this).find("span").text().split("\n");;
        $(this).balloon({ position: "null",  contents: x});
        //alert(x);mouseover
   });
});