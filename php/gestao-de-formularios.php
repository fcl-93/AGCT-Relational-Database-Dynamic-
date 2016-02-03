<?php
require_once("custom/php/common.php");

$link = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
$capability = 'manage_custom_forms';

//Verifica se algum utilizador está com sessão iniciada
if ( is_user_logged_in() )
{
	// Verifica se o utilziador atual tem a capability necessária para esta componente
	if(current_user_can($capability))
	{
		if(empty($_REQUEST["estado"]))
		{
			// Session leva o numero de propriedades para ser transportado entre páginas
			$_SESSION['numPropriedades'] = 0;
			$_SESSION['numPropriedades'] = apresentaTabelaForm($link);
		}
		else if(!strcmp($_REQUEST["estado"], "inserir"))
		{
			if(verificaDados())
			{
				insereDados($link);
			}
		}
		else if (!strcmp($_REQUEST["estado"], "editar_form")) 
		{
			$_SESSION['numPropriedades'] = 0;
			$_SESSION['numPropriedades'] = formEditaDados($link);
		}
		else if (!strcmp($_REQUEST["estado"], "atualizar_form_custom")) 
		{
			if(verificaDados())
			{
				atualizaDados($link);
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
	$numForms = 0;
	$numProp = 0;
	$result = mysqli_query($link, "SELECT * FROM custom_form ORDER BY name ASC");
	$numForms = mysqli_num_rows($result);
	if($numForms == 0)
	{
		echo 'Não existem formulários costumizados';
	}
	else
	{
		echo "<table border='1'>
		<tr>
		<th>id</th>
		<th>formulário</th>
		</tr>";
	
		while($row = mysqli_fetch_array($result)) 
		{
			echo "<tr>";
			echo "<td>".$row['id']."</td>";
			echo '<td>
			<a href="?estado=editar_form&id='.$row['id'].'">'.$row['name'].'</a></td>';
			/*<form method="GET" id="select_form">
				<input type="hidden" name="estado" value="editar_form"> 
				<input type="hidden" name="id" value="'.$row['id'].'">
				<input type="submit" name="nome" value="'.$row['name'].'">
			</form></td>';*/
			echo "</tr>";
		}
		//fechar a tabela
		echo "</table>";
		
	}
	echo '
	<h3>Gestão de formulários customizados - Introdução</h3>';
	$obtemComponente = 'SELECT * FROM component';
	$resultObtemComponente = mysqli_query($link, $obtemComponente);
	$numComp = mysqli_num_rows($resultObtemComponente);
	if($numComp == 0)
	{
		echo 'Não pode criar formulários uma vez que ainda não foram inseridas quaisquer componentes';
	}
	else
	{
		echo'
		<form method="POST">
			<input type="hidden" name="estado" value="inserir">
			<label>Nome do formulário customizado:</label><br>
			<input type="text" name="nome" required><br>
		<table class="mytable" style="text-align: left; width: 100%;" border="1" cellpadding="2" cellspacing="2">		
			<tbody>
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
				<th>escolher</th>
				<th>ordem</th>
				</tr>';
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
						$numProp++;	
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
						// Coloco uma checkbox o name leva concatenado o $numProp para ser mais simples fazer as ligações entre a checkbox e o text do campo ordem
						echo '<td><input type="checkbox" name="idProp'.$numProp.'" value="'.$arrayResObtemPropriedade["id"].'"></td>';
						// Text para colocar o campo ordem
						echo '<td><input type="text" name="ordem'.$numProp.'"></td>';
						echo '</tr>';
					}
	
				}
		echo '</tbody>
		<table>
		
		<input type="submit" value="Inserir formulário">
			
		</form>';
		// Retorna para a SESSION o numero de propriedades existentes na tabela
		return $numProp;
	}
	
}
// Verifica se os dados foram corretamente preenchidos
function verificaDados()
{
	if(empty($_REQUEST))
	{
		echo "ERRO! Não preencheu o formulário com nenhum valor!<br>";
		goBack();
		return false;
	}

	$controlaCheck = 0;
	for($i = 1; $i <= $_SESSION['numPropriedades']; $i++)
	{
		if(empty($_REQUEST["idProp".$i]))
		{
			$controlaCheck++;
		}
	}
	if($controlaCheck == $_SESSION['numPropriedades'])
	{
		echo "ERRO! Deve selecionar pelo menos 1 propriedade para o seu formulário!<br>";
		goBack();
		return false;
	}
	else
	{
		// tenho pelo menos 1 checlbox checked logo posso avançar
	}

	for($i = 1; $i <= $_SESSION['numPropriedades']; $i++)
	{
		if((!is_numeric($_REQUEST["ordem".$i]) || $_REQUEST["ordem".$i] < 1) && isset($_REQUEST["idProp".$i]))
		{
			echo "ERRO! O campo ordem deve ser numérico e superior a 0!<br>";
			goBack();
			return false;
		}
	}
	return true;
}

function atualizaDados($link)
{
	mysqli_autocommit($link,false);
	mysqli_begin_transaction($link);
	$queryInsereForm = "UPDATE custom_form  SET name = '".mysqli_real_escape_string($link, $_REQUEST["nome"])."' WHERE id = ".$_REQUEST['id'];
	$queryInsereForm = mysqli_query($link,$queryInsereForm);
	if(!$queryInsereForm)
	{
		echo'ERRO #1 '.mysqli_error($link);
		mysqli_rollback($link);
	}
	else
	{
		$id = $_REQUEST['id'];
		$controlaInserts = true;
		$queryApagaRelacoes = 'DELETE FROM custom_form_has_property WHERE custom_form_id = '.$id;
		$queryApagaRelacoes = mysqli_query($link, $queryApagaRelacoes);
		if(!$queryApagaRelacoes)
		{
			echo'ERRO #2 '.mysqli_error($link);
			mysqli_rollback($link);
		}
		else
		{
			for($i = 1; $i <= $_SESSION['numPropriedades']; $i++)
			{
				if(isset($_REQUEST["idProp".$i]) && isset($_REQUEST["ordem".$i])) 
				{
					$queryInsereRelation = "INSERT INTO `custom_form_has_property`(`custom_form_id`, `property_id`, `field_order`) VALUES (".$id.",".$_REQUEST["idProp".$i].",'".mysqli_real_escape_string($link, $_REQUEST["ordem".$i])."')";
					echo $queryInsereRelation;
					$queryInsereRelation = mysqli_query($link,$queryInsereRelation);
					if(!$queryInsereRelation)
					{
						echo 'ERRO #3 '.mysqli_error($link);
						mysqli_rollback($link);
						$controlaInserts = false;
						break;
					}
				}
			}
			if($controlaInserts)
			{
				mysqli_commit($link);
				echo 'Atualizou o seu formulário com sucesso!';
				echo 'Clique em <a href="/gestao-de-formularios/">Continuar</a> para avançar.';
			}
		}
		
	}
}
function insereDados($link)
{
	mysqli_autocommit($link,false);
	mysqli_begin_transaction($link);

	$queryInsereForm = "INSERT INTO `custom_form`(`id`, `name`)  VALUES (NULL,'".mysqli_real_escape_string($link,$_REQUEST["nome"])."')";
	$queryInsereForm = mysqli_query($link,$queryInsereForm);
	if(!$queryInsereForm)
	{
		echo'ERRO #1 '.mysqli_error($link);
		mysqli_rollback($link);
	}
	else
	{
		$id = mysqli_insert_id ($link);
		$controlaInserts = true;
		for($i = 1; $i <= $_SESSION['numPropriedades']; $i++)
		{
			if(isset($_REQUEST["idProp".$i]) && isset($_REQUEST["ordem".$i])) 
			{
				$queryInsereRelation = "INSERT INTO `custom_form_has_property`(`custom_form_id`, `property_id`, `field_order`) VALUES (".$id.",".$_REQUEST["idProp".$i].",'".mysqli_real_escape_string($link, $_REQUEST["ordem".$i])."')";
				$queryInsereRelation = mysqli_query($link,$queryInsereRelation);
				if(!$queryInsereRelation)
				{
					echo 'ERRO #2 '.mysqli_error($link);
					mysqli_rollback($link);
					$controlaInserts = false;
					break;
				}
			}
		}
		if($controlaInserts)
		{
			mysqli_commit($link);
			echo 'Inseriu um novo formulário com sucesso.';
			echo 'Clique em <a href="/gestao-de-formularios/">Continuar</a> para avançar.';
		}
	}
}
function formEditaDados($link)
{
	$numProp = 0;
	$queryNome = "SELECT name FROM custom_form WHERE id = ".$_REQUEST['id'];
	$queryNome = mysqli_query($link, $queryNome);
	$nome = mysqli_fetch_assoc($queryNome)['name'];
	?>
	<form method="POST">
		<input type="hidden" name="estado" value="atualizar_form_custom">
		<label>Nome do formulário customizado:</label><br>
		<input type="text" name="nome" value="<?php echo $nome?>"><br>
		<?php
		echo '
	<table class="mytable" style="text-align: left; width: 100%;" border="1" cellpadding="2" cellspacing="2">		
		<tbody>
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
			<th>escolher</th>
			<th>ordem</th>
			</tr>';
			$obtemComponente = 'SELECT * FROM component';
			$resultObtemComponente = mysqli_query($link, $obtemComponente);
			while($arrayRes = mysqli_fetch_assoc ($resultObtemComponente))
			{
				$nome = '\''.$arrayRes["name"].'\'';
				$obtemPropriedade = 'SELECT p.id, p.name, p.value_type, p.form_field_name, p.form_field_type, p.unit_type_id, p.form_field_order, p.form_field_size, p.mandatory, p.state FROM property AS p, component AS c WHERE  p.component_id = c.id AND c.name LIKE '.$nome.' ORDER BY p.name ASC';
				
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
					$numProp++;	
					echo '<td>'.$arrayResObtemPropriedade["id"].'</td>';
					echo '<td>'.$arrayResObtemPropriedade["name"].'</td>';
					echo '<td>'.$arrayResObtemPropriedade["value_type"].'</td>';
					echo '<td>'.$arrayResObtemPropriedade["form_field_name"].'</td>';
					echo '<td>'.$arrayResObtemPropriedade["form_field_type"].'</td>';
					echo '<td>';
					if(is_null($arrayResObtemPropriedade["unit_type_id"]))
					{
						echo '-';
					}
					else
					{
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

						$queryAssChecks = "SELECT * FROM custom_form_has_property AS cfhp WHERE cfhp.custom_form_id = ".$_REQUEST['id']." AND cfhp.property_id = ".$arrayResObtemPropriedade["id"];

						$queryAssChecks = mysqli_query($link, $queryAssChecks);


						if(mysqli_num_rows($queryAssChecks) == 1)
						{
							$arrayChecks = mysqli_fetch_assoc($queryAssChecks);
							echo '<td><input type="checkbox" name="idProp'.$numProp.'" value="'.$arrayResObtemPropriedade["id"].'" checked></td>';
							echo '<td><input type="text" name="ordem'.$numProp.'" value="'.$arrayChecks["field_order"].'"></td>';
						}
						else
						{
							echo '<td><input type="checkbox" name="idProp'.$numProp.'" value="'.$arrayResObtemPropriedade["id"].'"></td>';
							echo '<td><input type="text" name="ordem'.$numProp.'"></td>';
						}

						echo '<input type="hidden" name="id" value="'.$_REQUEST['id'].'">';
						echo '</tr>';
					

										
					
				}


			}
	echo '</tbody>
	<table>
	
	<input type="submit" value="Atualizar formulário">
		
	</form>';
	return $numProp;
}
?>