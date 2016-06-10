$().ready(function(){
	//insertion form 
	$("#insertForm").validate({
		rules:{
			nome:"required",
			atv_int: "required",
			valor: "required"
		},
		messages:{
			nome:"Por favor introduza o nome da entidade",
			atv_int:"Por favor selecione o estado da entidade",
			valor: "Por favor introduza um novo valor para o enum selecionado"
		}
	});
	//edition form 
	$("#editForm").validate({
		rules:{
			nome:"required",
			atv_int: "required",
			valor: "required"
		},
		messages:{
			nome:"Por favor introduza o nome da entidade",
			atv_int:"Por favor selecione o estado da entidade",
			valor: "Por favor introduza um novo valor para o enum selecionado"
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
                        entidadePertence:"Por favor selecione a entidade a que irá pertencer esta propriedade.",
                        tipoCampo:"Por favor selecione um tipo do campo do formulário.",
                        ordem:"Por favor introduza um valor superior a 0.",
                        obrigatorio:"Por favor indique se esta propriedade deve ou não ser obrigatória."
		}
	});
        
        $("#insertProp").change(function(){
            alert($('input[name=tipoCampo]:checked', '#insertProp').val()); 
});

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
	
        
});
