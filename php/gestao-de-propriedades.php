<?php
require_once("custom/php/common.php");

$capability = 'manage_properties';

$link = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);

//Verifica se algum utilizador está com sessão iniciada
if ( is_user_logged_in() )
{
	// Verifica se o utilziador atual tem a capability necessária para esta componente
	if(current_user_can($capability))
	{
		if(empty($_REQUEST["estado"]))
		{
			if ($result = mysqli_query($link, "SELECT * FROM property")) 
			{
			    // Verifica se existem tuplos na tabela property
			    $row_cnt = mysqli_num_rows($result);
				if($row_cnt == 0)
				{
					echo 'Não há propiedades especificadas.';
					apresentaTabelaForm($link);
				}
				else
				{
					apresentaTabelaForm($link);
				}
			}
		}
		// Se o estado for inserir
		else if(!strcmp($_REQUEST["estado"], "inserir"))
		{
			if(verificaDados())
			{
				insereDados($link);
			}
			
		}
		
	}
	else
	{
		echo 'Não tem autorização para a aceder a esta página.';
	}
}
else 
{
	echo 'O utilizador não tem sessão iniciada.';
}

// Apresenta a tabela e o formulário da página Gestão de propriedades
function apresentaTabelaForm($link)
{
	$numComponentes = 0;
	$obtemComponente = 'SELECT * FROM component';
	$resultObtemComponente = mysqli_query($link, $obtemComponente);
	$numComponentes = mysqli_num_rows($resultObtemComponente);
	if($numComponentes != 0)
	{
		echo '
	<table class="mytable" style="text-align: left; width: 100%;" border="1" cellpadding="2" cellspacing="2">'; // CSS para que toda a tabela fique dentro da página
	echo '<tbody>
			<tr>
			<th>componente</th>
			<th>id</th>
			<th>propriedade</th>
			<th>tipo de valor</th>
			<th>nome do campo no formulário</th>
			<th>tipo do campo no formulário</th>
			<th>tipo de unidade</th>
			<th>ordem do campo no formulário</th>
			<th>tamanho do campo no formulário</th>
			<th>obrigatório</th>
			<th>estado</th>
			<th>ação</th>
			</tr>';
			$obtemComponente = 'SELECT * FROM component';
			$resultObtemComponente = mysqli_query($link, $obtemComponente);
			while($arrayRes = mysqli_fetch_assoc ($resultObtemComponente))
			{
				$nome = '\''.$arrayRes["name"].'\'';
				// Seleciono todos os atributos de property necessários apresentar na tabela e que possuem correspondencia com o componente que é escrito na primeira coluna, ordenados por nome de componente
				$obtemPropriedade = 'SELECT p.id, p.name, p.value_type, p.form_field_name, p.form_field_type, p.unit_type_id, p.form_field_order, p.form_field_size, p.mandatory, p.state FROM property AS p, component AS c WHERE p.component_id = c.id AND c.name LIKE '.$nome.' ORDER BY p.name ASC';
				
				$resultObtemPropriedade = mysqli_query($link, $obtemPropriedade);
				if(!$resultObtemPropriedade)
				{
					printf("%s", mysqli_error($link));
				}
				$numLinhas = mysqli_num_rows($resultObtemPropriedade);
				echo '<tr>';
				echo '<td colspan="1" rowspan="'.$numLinhas.'" style="vertical-align: top;">'.$arrayRes["name"].'</td>';
				while($arrayResObtemPropriedade = mysqli_fetch_assoc ($resultObtemPropriedade))
				{
					
					echo '<td>'.$arrayResObtemPropriedade["id"].'</td>';
					echo '<td>'.$arrayResObtemPropriedade["name"].'</td>';
					echo '<td>'.$arrayResObtemPropriedade["value_type"].'</td>';
					echo '<td>'.$arrayResObtemPropriedade["form_field_name"].'</td>';
					echo '<td>'.$arrayResObtemPropriedade["form_field_type"].'</td>';
					echo '<td>';
					// Caso a propriedade nao tenha uma unidade escreve-se um - na célula da tabela
					if(is_null($arrayResObtemPropriedade["unit_type_id"]))
					{
						echo '-';
					}
					else
					{
						// Seleciona o nome da unidade da propriedade
						$unitNameQuery = 'SELECT name FROM prop_unit_type WHERE id = '.$arrayResObtemPropriedade["unit_type_id"];
						$resultObtemNome = mysqli_query($link, $unitNameQuery);
						if(!$resultObtemNome)
						{
							printf("%s", mysqli_error($link));
						}
						while($arrayResObtemNome = mysqli_fetch_assoc ($resultObtemNome))
						{
							echo $arrayResObtemNome["name"];
						}
					}
					echo '</td>';
					echo '<td>'.$arrayResObtemPropriedade["form_field_order"].'</td>';
					echo '<td>'.$arrayResObtemPropriedade["form_field_size"].'</td>';
					echo '<td>';
					if($arrayResObtemPropriedade["mandatory"] == 1)
					{
						echo 'sim';
					}
					else
					{
						echo 'não';
					}
					echo '</td>';
					echo '<td>'.$arrayResObtemPropriedade["state"].'</td>';
					echo '<td>[editar][desativar]</td>';
					echo '</tr>';
				}


			}
	echo '</tbody>
	<table>';
	}
	echo'
	<h3> Gestão de propriedades - introdução </h3>';
	if($numComponentes == 0)
	{
		echo 'Não pode inserir prpriedades uma vez que ainda não foram criados quaisquer componentes';
	}
	else
	{
		echo '
		<form method="POST">
			<label>Nome da Propriedade:</label><br>
			<input type="text" name="nome" required>
			<br><br>
			 <!--Botões de seeção de tipo-->
			<label>Tipo de valor:</label><br>';
			$field = 'value_type';
			$table = 'property';
			$array = getEnumValues($table, $field, $link);
			foreach($array as $values)
			{
				//echo $values;
				echo' <input type="radio" name="tipoValor" value="'.$values.'" required>'.$values.'<br>';
			}
	
			
			echo'
			<br>
			<label>Componente a que irá pertencer esta propriedade</label><br>
			<select name="componentePertence" required>';
			$selecionaComponentes = "SELECT name, id FROM component";
			$result = mysqli_query($link, $selecionaComponentes);
			while($guardaComponente = mysqli_fetch_assoc($result))
			{
				echo '<option value="'.$guardaComponente["id"].'">'.$guardaComponente["name"].'</option>';
			}
			echo '</select><br><br>
			<label>Tipo do campo do formulário</label><br>';
			$field = 'form_field_type';
			$table = 'property';
			$array = getEnumValues($table, $field, $link);
			foreach($array as $values)
			{
				//echo $values;
				echo' <input type="radio" name="tipoCampo" value="'.$values.'" required>'.$values.'<br>';
			}
			echo '
			<br>
			<label>Tipo de unidade</label><br>
			<select name="tipoUnidade">
			<option value="NULL"></option>';
			$selecionaTipoUnidade = "SELECT name, id FROM prop_unit_type";
			$result = mysqli_query($link, $selecionaTipoUnidade);
			while($guardaTipoUnidade = mysqli_fetch_assoc($result))
			{
				echo '<option value="'.$guardaTipoUnidade["id"].'">'.$guardaTipoUnidade["name"].'</option>';
			}		
			echo '</select><br><br>
			<label>Ordem do campo no formulário</label><br>
			<input type="text" name="ordem" min="1" required><br><br>';										//Verificar minimo
			echo '<label>Tamanho do campo no formulário</label><br>
			<input type="text" name="tamanho"><br><br>'; 															//Verificar obrigatório dependendo do tipo de campo
			echo '<label>Obrigatório</label><br>
			<input type="radio" name="obrigatorio" value="1" required>Sim
			<br>
			<input type="radio" name="obrigatorio" value="2" required>Não
			<br><br>
			<label>Componente referenciado por esta propriedade</label><br>
			<select name="componenteReferenciado">
			<option value="NULL"></option>';
			$selecionaComponentes = "SELECT id, name FROM component";
			$result = mysqli_query($link, $selecionaComponentes);
			while($guardaComponente = mysqli_fetch_array($result))
			{
				echo '<option value="'.$guardaComponente["id"].'">'.$guardaComponente["name"].'</option>';
			}
			echo '</select><br><br>
			<input type="hidden" name="estado" value="inserir"><br>
			<input type="submit" value="Inserir propriedade">
			
		</form>';
	}
	
}

