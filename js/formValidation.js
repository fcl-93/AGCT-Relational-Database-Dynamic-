$().ready(function(){
	//insertion form in page gestão de entidades.php
	$("#insertForm").validate({
		rules:{
			nome:"required",
			atv_int: "required"
		},
		messages:{
			nome:"Por favor introduza o nome da entidade",
			atv_int:"Por favor selecione o estado da entidade"
		}
	});
	//edition form in page gestão de entidades.php
	$("#editForm").validate({
		rules:{
			nome:"required",
			atv_int: "required"
		},
		messages:{
			nome:"Por favor introduza o nome da entidade",
			atv_int:"Por favor selecione o estado da entidade"
		}
	});
	//insertion form in page gestão de unidades
	//$("").validate({
		
	});
	
	
	
	
	
	
	
	$("#table").tablesorter();
		
});