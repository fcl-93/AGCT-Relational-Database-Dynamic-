$().ready(function(){
   $("[data-href]").mouseover(function(e){
	   	e.preventDefault();	//WON'T CHAGE THE NEW PAGE
	   	var link = $(this).attr('data-href'); //Gets the url 
	   	//console.log(link);
	   	$.ajax({ //Make the ajax request
	        url: link,
	        cache: false
	      }).done(function(rcvdData)
	      {
	    	  //alert(rcvdData);
	    		  });
	   	
    });
});