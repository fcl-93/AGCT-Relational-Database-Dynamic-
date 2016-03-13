<?php
require_once("custom/php/common.php");
$link = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);

if ( is_user_logged_in() ) 
{

	if(current_user_can('manage_allowed_values'))
	{
		if(isset($_REQUEST['estado']) && !strcmp($_REQUEST['estado'],'introducao'))
		{
			//guardr a variavel que vem pelo get URL numa var de session
			$_SESSION['property_id'] = $_REQUEST['propriedade'];
			echo '<h3>Gestão de valores permitidos - introdução</h3> <br> <br>';

			//Inicio do form
			echo '
			<form>
				<label>Valor</label>
				<input type="text" name="valor" required> 
				<input type="hidden" name="estado" value="inserir">
				<input type="submit" value="Inserir valor permitido">
			</form>
			';

		}
		else if(isset($_REQUEST['estado']) && !strcmp($_REQUEST['estado'],'inserir'))
		{
			echo '<h3>Gestão de valores permitidos - inserção</h3>';
			$queryAddvalor = 'INSERT INTO `prop_allowed_value`(`id`, `property_id`, `value`, `state`) VALUES (NULL,'.$_SESSION['property_id'].',\''.$_REQUEST['valor'].'\',\'active\')';
			//echo $queryAddvalor;
			$adicionaNovoValor = mysqli_query($link,$queryAddvalor);
			if(!$adicionaNovoValor)
			{
				echo 'Erro ao executar a query de adição. '.mysqli_error($link);
			}
			else
			{
				echo 'Inseriu os dados de novo valor permitido com sucesso.';
				echo 'Clique em <a href="gestao-de-valores-permitidos"> Continuar </a> para avançar';
			}


		}
		else if(empty($_RESQUEST['estado']))
		{
        		//Buscar valores da tabela propriedados que tem tipo enum.
			$queryEnumCheck = "SELECT * FROM property WHERE value_type = 'enum'";
			$valuesEnum = mysqli_query($link,$queryEnumCheck);
			
			if(mysqli_num_rows($valuesEnum) == 0)
			{
				echo 'Não há propriedades especificadas cujo tipo de valor seja enum. Especificar primeiro nova(s) propriedade(s) e depois voltar a esta opção.';
			}
			else
			{
        			//Tabela a ser apresentada
				echo '<html>
				<table>
					<tbody>
						<tr>
							<td>componente</td>
							<td>id</td>
							<td>propriedade</td>
							<td>id</td>
							<td>valores permitidos</td>
							<td>estado</td>
							<td>acção</td>
						</tr>';
						$array = array();
						while($valoresEnum = mysqli_fetch_assoc($valuesEnum))
						{

							echo '<tr>'; 

							  				//echo ''.$valoresEnum['component_id'];
							$queryPropAllowed = "SELECT * FROM prop_allowed_value WHERE property_id = ".$valoresEnum['id'];
							$propAllowed = mysqli_query($link,$queryPropAllowed);

							$queryGetValores = "SELECT id, name FROM component WHERE id = ".$valoresEnum['component_id'];
							$nomeComponente = mysqli_query($link,$queryGetValores);
							$nomeComponente = mysqli_fetch_assoc($nomeComponente);

							//Acerto dos rowspan caso exista valores repetidos como tv.
							$acertaRowSpan = "SELECT * FROM prop_allowed_value as pav ,property as prop, component as comp WHERE comp.id = ".$nomeComponente['id']." AND  prop.component_id = ".$nomeComponente['id']." AND prop.value_type = 'enum' AND prop.id = pav.property_id";
							$acerta = mysqli_query($link,$acertaRowSpan);

							$verificaNumComp = "SELECT * FROM property WHERE component_id = ".$valoresEnum['component_id']." AND value_type = 'enum'";
							//echo $verificaNumComp;
							$getVerificaComp = mysqli_query($link,$verificaNumComp);

							//Verifica se o nome que vou escrever já foi escrito alguma vez
							$conta = 0;
							for($i = 0; $i < count($array); $i++)
							{
								if($array[$i] == $nomeComponente['name'])
								{
									$conta++;
								}
							}

							if($conta == 0)
							{
								echo '<td rowspan='.mysqli_num_rows($acerta).'>';	
								echo $nomeComponente['name'];
								$array[] = $nomeComponente['name'];
							}
							else
							{
								//echo '<td rowspan='.mysqli_num_rows($acerta).'>';	

							}

							echo '<td rowspan='.mysqli_num_rows($propAllowed).'>';
							echo ''.$valoresEnum['id'];
							echo '</td>';
							//Nome da propriedade
							echo '<td rowspan='.mysqli_num_rows($propAllowed).'>';
							echo '<a href="gestao-de-valores-permitidos?estado=introducao&propriedade='.$valoresEnum['id'].'">['.$valoresEnum['name'].']</a>';
							//echo '['.$valoresEnum['name'].']';
							echo '</td>';	

							
											//$propAllowedArray = mysqli_fetch_assoc($propAllowed);
							while($propAllowedArray = mysqli_fetch_assoc($propAllowed))
							{											
								if(mysqli_num_rows($propAllowed) == 0)
								{	
									echo '<td colspan=4>';
									echo "Não há valores permitidos definidos";
									echo '</td>';	
								}
								else
								{
									echo '<td>';
									echo $propAllowedArray['id'];
									echo '</td>';
									echo '<td>';
									echo $propAllowedArray['value'];
									echo '</td>';
									echo '<td>';
									echo $propAllowedArray['state'];
									echo '</td>';
									echo '<td>';
									echo '[editar]';
									echo '[desativar]';
									echo '</td>';
									echo '</tr>';
								}		
							}


							echo '</tr>';

						}
						echo '
					</tbody>
				</table>
				</html>
				';


			}
		}
		//request estado 
		
		//Outros estados do request aqui
        	///Ver os outros valores do enum
	}
	else
	{
		echo 'Não tem autorização para aceder a esta página';
	}

}
else
{
	echo 'O utilizador não tem sessão iniciada.';
}
?>