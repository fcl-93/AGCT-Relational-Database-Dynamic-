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
        //insertion form in page gestão de propriedades.php
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
        //edition form in page gestão de propriedades.php
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
        //insertion form in page gestão de relacoes.php
	$("#insertRelation").validate({
		rules:{
			ent1: "required",
                        ent2: "required"
		},
		messages:{
			ent1: "Deve selecionar uma entidade em ambos os campos.",
                        ent2: "Deve selecionar uma entidade em ambos os campos."
		}
	});
	
	
	
	
	
	
	$("#table").tablesorter();
		
});