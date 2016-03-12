$().ready(function(){
	$("#insertForm").validate({
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
	$("#editForm").validate({
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
		
});
