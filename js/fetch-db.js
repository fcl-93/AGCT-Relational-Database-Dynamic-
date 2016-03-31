$().ready(function(){
   $("[data-showHidden]").mouseover(function(e){
	var x = $(this).find("p span").text();
        $("[data-showHidden] [hidden]").balloon({ position: "null", content: "Olá Olé Oli Olo Olu", css: z-index="9999"});
        //alert(x);mouseover
   });
});