<?php
require_once("custom/php/common.php");
/** Include path **/
include 'PHPExcel/Classes/PHPExcel/IOFactory.php';

$link = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
$capability = 'values_import';

if ( is_user_logged_in() )
{
	if(current_user_can($capability))
	{
		if(empty($_REQUEST["estado"]))
		{
			$result = mysqli_query($link, "SELECT * FROM component ORDER BY name ASC");
			/* determina o número de linhas do conjunto de resultados */
			$row_cnt = mysqli_num_rows($result);
			//se o número de linhas do conjunto de resultados for = 0,não ha componentes mas se ouver linhas entra no if abaixo
			if($row_cnt == 0)
			{
				echo 'Não pode importar valores uma vez que ainda não foram introduzidas componentes.';
			}
			else
			{
				echo '<h3>Importação de Valores - escolher componente</h3>';
				apresentaLista($link);
			}
		}
		else if(!strcmp($_REQUEST["estado"], "introducao"))
		{
			estadoIntroducao($link);
		}
		else if (!strcmp($_REQUEST["estado"], "insercao")) 
		{
			estadoInsercao($link);
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

function apresentaLista($link)
{
	$selecionaTipoComponentes = "SELECT DISTINCT ct.name, ct.id FROM comp_type AS ct";
	$selecionaTipoComponentes = mysqli_query($link, $selecionaTipoComponentes);
	echo mysqli_error($link);
	echo '
	<ul>
	<li>Componentes:</li>
	<ul>';
	while($tipo=mysqli_fetch_assoc($selecionaTipoComponentes))
	{
		
		echo '<li>'.$tipo["name"].'</li>';
		$selecionaComponentes = "SELECT DISTINCT c.name, c.id FROM component AS c WHERE c.comp_type_id = ".$tipo["id"];
		$selecionaComponentes = mysqli_query($link, $selecionaComponentes);
		echo mysqli_error($link);
		echo '<ul>';
		while($componentes = mysqli_fetch_assoc($selecionaComponentes))
		{
			echo '<li><a href="importacao-de-valores?estado=introducao&comp='.$componentes['id'].'">['.$componentes["name"].']</a></li>';
		}
		echo '</ul>';
	}
	echo '
	</ul>
	<li>Formulários customizados</li>';
	$selForm = "SELECT name, id FROM custom_form";
	$selForm = mysqli_query($link, $selForm);
	echo mysqli_error($link);
	echo '<ul>';
	while($form = mysqli_fetch_assoc($selForm))
	{
		echo '<li><a href="importacao-de-valores?estado=introducao&form='.$form['id'].'">['.$form['name'].']</a></li>';
	}
	echo '</ul>';
	echo '</ul>';
}
function estadoIntroducao($link)
{
	echo'
	<table>
		<tr>';
		if(isset($_REQUEST['form']))
		{
			$selPropQuery = "SELECT p.id FROM property AS p, custom_form AS cf, custom_form_has_property AS cfhp 
									WHERE cf.id=".$_REQUEST['form']." AND cf.id = cfhp.custom_form_id AND cfhp.property_id = p.id";
		}
		else
		{
			$selPropQuery = "SELECT p.id FROM property AS p, component AS c 
									WHERE c.id=".$_REQUEST['comp']." AND p.component_id = c.id";
		}
		$selProp = mysqli_query($link, $selPropQuery);
		while($prop = mysqli_fetch_assoc($selProp))
		{
			$selFormFieldNamesQuery = "SELECT value_type, form_field_name FROM property WHERE id = ".$prop['id'];
			$selFormFieldNames = mysqli_query($link, $selFormFieldNamesQuery);
			while($formfieldnames = mysqli_fetch_assoc($selFormFieldNames))
			{
				if($formfieldnames['value_type'] == 'enum')
				{
					$querySelfAllowed = "SELECT * FROM prop_allowed_value WHERE property_id = ".$prop['id'];
					$selfAllowed = mysqli_query($link, $querySelfAllowed);
					while($linha = mysqli_fetch_assoc($selfAllowed))
					{
						echo '<td>'.$formfieldnames['form_field_name'].'</td>';	
					}
				}
				else
				{
					echo '<td>'.$formfieldnames['form_field_name'].'</td>';
				}
			}
		}
		echo '
		</tr>
		<tr>';
		$selProp = mysqli_query($link,$selPropQuery);
		while($prop = mysqli_fetch_assoc($selProp))
		{
			$selFormFieldNamesQuery = "SELECT value_type, form_field_name FROM property WHERE id = ".$prop['id'];
			$selFormFieldNames = mysqli_query($link, $selFormFieldNamesQuery);
			while($formfieldnames = mysqli_fetch_assoc($selFormFieldNames))
			{
				if($formfieldnames['value_type'] == 'enum')
				{
					$querySelfAllowed = "SELECT * FROM prop_allowed_value WHERE property_id = ".$prop['id'];
					$selfAllowed = mysqli_query($link, $querySelfAllowed);
					while($linha = mysqli_fetch_assoc($selfAllowed))
					{
						echo '<td>'.$linha['value'].'</td>';	
					}
				}
				else
				{
					echo '<td></td>';
				}
			}
		}
	echo '
		</tr>
	</table>';
	echo 'Caro utilizador,<br>
	Deverá copiar estas linhas para um ficheiro excel e introduzir os valores a importar,sendo que no caso das propriedades enum, 
	deverá constar um 0 quando esse valor permitido não se aplique à instância em causa e um 1 quando esse valor se aplica.<br>';

	echo'
	<form name="import" method="POST" enctype="multipart/form-data">
	    	<input type="file" name="file">
	    	<input type="hidden" name="estado" value="insercao">
	        <input type="submit" name="submit" value="Submeter" />
	</form>';
}

function estadoInsercao($link)
{
	$target_file = $_FILES["file"]["name"];
	$uploadOk = 1;
	$fileType = pathinfo($target_file,PATHINFO_EXTENSION);
	$sucesso = false;

	// Check if file already exists
	if (file_exists($target_file)) {
	    echo "Sorry, file already exists.";
	    $uploadOk = 0;
	}
	// Allow certain file formats
	if($fileType != "xls" && $fileType != "xlsx") {
	    echo "Apenas são permitidos ficheiros Excel.";
	    $uploadOk = 0;
	}
	// Check if $uploadOk is set to 0 by an error
	if ($uploadOk == 0) {
	    echo "Pedimos desculpa, mas o seu ficheiro não foi carregado!.";
	// if everything is ok, try to upload file
	} 
	else 
	{
		$inputFileName = $_FILES["file"]["tmp_name"];
		$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);

		$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
		$propriedadesExcel = array();
		$valoresPermitidosEnum = array();
		foreach($sheetData["1"] as $valores )
		{
			array_push($propriedadesExcel, $valores);
		}
		foreach($sheetData["2"] as $valores )
		{
			array_push($valoresPermitidosEnum, $valores);
		}
		$contaLinhas = 3;
		mysqli_autocommit($link,false);
		
		while($contaLinhas <= count($sheetData))
		{
			$i = 0;
			if(isset($_REQUEST["comp"]))
			{
				$compID = $_REQUEST["comp"];
				mysqli_begin_transaction($link);
				$queryInsertInst = "INSERT INTO `comp_inst`(`id`, `component_id`, `component_name`) VALUES (NULL,".$compID.",NULL)";
				$queryInsertInst = mysqli_query($link,$queryInsertInst);
				$idCompInst = mysqli_insert_id($link);
				if(!$queryInsertInst )
				{
					echo "Erro 1 ".mysqli_error($link);
					mysqli_rollback($link);
					$sucesso = false;
				}
			}
			$valoresIntroduzidos = array();
			$compID = 0;
			$j = 0;
			foreach($sheetData[strval($contaLinhas)] as $valores)
			{
				if(isset($_REQUEST["form"]))
				{
					$selecionaComponente = "SELECT c.id FROM component AS c, property AS p WHERE p.component_id = c.id AND p.form_field_name = '".$propriedadesExcel[$j]."'";
					$selecionaComponente = mysqli_query($link, $selecionaComponente);
					$guardaID = mysqli_fetch_assoc($selecionaComponente)['id'];
					if($guardaID != $compID)
					{
						$compID = $guardaID;
						mysqli_begin_transaction($link);
						$queryInsertInst = "INSERT INTO `comp_inst`(`id`, `component_id`, `component_name`) VALUES (NULL,".$compID.",NULL)";
						$queryInsertInst = mysqli_query($link,$queryInsertInst);
						$idCompInst = mysqli_insert_id($link);
						if(!$queryInsertInst )
						{
							echo "Erro 1 ".mysqli_error($link);
							mysqli_rollback($link);
							$sucesso = false;
						}
					}
					$j++;
				}
				$querySelectProp = "SELECT id, value_type, comp_fk_id FROM property WHERE form_field_name = '".$propriedadesExcel[$i]."'";
				$querySelectProp = mysqli_query($link, $querySelectProp);
				if(!$querySelectProp )
				{
					echo "Erro 2 ".mysqli_error($link);
					mysqli_rollback($link);
					$sucesso = false;
				}
				while($atrProp = mysqli_fetch_assoc($querySelectProp))
				{
					$idProp = $atrProp['id'];
					$value_type = $atrProp['value_type'];
					$comp_fk_id = $atrProp['comp_fk_id'];
				}
				if(empty($valoresPermitidosEnum[$i]))
				{
					$valores = mysqli_real_escape_string($link, $valores);
					$tipoCorreto = false;
					switch($value_type)
					{
						case 'int':
							if(ctype_digit($valores))
							{
								$valores = (int)$valores;
								$tipoCorreto = true;
							}
							else
							{
								echo 'O valor introduzido para o campo '.$propriedadesExcel[$i].' não está correto. Certifique-se que introduziu um valor numérico'.
								$tipoCorreto = false;
							}
							break;
						case 'double':
							if(is_numeric($valores))
							{
								
								$valores = floatval($valores);
								$tipoCorreto = true;
							}
							else
							{
								echo 'O valor introduzido para o campo '.$propriedadesExcel[$i].' não está correto. Certifique-se que introduziu um valor numérico'.
								$tipoCorreto = false;
							}
							break;
						case 'bool':
							if($valores == 'true' || $valores == 'false')
							{
								$valores = boolval($valores);
								$tipoCorreto = true;
							}
							else
							{
								echo 'O valor introduzido para o campo '.$propriedadesExcel[$i].' não está correto. Certifique-se que introduziu um valor true ou false'.
								$tipoCorreto = false;
							}
						case 'comp_ref':
							if(is_numeric($valores))
							{
								// vai buscar o id da instancia do componente que tem uma referencia de outro compoenente
								$selecionainstancia = mysqli_query($link, "SELECT `id` FROM `comp_inst` WHERE component_id = ".$comp_fk_id."");
								
								$verificaInst = false;
								while($instancia = mysqli_fetch_assoc($selecionainstancia))
								{
									if($instancia['id'] == $valores)
									{
										$valores = (int)$valores;
										$tipoCorreto = true;
										$verificaInst = true;
										break;
									}									
								}
								if($verificaInst == false)
								{
									echo ' Não existe nenhuma instância com o id que introduziu no campo '.$propriedadesExcel[$i];
									$tipoCorreto = false;
								}
							}
							else
							{
								echo 'O valor introduzido para o campo '.$propriedadesExcel[$i].' não está correto. Certifique-se que introduziu um valor numérico'.
								$tipoCorreto = false;
							}
							break;
						default: break;
							
					}
					if($tipoCorreto)
					{
						$queryInsertValue = "INSERT INTO `value`(`id`, `comp_inst_id`, `property_id`, `value`, `date`, `time`, `producer`) VALUES (NULL,".$idCompInst.", ".$idProp.",'".$valores."','".date("Y-m-d")."','".date("H:i:s")."','".wp_get_current_user()->user_login."')";
						$queryInsertValue = mysqli_query($link, $queryInsertValue);
						if(!$queryInsertValue)
						{
							echo "Erro 3 ".mysqli_error($link);
							mysqli_rollback($link);
							$sucesso = false;
						}
						else
						{
							$sucesso = true;
						}
					}
					else
					{
						$sucesso = false;
						break;
					}
					
				}
				else
				{
					if($valores == 1)
					{
						$queryInsertValue = "INSERT INTO `value`(`id`, `comp_inst_id`, `property_id`, `value`, `date`, `time`, `producer`) VALUES (NULL,".$idCompInst.", ".$idProp.",'".$valoresPermitidosEnum[$i]."','".date("Y-m-d")."','".date("H:i:s")."','".wp_get_current_user()->user_login."')";
						$queryInsertValue = mysqli_query($link, $queryInsertValue);
						if(!$queryInsertValue)
						{
							echo "Erro 3 ".mysqli_error($link);
							mysqli_rollback($link);
							$sucesso = false;
						}
						else
						{
							$sucesso = true;
						}
					}
				}
				$i++;
			}
			if($sucesso)
			{
				mysqli_commit($link);
				echo 'Os dados foram inseridos com sucesso!';
			}
			$contaLinhas++;
		}
	}
	
	
}
?>