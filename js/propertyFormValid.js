$().ready(function(){
	$("#insertForm").validate({
		rules:{
			name: "required",
                        typeValue: "required",
                        entRel: "required",
                        formType: "required",
                        order: {
                            required: true,
                            min:1
                        },
                        mandatory:"required"
		},
		messages:{
			name:"Por favor introduza o nome da propriedade.",
                        typeValue:"Por favor selecione um tipo de valor para a sua entidade.",
                        entRel:"Por favor selecione a entidade/relação a que irá pertencer esta propriedade.",
                        formType:"Por favor selecione um tipo de do campo do formulário.",
                        order:"Por favor introduza um valor superior a 0.",
                        mandatory:"Por favor indique se esta propriedade deve ou não ser obrigatória."
		}
	});
	$("#editForm").validate({
		rules:{
			
		},
		messages:{
			
		}
	});
	$("#table").tablesorter();
		
});
