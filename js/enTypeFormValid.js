$().ready(function(){
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
	$("#table").tablesorter({widthFixed: true, widgets: ['zebra']}).tablesorterPager({container: $("#pager")}); 
		
});