$().ready(function(){
   $("[data-showHidden]").mouseover(function(e){
	var x = $(this).find("span").text();
        x.replace(/\n/g, '<br>');
        $(this).balloon({ position: "null",  contents: x});
        //alert(x);mouseover
   });
});