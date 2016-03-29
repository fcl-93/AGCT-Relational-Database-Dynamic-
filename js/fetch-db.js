$().ready(function(){
   $("[data-href]").mouseover(function(e){
	   	e.preventDefault();	//não deixa ir para um novo url
	   	var link = $(this).attr('data-href'); //Gets the url 
	   	console.log(link);
    });
});