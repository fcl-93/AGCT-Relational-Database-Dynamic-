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
		
//	});
        $("#insertProp").validate({
		rules:{
			nome: "required",
                        tipoValor: "required",
                        relacaoPertence: "required",
                        entidadePertence: "required",
                        tipoCampo: "required",
                        ordem: {
                            required: true,
                            min:1
                        },
                        obrigatorio:"required"
		},
		messages:{
			nome:"Por favor introduza o nome da propriedade.",
                        tipoValor:"Por favor selecione um tipo de valor para a sua entidade.",
                        relacaoPertence:"Por favor selecione a relação a que irá pertencer esta propriedade.",
                        entidadePertence:"Por favor selecione a relação a que irá pertencer esta propriedade.",
                        tipoCampo:"Por favor selecione um tipo do campo do formulário.",
                        ordem:"Por favor introduza um valor superior a 0.",
                        obrigatorio:"Por favor indique se esta propriedade deve ou não ser obrigatória."
		}
	});
	$("#editProp").validate({
		rules:{
			nome: "required",
                        tipoValor: "required",
                        relacaoPertence: "required",
                        entidadePertence: "required",
                        tipoCampo: "required",
                        ordem: {
                            required: true,
                            min:1
                        },
                        obrigatorio:"required"
		},
		messages:{
			nome:"Por favor introduza o nome da propriedade.",
                        tipoValor:"Por favor selecione um tipo de valor para a sua entidade.",
                        relacaoPertence:"Por favor selecione a relação a que irá pertencer esta propriedade.",
                        entidadePertence:"Por favor selecione a relação a que irá pertencer esta propriedade.",
                        tipoCampo:"Por favor selecione um tipo do campo do formulário.",
                        ordem:"Por favor introduza um valor superior a 0.",
                        obrigatorio:"Por favor indique se esta propriedade deve ou não ser obrigatória."
		}
	});
	
	
	
	
	
	
	
	$("#table").tablesorter();
		
});