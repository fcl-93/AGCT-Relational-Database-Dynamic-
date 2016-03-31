$().ready(function(){
   $("[data-showHidden]").mouseover(function(e){
	var x = $(this).find("#wrapper").html();
        $(this).balloon({ position: "null",  contents: x});
        //alert(x);mouseover
   });
});