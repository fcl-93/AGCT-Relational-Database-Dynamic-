<?php
require_once("custom/php/common.php");
$link = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);


if ( is_user_logged_in() ) 
{
	if(current_user_can('dynamic_search'))
	{
		if(empty($_REQUEST['estado']))
		{
			$result = mysqli_query($link, "SELECT * FROM component ORDER BY name ASC");
			/* determina o número de linhas do conjunto de resultados */
			$row_cnt = mysqli_num_rows($result);
			//se o número de linhas do conjunto de resultados for = 0,não ha componentes mas se ouver linhas entra no if abaixo
			if($row_cnt == 0)
			{
				echo 'Não pode efetuar pesquisas uma vez que ainda não foram introduzidas componentes.';
			}
			else
			{
				echo '<h3>Pesquisa Dinâmica - escolher componente</h3>';
				$querynameComp = "SELECT id, name FROM  comp_type";
				$resnameComp = mysqli_query($link,$querynameComp);
				echo '<ul>';
				echo '<li> Componente: </li>';
				echo '<br>';
				while($nameComp = mysqli_fetch_assoc($resnameComp))
				{ 
					echo '<ul>';
					//imprime o tipo de componente
					echo '<li>'.$nameComp['name'].'</li>';
					echo '<br>';
						//Apresenta uma lista de componentes 
						$queryCompRef = "SELECT component.name, component.id FROM component INNER JOIN property ON property.comp_fk_id = component.id AND component.comp_type_id = ".$nameComp['id']."";
						$resCompRef = mysqli_query($link,$queryCompRef);		
						echo '<ul>';
						while($nomeCompRef = mysqli_fetch_assoc($resCompRef))
						{
							//imprime os componentes que têm pelo menos uma referencia a comp_fk_id 
							echo '<li><a href="pesquisa-dinamica?estado=escolha&propriedade='.$nomeCompRef['id'].'">['.$nomeCompRef['name'].']</a></li>';
						}
						echo '</ul>';
					echo '</ul>';
				}
				echo '</ul>';
			}
		
		}
		else if(!strcmp($_REQUEST['estado'],'escolha'))
		{
			$queryPropsPorComp = "SELECT * FROM  property WHERE property.component_id = ".$_REQUEST['propriedade']."";
			$querycompName = "SELECT name FROM component WHERE component.id = ".$_REQUEST['propriedade']."";
			$compName = mysqli_query($link,$querycompName);
			$compName = mysqli_fetch_assoc($compName);
			//echo $queryPropsPorComp;
			$resPrpsComp = mysqli_query($link,$queryPropsPorComp);
			echo '<ul>';
				echo  '<li> Lista de propriedades do componente '.$compName['name'].'</li>';
				echo '<ul>';
			echo '<form method=\'POST\'>';
				//Envia no form o valor do component selecionado
				//echo'<input type="hidden" name="idCompSel" value="'.$_REQUEST['propriedade'].'">';
				echo'<table>';
				//Array de operadores que está declarado no common, e que está na função operadores                   
				$operadoresArray =  operadores();
				$count = 0;
				if(mysqli_num_rows($resPrpsComp) == 0)
				{
					echo '<li>Não existem propriedades para o componente '.$compName['name'].'.</li>';
				}

				while($propriedades = mysqli_fetch_assoc($resPrpsComp))
				{
						$count++;
						echo'<tr>';
						//Nome das propriedades do componente selecionado
						echo'<td>'.$propriedades['name'].'</td>';
						//Imprimecheck boxes
						echo '<td><input type="checkbox" name="check'.'_'.$count.'" value="'.$propriedades['id'].'"></td>';
						//Verificaseovalue type das propriedades é enum, int,double,booletc.
							switch ($propriedades['value_type'])
							{
								case 'enum':
									//buscar valores permitidos para o commponentes enum.
									$getCompPermited = "SELECT * FROM prop_allowed_value WHERE prop_allowed_value.property_id = ".$propriedades['id']." AND prop_allowed_value.state = 'active'	";
									$getValPer = mysqli_query($link,$getCompPermited);
									echo '<td>';
										echo'<select name="enum_'.$count.'">';
										while($nameValPerm = mysqli_fetch_assoc($getValPer))
										{
											echo '<option>'.$nameValPerm['value'].'</option>';
										}
										echo'</select>';
									echo '</td>';
									break;
								case 'bool':
									echo '<td>';
									echo'<select name="bool_'.$count.'">';
										echo '<option> False </option>';
										echo '<option> True </option>';
									echo'</select>';
									echo '</td>';
									break;
								case 'double':
									echo '<td>';
										echo '<select name="operadores_'.$count.'">';
											echo '<option> </option>'; //Resolve o problema de os operadores irem sempre em set
											foreach($operadoresArray as$key=>$value)
											{
												echo '<option>'.$value.'</option>';
											}
										echo '</select>';
										echo '	<input type="text" name="double_'.$count.'"><br>';
									echo '</td>';
									break;
								case 'text':
									echo '<td>';
									//
										echo '<input type="text" name="text_'.$count.'"><br>';
									echo '</td>';
									break;
								case 'int':
									echo '<td>';
										echo '<select  name="operadores_'.$count.'">';
											echo '<option> </option>'; //Resolve o problema de os operadores irem sempre em set
											foreach($operadoresArray as$key=>$value)
											{
												echo '<option>'.$value.'</option>';
											}
										echo '</select>';
										echo '	<input type="text" name="int_'.$count.'"><br>';
									echo '</td>';
									//	echo'Last name: <input type="text" name="lname"><br>';
									break;
							} 
						echo '</tr>';
				}
				echo'</table>';
				echo '</ul>';
			echo '</ul>';
			

			//Apresentar lita de componente que satisfaçam pelo menos uma propriedade cujo value_type seja "comp_ref" e cujo atributo comp_fk_id referencie o componente escolhido
			$queryCompProp = "SELECT component.id, component.name FROM component, property WHERE component.id = property.component_id AND property.value_type = 'comp_ref' AND property.comp_fk_id = ".$_REQUEST['propriedade'] ;
			//echo $queryCompProp ;
			$resComp = mysqli_query($link,$queryCompProp);
			//echo mysqli_error($link);
			echo '<ul>';
				echo  '<li> Propriedades de componentes que contenham pelo menos uma propriedade que referêncie o componente '.$compName['name'].'</li>';
				echo '<ul>';
					if(mysqli_num_rows($resComp) == 0)
					{
						echo '<li>Não existem propriedades de componentes que referenciem o componente '.$compName['name'].'!</li>';
					}
					//Listar propriedades dos componentes que têm propriedades que referenciam a componente selecionada
					while($compRef = mysqli_fetch_assoc($resComp))
					{	
						echo '<ul>';	
							echo '<li>Propriedades do componente : '.$compRef['name'].'</li>';
								//Busca dos componentes;
								$propRefoCompSelec = "SELECT * FROM property WHERE property.component_id = " .$compRef['id']." ";
								//echo $propRefoCompSelec ;
								$execRefSelec = mysqli_query($link,$propRefoCompSelec);
								echo '<table>';
									//imprime as propriedades do componente que referencia o componete selecionado
							 		while($propstuff = mysqli_fetch_assoc($execRefSelec))
							 		{
							 			$count++;
							 			echo '<tr>';
							 			if($propstuff['value_type'] != 'comp_ref')
							 			{
							 				echo '<td>'.$propstuff['name'].'</td>';
							 				echo '<td><input type="checkbox" name="check'.'_'.$count.'" value="'.$propstuff['id'].'"></td>';
							 			}
							 				//Lista dos possiveis valores a selecionar
							 				//switch
							 				switch ($propstuff['value_type'])
											{
												case 'enum':
													//buscar valores permitidos para o commponentes enum.
													$getCompPermited = "SELECT * FROM prop_allowed_value WHERE prop_allowed_value.property_id = ".$propstuff['id']." AND prop_allowed_value.state = 'active'	";
													$getValPer = mysqli_query($link,$getCompPermited);
													echo '<td>';
														echo'<select name="enum_'.$count.'">';
														while($nameValPerm = mysqli_fetch_assoc($getValPer))
														{
															echo '<option>'.$nameValPerm['value'].'</option>';
														}
														echo'</select>';
													echo '</td>';
													break;
												case 'bool':
													echo '<td>';
													echo'<select name="bool_'.$count.'">';
														echo '<option> False </option>';
														echo '<option> True </option>';
													echo'</select>';
													echo '</td>';
													break;
												case 'double':
													echo '<td>';
														echo '<select name="operadores_'.$count.'">';
															echo '<option> </option>'; //Resolve o problema de os operadores irem sempre em set
															foreach($operadoresArray as$key=>$value)
															{
																echo '<option>'.$value.'</option>';
															}
														echo '</select>';
														echo '	<input type="text" name="double_'.$count.'"><br>';
													echo '</td>';
													break;
												case 'text':
													echo '<td>';
													//
														echo '<input type="text" name="text_'.$count.'"><br>';
													echo '</td>';
													break;
												case 'int':
													echo '<td>';
														echo '<select  name="operadores_'.$count.'">';
															echo '<option> </option>'; //Resolve o problema de os operadores irem sempre em set
															foreach($operadoresArray as$key=>$value)
															{
																echo '<option>'.$value.'</option>';
															}
														echo '</select>';
														echo '	<input type="text" name="int_'.$count.'"><br>';
													echo '</td>';
													break;
												case 'comp_ref':
													echo'<input type="hidden" name="comp_ref" value="'.$propstuff['id'] .'">';
													break;
											} 

							 			echo '</tr>';
							 		}
								echo '</table>';
						echo '</ul>';
					}
				echo '</ul>';

			echo '</ul>';
				echo'<input type="hidden" name="numChecks" value="'.$count.'">';
				echo'<input type="hidden" name="estado" value="execucao">';
				echo'<input type="submit" value="Pesquisar">';
			echo '</form>';
		}
		else if(!strcmp($_REQUEST['estado'],'execucao'))
		{
			$componenteSelecionado = $_REQUEST['propriedade']; // vem pelo get é o id do componente selecionado.
			$numeroDechecksImpressos = $_REQUEST['numChecks'];	//numero de checkboxes impressas na pagina anterior == ao numero de propriedades.
			//echo $numeroDechecksImpressos;
			$erroParametros = false;
			$contaChecksSelecionadas = 0;
			  
			$fraseGerada = "";
			$fraseParteFinal = "";
			$entrouNaPrimeira = false;
			print_r($_REQUEST);	
			//Prepara a query dinamica
			//$querydinamica1 = "SELECT comp_inst.component_name FROM value, comp_inst WHERE comp_inst.component_name = value.value";
			$querydinamica = "SELECT * FROM comp_inst, value, property WHERE  comp_inst.id = value.comp_inst_id AND comp_inst.component_id = ".$componenteSelecionado." AND value.property_id = property.id AND ";
			$valor = " AND value.value IN (";
			$propriedades = "property.id IN (";
			//percorre o request 
			$checkSelected = 0;
			$i = 0;
			$guardanomePropSelec = array();
			$guardaValorDaProp = array();
			$guardaidDosSelecionados = array();
			while( $i <=  $numeroDechecksImpressos)
			{
				if(isset($_REQUEST['check'.'_'.$i]))
				{ //significa que tudo o que tem qq coisa underscore count não foi selecionado
					$checkSelected++;
				}
				$i++;
			}

			for($count = 1 ;$count <= $numeroDechecksImpressos; $count++ )
			{
						
				//CheckBoxes não foram selecionadas
				if(empty($_REQUEST['check'.'_'.$count]))
				{ //significa que tudo o que tem qq coisa underscore count não foi selecionado
					$contaChecksSelecionadas++;
				}
				//checkboxes selecionadas.
				else
				{
					$idDaPropriedade = $_REQUEST['check'.'_'.$count]; //Como a check foi selecionada então vai mandar o id da propriedade via post
					$querynomeProp = "SELECT name FROM property where id = " .  $idDaPropriedade;
					$querynomeProp = mysqli_query($link,$querynomeProp);
					$nomeProp = mysqli_fetch_assoc($querynomeProp);
					
					//verificar se operadores.$count existem
					if(isset($_REQUEST['operadores_'.$count.'']))
					{
						//valida se o utilzador  introduziu ou não o operador para o filtro selecionado.
						if($_REQUEST['operadores_'.$count.'']=='')
						{
							echo 'Verifique se introduziu os operadores.';
							$erroParametros = true;
						}
						//significa que vem double || int.
						else
						{
							if(isset($_REQUEST['double_'.$count.'']))
							{
								$double_escaped = mysqli_real_escape_string($link,$_REQUEST['double_'.$count.'']);
								if(is_numeric($double_escaped))
								{
									$double_escaped = floatval($double_escaped);
									if(is_double ($double_escaped))
									{
										$propriedades = $propriedades.$idDaPropriedade;
										$valor = $valor."'".$double_escaped."'";
										//Guarda o nome do componente
										array_push($guardaidDosSelecionados,$idDaPropriedade);
										array_push($guardanomePropSelec, $nomeProp);
										array_push($guardaValorDaProp,$double_escaped);
									}
									else
									{
										echo 'Verifique se introduziu um valor númerico.';
										$erroParametros = true;
									}
								}
								else
								{
									echo 'Verifique se introduziu um valor númerico.';
									$erroParametros = true;	
								}
								
							}
							else
							{

								$int_escaped = mysqli_real_escape_string($link,$_REQUEST['int_'.$count.'']);
								if(ctype_digit($int_escaped))
								{	
									//Se todo o input do user são numeros então converter para inteitro
									$int_escaped = (int)$int_escaped;
									if(is_int($int_escaped))
									{			
										$propriedades = $propriedades.$idDaPropriedade;
										$valor = $valor."'".$int_escaped."'";
										//Guarda o nome do componente
										array_push($guardaidDosSelecionados,$idDaPropriedade);
										array_push($guardanomePropSelec, $nomeProp);
										array_push($guardaValorDaProp,$int_escaped);
									}
									else
									{
										echo 'Verifique se introduziu um valor númerico.';
										$erroParametros = true;
									}
								}
								else
								{
									echo 'Verifique se introduziu um valor númerico.';
									$erroParametros = true;
								}
								
							}
						}
					}
					else
					{
						//vêm os enum || boolean || text 

						if(isset($_REQUEST['enum_'.$count.'']))
						{
							$propriedades = $propriedades.$idDaPropriedade;
							$valor = $valor."'".$_REQUEST['enum_'.$count.'']."'";
							array_push($guardaidDosSelecionados,$idDaPropriedade);
							array_push($guardanomePropSelec, $nomeProp);
							array_push($guardaValorDaProp,$_REQUEST['enum_'.$count.'']);

					
						}
						else if(isset($_REQUEST['bool_'.$count.'']))
						{
							$propriedades = $propriedades.$idDaPropriedade;
							$valor = $valor."'".$_REQUEST['bool_'.$count.'']."'";
							array_push($guardaidDosSelecionados,$idDaPropriedade);
							array_push($guardanomePropSelec, $nomeProp);
							array_push($guardaValorDaProp,$_REQUEST['enum_'.$count.'']);


								
						}
						else if(isset($_REQUEST['text_'.$count.'']))
						{
							if($_REQUEST['text_'.$count.''] != '')
							{
								$propriedades = $propriedades.$idDaPropriedade;
								$valor = $valor."'".$_REQUEST['text_'.$count.'']."'";
								//Guarda o nome do componente
								array_push($guardaidDosSelecionados,$idDaPropriedade);
								array_push($guardanomePropSelec, $nomeProp);
								array_push($guardaValorDaProp,$_REQUEST['text_'.$count.'']);
							}
							else
							{
								echo 'Verifique se preencheu todos os campos.';
								$erroParametros = true;
							}
							
						}
					}
					if($count < $checkSelected)
					{
							$propriedades = $propriedades." , ";
							$valor = $valor." , ";
					}
				}		
			}

			if($count> $numeroDechecksImpressos)
			{
				$propriedades = $propriedades." ) ";
				$valor = $valor." ) ";
			}
				/*if(isset($_REQUEST['comp_ref']))
				{
					$queryCR = "SELECT * FROM property WHERE id = ".$_REQUEST['comp_ref'];
					$execCR = mysqli_query($link,$queryCR);
					$execCR = mysqli_fetch_assoc($execCR);
					//todas as instancias que estão referenciadas pelo componente selecionado
					$queryInstancias = "SELECT comp_inst.component_name FROM value, comp_inst WHERE  value.property_id = ".$execCR['id']." AND comp_inst.component_name = value.value ";


					$instaComp = mysqli_query($link,$queryInstancias." IN(".$querydinamica.") ");				
				}*/

						
			
			//Quando não seleciona nenhuma checkbox
			//echo $count; 
			//echo $contaChecksSelecionadas;
			if($count - 1 == $contaChecksSelecionadas )
			{
				//Significa que não se selecionou nada pelo que a query deverá ser default ou seja listar todas as instancias do componente selecionado.
				$fraseGerada = "Seleciona todas as instâncias da tabela comp_inst, uma vez que não foram selecionados quaisquer filtros.";
				$querydinamica = "SELECT * FROM comp_inst WHERE comp_inst.component_id = ".$componenteSelecionado." ";
				//echo "Entrou aqui corrigido";
			}
			//echo $querydinamica;
			if($erroParametros)
			{
				goBack();
			}
			else
			{	
					$numeroDepropsSelected = ($count - 1 ) - $contaChecksSelecionadas;
					
					$querydinamica = $querydinamica .$propriedades .$valor ." GROUP BY comp_inst.id having COUNT(*) = ". $numeroDepropsSelected ;
					//echo '<br>';
					//echo $querydinamica;
					//echo '<br>';
					$instaComp = mysqli_query($link,$querydinamica);		
					//imprime a lista de instancias do componente selecionado de acordo com os filtros
					echo '<table>';
						echo '<td>Id</td>';
						echo '<td>Instância</td>';
					$arrayInstId = array();
					$arrayInstComp = array();
					while($instancias = mysqli_fetch_assoc($instaComp))
					{
						
							echo '<tr>';
								echo '<td>'.$instancias['id'].'</td>';
								echo '<td>'.$instancias['component_name'].'</td>';
							echo '</tr>'; 	

							array_push($arrayInstId,$instancias['id']);
							array_push($arrayInstComp,$instancias['component_name']); 
					}
					echo '</table>'; 
					$fraseGerada = "Filtra as instancias do componente  através de um conjunto de propriedades que foram selecionadas e  ";
					//$fraseGerada .= $fraseParteFinal;
					//gera o ficheiro xlsx

					geraExcel($querydinamica,$link,$fraseGerada,$guardaidDosSelecionados,$guardanomePropSelec,$guardaValorDaProp,$arrayInstId,$arrayInstComp);
				
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

//Função que gera o ficheiro xlsx recebe a frase gerada dinamicamente, a ligaçção a base de dados e a query dinamica gerada a partir dos trios.
function geraExcel($querydinamica,$link,$frase,$arrayId,$arrayNomes, $arrayValores,$arrayInstId,$arrayInstComp)
{
			error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	date_default_timezone_set('Europe/London');

	if (PHP_SAPI == 'cli')
		die('This example should only be run from a Web Browser');

	/** Include PHPExcel */
	require_once 'PHPExcel/Classes/PHPExcel.php';
	require_once 'PHPExcel/Classes/PHPExcel/Writer/Excel2007.php';

	// Create new PHPExcel object
	$objPHPExcel = new PHPExcel();

	// Set document properties
	$objPHPExcel->getProperties()->setCreator("")
								 ->setLastModifiedBy("")
								 ->setTitle("")
								 ->setSubject("")
								 ->setDescription("")
								 ->setKeywords("")
								 ->setCategory("");

	//Frase gerada de forma automática
	$linha = 1;
	$coluna = 'A';
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coluna.$linha, $frase);	 

	//A segunda linha deve conter o cabeçalho com os nomes em form-field-name
	
	$linha = 2;
	$coluna = 'A';

	for($i = 0; $i < count($arrayId); $i++)
	{
		$get_form_field_name = "SELECT form_field_name FROM property WHERE id = ".$arrayId[$i];
		$fieldformnames = mysqli_query($link,$get_form_field_name);
		while($names = mysqli_fetch_assoc($fieldformnames))
		{
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coluna.$linha, $names['form_field_name']);
			$objPHPExcel->getActiveSheet()->getColumnDimension($coluna)->setAutoSize(true);
			$coluna++;
		}
	}

	$percorre = 0;
	$linha = 3;
	$coluna = 'A';
	while($percorre < count($arrayNomes) )
	{
		
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coluna.$linha, $arrayNomes[$percorre]['name']);
		$objPHPExcel->getActiveSheet()->getColumnDimension($coluna)->setAutoSize(true);
		$coluna++;
		$percorre++;
	}

	$percorre = 0;
	$linha = 4;
	$coluna = 'A';
	while($percorre < count($arrayValores) )
	{
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coluna.$linha, $arrayValores[$percorre]);
		$objPHPExcel->getActiveSheet()->getColumnDimension($coluna)->setAutoSize(true);
		$coluna++;
		$percorre++;
	}


		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A6','ID');
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B6','Instâncias');

	$percorre = 0;
	$linha = 7;
	$coluna = 'A';
	while($percorre < count($arrayInstComp))
	{
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coluna.$linha,$arrayInstId[$percorre]);
		$coluna++;
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($coluna.$linha,$arrayInstComp[$percorre]);
		$coluna = 'A';
		$linha++;
		$percorre++;
	}
	// a terceira linha os nomes em si das propriedades.

	$objPHPExcel->getActiveSheet()->setTitle('Simple');


	// Set active sheet index to the first sheet, so Excel opens this as the first sheet

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

	//Link para download do xlsx gerado
	echo '<a href="/test01.xlsx" target="_blank">Clique aqui para descarregar</a>';
	$objWriter->save("/opt/bitnami/apps/wordpress/htdocs/test01.xlsx");
	//$objWriter->save('php://output');

	exit;
}


?>