// Verifica se todos os campos obrigatórios estão preenchidos e se foram preenchidos com os valores esperados
function verificaDados(){
	if(!is_numeric($_REQUEST["ordem"]) || empty($_REQUEST["ordem"]))
	{
		echo 'ERRO! O valor introduzido no campo Ordem do campo no formulário não é numérico!<br>';
		goBack();
		echo '<br>';
		return false;
	}
	else if($_REQUEST["ordem"] < 1)
	{
		echo 'ERRO! O valor introduzido no campo Ordem do campo no formulário deve ser superior a 0!<br>';
		goBack();
		echo '<br>';
		return false;
	}
	if(($_REQUEST["tipoCampo"] == "text") && (!is_numeric($_REQUEST["tamanho"]) || empty($_REQUEST["tamanho"])))
	{
		echo 'ERRO! O campo Tamanho do campo no formulário deve ser preenchido com valores numéricos 
			uma vez que indicou que o Tipo do campo do formulário era text<br>';
		goBack();
		echo '<br>';
		return false;
	}
	// preg_match serve para verificar se o valor introduzido está no formato aaxbb onde aa e bb são números de 0 a 9
	if(($_REQUEST["tipoCampo"] == "textbox") && ((preg_match("/[0-9]{2}x[0-9]{2}/", $_REQUEST["tamanho"]) === 0) || empty($_REQUEST["tamanho"])))
	{
		echo 'ERRO! O campo Tamanho do campo no formulário deve ser preenchido com o seguinte formato
		 aaxbb em que aa é o número de colunas e bb o número de linhas da caixa de texto<br>';
		goBack();
		echo '<br>';
		return false;
	}
	return true;

	
}
// Insere os dados introduzidos na BD após a validação dos mesmos
function insereDados($link){
	echo '<h3>Gestão de propriedades - inserção</h3>';
	$componenteQuery = 'SELECT name FROM component WHERE id = '.$_REQUEST["componentePertence"];
	$componentResult = mysqli_query($link,$componenteQuery);
	$componenteArray = mysqli_fetch_assoc($componentResult);
	// contrução do form_field_name
	// obtém-se o nome da componente a que corresponde a propriedade que queremos introduzir
	$componente = $componenteArray["name"];
	// Obtemos as suas 3 primeiras letras
	$comp = substr($componente, 0 , 3);
	$traco = '-';
	$idProp = '';
	// Garantimos que não há SQL injection através do campo nome
	$nome = mysqli_real_escape_string($link, $_REQUEST["nome"]);
	// Substituimos todos os carateres por carateres ASCII
	$nomeField = preg_replace('/[^a-z0-9_ ]/i', '', $nome);
	// Substituimos todos pos espaços por underscore
	$nomeField = str_replace(' ', '_', $nomeField);
	$form_field_name = $comp.$traco.$idProp.$traco.$nomeField;
	// Inicia uma tansação uma vez que, devido ao id no campo form_field_name vamos ter de atualizar esse atributo, após a inserção
	mysqli_autocommit($link,false);
	mysqli_begin_transaction($link);
	// De modo a evitar problemas na execução da query quando o campo form_field_size é NULL, executamos duas queries diferentes, uma sem esse campo e outra com esse campo
	if(empty($_REQUEST["tamanho"]))
	{
		$queryInsere = 'INSERT INTO `property`(`id`, `name`, `component_id`, `value_type`, `form_field_name`, `form_field_type`, `unit_type_id`, `form_field_order`, `mandatory`, `state`, `comp_fk_id`)
	 VALUES (NULL,\''.mysqli_real_escape_string($link, $_REQUEST["nome"]).'\','.$_REQUEST["componentePertence"].',\''.$_REQUEST["tipoValor"].'\',\''.$form_field_name.'\',\''.$_REQUEST["tipoCampo"].'\','.$_REQUEST["tipoUnidade"].','.mysqli_real_escape_string($link, $_REQUEST["ordem"]).','.$_REQUEST["obrigatorio"].',"active",'.$_REQUEST["componenteReferenciado"].')';
	}
	else
	{
		$queryInsere = 'INSERT INTO `property`(`id`, `name`, `component_id`, `value_type`, `form_field_name`, `form_field_type`, `unit_type_id`, `form_field_size`, `form_field_order`, `mandatory`, `state`, `comp_fk_id`)
	 VALUES (NULL,\''.mysqli_real_escape_string($link, $_REQUEST["nome"]).'\','.$_REQUEST["componentePertence"].',\''.$_REQUEST["tipoValor"].'\',\''.$form_field_name.'\',\''.$_REQUEST["tipoCampo"].'\','.$_REQUEST["tipoUnidade"].',"'.mysqli_real_escape_string($link, $_REQUEST["tamanho"]).'",'.mysqli_real_escape_string($link, $_REQUEST["ordem"]).','.$_REQUEST["obrigatorio"].',"active",'.$_REQUEST["componenteReferenciado"].')';
	}
	$insere = mysqli_query($link, $queryInsere);
	if(!$insere)
	{
		echo 'ERRO #1 '. mysqli_error($link);
		mysqli_rollback($link);
	}
	else
	{
		//obtem o último id que foi introduzido na BD
		$id = mysqli_insert_id ($link);
		// constroi novamente o form_field_name agora com o id do tuplo que acabou de ser introduzido
		$form_field_name = $comp.$traco.$id.$traco.$nomeField;
		// atualiza esse atributo
		$atualiza = "UPDATE property SET form_field_name = '".$form_field_name."' WHERE property.id = ".$id;
		$atualiza = mysqli_query($link, $atualiza);
		if(!$atualiza)
		{
			echo 'ERRO #2 '. mysqli_error($link);
			mysqli_rollback($link);
		}
		else
		{
			mysqli_commit($link);
			echo 'Inseriu os dados de nova propriedade com sucesso.';
			echo 'Clique em <a href="/gestao-de-propriedades/">Continuar</a> para avançar.';
		}
	}
}


?>