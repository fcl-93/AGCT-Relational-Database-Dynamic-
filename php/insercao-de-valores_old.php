	<?php
		
		require_once("custom/php/common.php");
					
		$capability='insert_values';
		
		if ( is_user_logged_in() )
		{
			if(current_user_can($capability))
			{
				//estabelece conexão com base de dados do wordpress
				$link = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
	
				// Check connection
				if (mysqli_connect_errno())
				{
					echo "Failed to connect to MySQL: " . mysqli_connect_error();
				}
				//usar mysqli_query para executar queries */
				//Exemplo e verificação de uma query
				$result = mysqli_query($link, "SELECT * FROM component ORDER BY name ASC");
				if ($result) 
				{
					/* determina o número de linhas do conjunto de resultados */
					$row_cnt = mysqli_num_rows($result);
					
					//se o número de linhas do conjunto de resultados for = 0,não ha componentes mas se ouver linhas entra no if abaixo
					if($row_cnt == 0)
					{
						echo 'Não pode inserir valores uma vez que ainda não foram introduzidas componentes.';
					}
					else
					{
						// se o estado do array Request for vazio
						if(empty($_REQUEST["estado"]))
						{
							// header 3, título 
							echo "<h3>Inserção de valores - escolher componente/formulário customizado</h3>";
							//inicio da lista não ordenada
							echo "<ul>";
							//inicio do primeiro item da lista principal com o nome Componentes
							echo"<li>Componentes</li>";
							//variável guarda a query
							$queryNomeTipoComp = "SELECT id, name FROM `comp_type`";
							//Executa a query
							$buscaNomeComp = mysqli_query($link,$queryNomeTipoComp);
							//guarda um array associativo que recebe a informação da query
							while($arrayComNomesComp = mysqli_fetch_array($buscaNomeComp))
							{
								//inicio da sub-lista nao ordenada, inicio do primeiro item da sub-lista que recebe o name da query que está guardada no array associativo 
								echo "<ul><li>".$arrayComNomesComp['name']."</li><ul>";
								//variável que guarda a query que compara os id´s e imprime o id da componente e o nome sem duplicados
								$queryNomeComponente = "SELECT distinct component.id, component.name FROM `component`, `comp_type` WHERE component.comp_type_id = ".$arrayComNomesComp['id'];
								// Executa a query
								$executaNomeComponente = mysqli_query($link,$queryNomeComponente);
								
								// retorna a ultima a descrição do ultimo erro
								echo mysqli_error($link);
								
								// guarda um array associativo que recebe a informação da query, 
								while($arrayComNomesComponente = mysqli_fetch_array($executaNomeComponente))
								{
									//ligação de cada item ao endereço Inserção de Valores
									echo'<li><a href="insercao-de-valores?estado=introducao&comp='.$arrayComNomesComponente['id'].'">['.$arrayComNomesComponente['name'].']</a>';
								}
								echo "</ul>";
								echo "</ul>";
							}
							
							//item da lista com os Formulários Customizados
							echo '<li>Formulários customizados</li>';
							
							//query que busca o nome e o id dos formularios customizados
							$selForm = "SELECT name, id FROM custom_form";
							
							//execução da query
							$selForm = mysqli_query($link, $selForm);
							
							//esta função retorna o ultimo erro 
							echo mysqli_error($link);
							
							//inicio da lista
							echo '<ul>';
							
							//ciclo while que imprime linha a linha os formularios
							while($form = mysqli_fetch_assoc($selForm))
							{
								//item da lista dinamica que mostra o nome do formulario
								echo '<li><a href="insercao-de-valores?estado=introducao&form='.$form['id'].'">['.$form['name'].']</a></li>';
							}
							
							//fecho da lista
							echo"</ul>";
						}
					
						// se o estado do array Request for igual a introdução
						else if($_REQUEST["estado"] == "introducao")
						{
							//se o componente selecionado nao for um formulario significa que é um componente
							if(empty($_REQUEST['form'])  )
							{
								//variável de sessão que guarda o que vem do array associativo REQUEST, a variável comp
								$_SESSION["comp_id"] = $_REQUEST["comp"];
								
								//execução da query associada ao id do nome que esta na variavel de sessao comp_id
								$buscaid = mysqli_query($link, "SELECT `name` FROM `component` WHERE id = ".$_SESSION["comp_id"]."");					
								
								//a variavel de sessao guarda o array associativo que vai buscar o nome da componente atraves do seu id que vem da query que esta guardada na variavel $buscaid
								$_SESSION["comp_name"] = mysqli_fetch_assoc($buscaid)['name'];
								
								//execução da query que vai buscar o id da tabela comp_type
								$buscaidcomptype = mysqli_query($link, "SELECT `id` FROM `comp_type`");
								
								// variavel de sessao que guarda o array associativo que vai buscar o id á query
								$_SESSION["comp_type_id"] = mysqli_fetch_assoc($buscaidcomptype)['id'];
								
								//apresentacao do sub titulo e concatenação da variavel de sessao que guarda o nome do componente
								echo "<h3>Inserção de valores - ".$_SESSION["comp_name"]."</h3>";
								
								//inicialização do formulario dinamico, o nome do formulario, esta concatenado com a variavel de sessao que guarda o id do tipo de componente, e o action é concatenado com a variavel de sessao que guarda o id do formulário
								echo "<form method = 'POST' name='comp_type_".$_SESSION["comp_type_id"]."comp_".$_SESSION["comp_id"]."' action='?estado=validar&comp=".$_SESSION["comp_id"]."' >";
								
								//execução da query que compara os id´s e retorna todas as propriedades associadas ao componente
								$propriedadescomponente = mysqli_query($link, "SELECT `id`, `name`, `component_id`, `value_type`, `form_field_name`, `form_field_type`, `unit_type_id`, `form_field_size`, `form_field_order`, `mandatory`, `state`, `comp_fk_id` FROM `property` WHERE state = 'active' AND component_id = ".$_SESSION["comp_id"]."");
								
								//inicialização do while, array associativo que guarda o resultado da query executada em cima
								while( $propriedades = mysqli_fetch_assoc($propriedadescomponente))
								{
									//variavel que guarda o array associativo que recebe o value_type que vem da query
									$tipovalor = $propriedades['value_type'];
									
									//inicialização do switch que recebe como parametros a variavel declarada acima
									switch ($tipovalor) 
									{
										//o tipo for text
										case 'text':
											//se o form_field_type for igual a text
											if($propriedades['form_field_type'] == "text")
											{
												//poe os nomes antes das caixas de texto para os utilizadores saberem o que têm de introduzir, vai buscar o name ao array $propriedades que guarda o resultado da query
												//o nome do atributo da label é igual ao value para poderem conectar-se
												echo "<label for=".$propriedades['form_field_name'].">".$propriedades['name'].":</label></br><input type='text' name='".$propriedades['form_field_name']."'/></br>";
												
												//retorna o tipo de unidade
												echo $propriedades['unit_type_id'];
											}
											
											//se o form_field_type não for igual a text,mas igual a textbox
											else if($propriedades['form_field_type'] == "textbox")
											{
												//poe os nomes antes das caixas de texto para os utilizadores saberem o que têm de introduzir, vai buscar o name ao array $propriedades que guarda o resultado da query
												//o nome do atributo da label é igual ao value para poderem conectar-se
												echo "<label for=".$propriedades['form_field_name'].">".$propriedades['name'].":</label></br><input type='textbox' name='".$propriedades['form_field_name']."' />";
												
												//se a propriedade for diferente de nulo executa a query e mostra o resultado
												if(!is_null($propriedades['unit_type_id']))
												{
													//variavel que guarda a query que compara o id e retorna o nome da unidade
													$nomeunidade = mysqli_query($link, "SELECT `name` FROM `prop_unit_type` WHERE ".$propriedades['unit_type_id']." = prop_unit_type.id");
													
													//array associativo que recebe os resultados da query
													$recebenomeunidade = mysqli_fetch_assoc($nomeunidade);
													
													//vai buscar o name ao array associativo
													echo $recebenomeunidade['name']."</br>";
												}								
											}
										//para o caso text
										break;
										
										//o tipo for booleano
										case 'bool':
										
											echo "<label for=".$propriedades['name'].">".$propriedades['name'].":</label></br>";
											
											//retorna um input do tipo radio com o value True
											echo "<input type='radio' value = 'true' name='".$propriedades['form_field_name']."'/> Sim </br>";
											
											//retorna um input do tipo radio com o value False
											echo "<input type='radio' value = 'false' name='".$propriedades['form_field_name']."'/> Não </br>";
										
										//para o caso bool
										break;
										
										//se for do tipo inteiro
										case 'int':
										
											//poe os nomes antes das caixas de texto para os utilizadores saberem o que têm de introduzir, vai buscar o name ao array $propriedades que guarda o resultado da query
											//o nome do atributo da label é igual ao value para poderem conectar-se
											echo "<label for=".$propriedades['form_field_name'].">".$propriedades['name'].":</label></br><input type='text' name='".$propriedades['form_field_name']."'/>";
											
											//se a propriedade for diferente de nulo executa a query e mostra o resultado
											if(!is_null($propriedades['unit_type_id']))
											{
												//variavel que guarda a query que compara o id e retorna o nome
												$nomeunidade = mysqli_query($link, "SELECT `name` FROM `prop_unit_type` WHERE ".$propriedades['unit_type_id']." = prop_unit_type.id");
												
												//array associativo que recebe os resultados da query
												$recebenomeunidade = mysqli_fetch_assoc($nomeunidade);
												
												//vai buscar o name ao array associativo
												echo $recebenomeunidade['name']."</br>";
											}
		
										//fecho do caso int									
										break;
										
										//o tipo for double
										case 'double':
										
											//poe os nomes antes das caixas de texto para os utilizadores saberem o que têm de introduzir, vai buscar o name ao array $propriedades que guarda o resultado da query
											//o nome do atributo da label é igual ao value para poderem conectar-se
											echo "<label for=".$propriedades['form_field_name'].">".$propriedades['name'].":</label></br><input type='text' name='".$propriedades['form_field_name']."'/>";
											
											//se a propriedade for diferente de nulo executa a query e mostra o resultado
											if(!is_null($propriedades['unit_type_id']))
											{
												//variavel que guarda a query que compara o id e retorna o nome
												$nomeunidade = mysqli_query($link, "SELECT `name` FROM `prop_unit_type` WHERE ".$propriedades['unit_type_id']." = prop_unit_type.id");
												
												//array associativo que recebe os resultados da query
												$recebenomeunidade = mysqli_fetch_assoc($nomeunidade);
												
												//vai buscar o name ao array associativo
												echo $recebenomeunidade['name']."</br>";
											}
										//fecha o caso double								
										break;
										
										//o tipo for enum
										case 'enum':
										
											//vai buscar o nome das propriedades
											echo "<label for=".$propriedades['form_field_name'].">".$propriedades['name'].":</label></br></br>";
											
											//execução da query que retorna o id e o valor depois de comparar os id´s
											$valorespremitidos = mysqli_query($link, "SELECT `id`, `value` FROM `prop_allowed_value` WHERE property_id = ".$propriedades['id']."");
											
											//se o form_field_type for igual a radio
											if($propriedades['form_field_type'] == "radio")
											{
												//inicializa o ciclo while, array associativo que guarda o resultado da query
												while($idvalores = mysqli_fetch_assoc($valorespremitidos))
												{
													//input do tipo radio, o nome é o form_field_name que vem do array associativo
													echo "<input type='radio' name='".$propriedades['form_field_name']."'/> ";
													
													//value como o nome do valor permitido
													echo $idvalores['value']."</br>";
													
													//se a propriedade for diferente de nulo executa a query e mostra o resultado 
													if(!is_null($propriedades['unit_type_id']))
													{
														//variavel que guarda a query que compara o id e retorna o nome
														$nomeunidade = mysqli_query($link, "SELECT `name` FROM `prop_unit_type` WHERE ".$propriedades['unit_type_id']." = prop_unit_type.id");
														
														//array associativo que recebe os resultados da query
														$recebenomeunidade = mysqli_fetch_assoc($nomeunidade);
														
														//vai buscar o name ao array associativo
														echo $recebenomeunidade['name']."</br>";					
													}
												}
												echo "</br>";	
											}
											
											//se o form_field_type for igual a checkbox
											else if($propriedades['form_field_type'] == "checkbox")
											{
												//inicializa o ciclo while, array associativo que guarda o resultado da query
												while($idvalores = mysqli_fetch_assoc($valorespremitidos))
												{
													//input do tipo checkbox com o nome que vem do array associativo
													echo "<input type='checkbox' name='".$propriedades['form_field_name']."'/> ";
													
													//busca o value que vem do array associativo que guarda a query
													echo $idvalores["value"]."</br>";
													
													//se o array associativo for diferente de nulo
													if(!is_null($propriedades['unit_type_id']))
													{
														//variavel que guarda a query que compara o id e retorna o nome
														$nomeunidade = mysqli_query($link, "SELECT `name` FROM `prop_unit_type` WHERE ".$propriedades['unit_type_id']." = prop_unit_type.id");
														
														//array associativo que recebe os resultados da query
														$recebenomeunidade = mysqli_fetch_assoc($nomeunidade);
														
														//vai buscar o name ao array associativo
														echo $recebenomeunidade['name']."</br>";					
													}		
												}	
												echo "</br>";
											}
											
											//se o form_field_type for igual a selectbox
											else if($propriedades['form_field_type'] == "selectbox")
											{
												//declaração de uma lista que recebe opções, com o name que vem do array associativo
												echo '<select name="'.$propriedades['form_field_name'].'">';
												
												//array associativo que guarda o resultado que vem da query que retorna o id e o valor depois de comparar os id´s
												while($idvalores = mysqli_fetch_assoc($valorespremitidos))
												{
													//opções sao geradas dinamicamente com o que vem da query que esta guardada no array associativo
													echo '<option>'.$idvalores['value'].'</option>' ;
																						
												}
												echo '</select>';
												
												//se o array associativo do diferente de nulo
												if(!is_null($propriedades['unit_type_id']))
												{
													//variavel que guarda a query que compara o id e retorna o nome
													$nomeunidade = mysqli_query($link, "SELECT `name` FROM `prop_unit_type` WHERE ".$propriedades['unit_type_id']." = prop_unit_type.id");
													
													//array associativo que recebe os resultados da query
													$recebenomeunidade = mysqli_fetch_assoc($nomeunidade);
													
													//vai buscar o name ao array associativo
													echo $recebenomeunidade['name']."</br>";
							
												}		
												echo "</br>";
											}
											
										//fecho do caso enum
										break;
										
										//tipo for comp_ref
										case 'comp_ref':
										
											//definição de um rotulo para o elemento select, o nome vem do array associativo 
											echo "<label for=".$propriedades['form_field_name'].">".$propriedades['name'].":</label></br>";
											
											//inicialização da lista select que tem como nome o que no array associativo
											echo '<select name="'.$propriedades['form_field_name'].'">';
											
											//vai buscar todos as referencias a componentes que tem como chave estrangeira uma referenca a outra componente
											$selecionaFK = mysqli_query($link, "SELECT `comp_fk_id` FROM `property` WHERE ".$_SESSION["comp_id"]." = component_id AND value_type = 'comp_ref'");
											
											//array associativo que guarda o resultado da query executada acima
											while($FK = mysqli_fetch_assoc($selecionaFK))
											{
												// vai buscar o id e o nome da instancia do componente que tem uma referencia de outro compoenente
												$selecionainstancia = mysqli_query($link, "SELECT `id`, `component_id`, `component_name` FROM `comp_inst` WHERE component_id = ".$FK['comp_fk_id']."");
												
												//array associativo que guarda o resultado que vem da query 
												while($nomeinstancia = mysqli_fetch_assoc($selecionainstancia))
												{
													//criação das opções dinamicas que recebm o nome do componente que vem do array associativo
													echo '<option>'.$nomeinstancia['component_name'].'</option>';									
												}
											}
											echo "</select></br>";
										
										//fecho do caso comp_ref
										break;
										
										//parar o ciclo while
										default:break;
									}												
								}
								
								//label e input para introduzir nova instancia do componente
								echo "<label for='nome'>nome para instância do componente:</label></br><input type='text' name = 'instancia'/></br>";
								echo "<input type='hidden' value='validar'/></br>";
								
								//botao para submeter os dados
								echo "<button type='submit'>Submeter</button>";
								
								//fecho do formulario dinamico
								echo "</form>";
							}
							
							//caso contrário o componente selecionado é um formulário
							else
							{
								//variável de sessão que guarda o form que vem do array associativo REQUEST,
								$_SESSION["form_id"] = $_REQUEST["form"];
								
								//execução da query associada ao id do nome que esta na variavel de sessao form_id
								$buscaid = mysqli_query($link, "SELECT `name` FROM `custom_form` WHERE id = ".$_SESSION["form_id"].""); 
								
								//a variavel de sessao guarda o array associativo que vai buscar o nome do formulário atraves do seu id que vem da query que esta guardada na variavel $buscaid
								$_SESSION["form_name"] = mysqli_fetch_assoc($buscaid)['name'];					
								
								//apresentacao do sub titulo e concatenação da variavel de sessao que guarda o nome do formulário
								echo "<h3>Inserção de valores - ".$_SESSION["form_name"]."</h3>";
								
								//inicialização do formulario dinamico, 
								echo "<form method = 'POST' name='form_".$_SESSION["form_id"]."' action='?estado=validar&form=".$_SESSION["form_id"]."' >";
								
								//execução da query que compara os id´s, o que vem na variável de sessão com o que esta na tabela custom_form_has_property
								$idpropriedadesformulario = mysqli_query($link, "SELECT `custom_form_id`, `property_id`, `field_order` FROM `custom_form_has_property` WHERE ".$_SESSION["form_id"]." = custom_form_id ");
								
								//retorna a ultima descrição do erro
								echo mysqli_error($link);
								
								//inicialização do ciclo while, array associativo que guarda o resultado da query
								while($idpropform = mysqli_fetch_assoc($idpropriedadesformulario))
								{
									//execução da query que compara os id´s das propriedades
									$propriedadesformulario = mysqli_query($link, "SELECT `id`, `name`, `component_id`, `value_type`, `form_field_name`, `form_field_type`, `unit_type_id`, `form_field_size`, `form_field_order`, `mandatory`, `state`, `comp_fk_id` FROM `property` WHERE state = 'active' AND id = ".$idpropform['property_id'] ."");
									
									//array associativo que guarda o resultado da query e como esta num ciclo while imprime linha a linha
									while( $pf = mysqli_fetch_assoc($propriedadesformulario))
									{
										//variavel que guarda o array associativo que recebe o value_type que vem da query
										$tipovalor = $pf['value_type'];
										
										//inicialização do switch que recebe como parametros a variavel declarada acima
										switch ($tipovalor) 
										{
											//tipo for text
											case 'text':
											
												//se o form_field_name que esta no array associativo for igual a text
												if($pf['form_field_type'] == "text")
												{
													//para por os nomes antes das caixas de texto para os utilizadores saberem o que têm de introduzir, vou buscar o name ao array que guarda a query
													echo "<label for=".$pf['name'].">".$pf['name'].":</label></br><input type='text' name='".$pf['form_field_name']."'/></br>";
													
													////retorna o id do tipo de unidade que esta no array associativo
													echo $pf['unit_type_id'];
												}
												
												//caso contrário, se for igual a textbox
												else if($pf['form_field_type'] == "textbox")
												{
													//poe os nomes antes das caixas de texto para os utilizadores saberem o que têm de introduzir, vai buscar o name ao array $pf que guarda o resultado da query
													//o nome do atributo da label é igual ao value para poderem conectar-se
													echo "<label for=".$pf['name'].">".$pf['name'].":</label></br><input type='textbox' name='".$pf['form_field_name']."' />";
													
													//se a propriedade for diferente de nulo executa a query e mostra o resultado
													if(!is_null($pf['unit_type_id']))
													{
														//variavel que guarda a query que compara o id e retorna o nome
														$nomeunidade = mysqli_query($link, "SELECT `name` FROM `prop_unit_type` WHERE ".$pf['unit_type_id']." = prop_unit_type.id");
														
														//array associativo que recebe os resultados da query
														$recebenomeunidade = mysqli_fetch_assoc($nomeunidade);
														
														//vai buscar o name ao array associativo
														echo $recebenomeunidade['name']."</br>";
													}								
												}
											//fecho do caso text	
											break;
											
											//tipo for bool
											case 'bool':
											
												//rotulo para colocar antes do input para o utilizador saber o que vai introduzir
												echo "<label for=".$pf['name'].">".$pf['name'].":</label></br>";
												
												//retorna um input do tipo radio com o value True
												echo "<input type='radio' value = 'true' name='".$pf['form_field_name']."'/> Sim </br>";
												
												//retorna um input do tipo radio com o value false
												echo "<input type='radio' value = 'false' name='".$pf['form_field_name']."'/> Não </br>";							
												
												//fecho do caso bool
												break;
											
											//tipo for int
											case 'int':
											
												//poe os nomes antes das caixas de texto para os utilizadores saberem o que têm de introduzir, vai buscar o name ao array $pf que guarda o resultado da query
												//o nome do atributo da label é igual ao value para poderem conectar-se
												echo "<label for=".$pf['name'].">".$pf['name'].":</label></br>
												<input type='text' name='".$pf['form_field_name']."'/>";
												
												//se a propriedade for diferente de nulo executa a query e mostra o resultado
												if(!is_null($pf['unit_type_id']))
												{
														//variavel que guarda a query que compara o id e retorna o nome
														$nomeunidade = mysqli_query($link, "SELECT `name` FROM `prop_unit_type` WHERE ".$pf['unit_type_id']." = prop_unit_type.id");
														
														//array associativo que recebe os resultados da query
														$recebenomeunidade = mysqli_fetch_assoc($nomeunidade);
														
														//vai buscar o name ao array associativo
														echo $recebenomeunidade['name']."</br>";
												}	
												
											//fecho do caso int
											break;
											
											//tipo for double
											case 'double':
											
												echo "<label for=".$pf['name'].">".$pf['name'].":</label></br><input type='text' name='".$pf['form_field_name']."'/>";
												
												//se a propriedade for diferente de nulo executa a query e mostra o resultado
												if(!is_null($pf['unit_type_id']))
												{
													//variavel que guarda a query que compara o id e retorna o nome
													$nomeunidade = mysqli_query($link, "SELECT `name` FROM `prop_unit_type` WHERE ".$pf['unit_type_id']." = prop_unit_type.id");
													
													//array associativo que recebe os resultados da query
													$recebenomeunidade = mysqli_fetch_assoc($nomeunidade);
													
													//vai buscar o name ao array associativo
													echo $recebenomeunidade['name']."</br>";
												}	
		
											//fecho do caso double
											break;
											
											//tipo for enum
											case 'enum':
											
												//vai buscar o nome das propriedades
												echo "<label for=".$pf['name'].">".$pf['name'].":</label></br></br>";
												
												//execução da query que retorna o id e o valor depois de comparar os id´s
												$valorespremitidos = mysqli_query($link, "SELECT `id`, `value` FROM `prop_allowed_value` WHERE property_id = ".$pf['id']."");
												
												//se o form_field_name que esta no array $pf for igual a radio
												if($pf['form_field_type'] == "radio")
												{
													//array associativo que guarda o resultado da query
													while($idvalores = mysqli_fetch_assoc($valorespremitidos))
													{
														//input do tipo radio que recebe o form_field_name que vem do array $pf e o value que vem do array $idvalores
														echo "<input type='radio' name='".$pf['form_field_name']."' value='".$idvalores['value']."'/> ";
														
														//value como o nome do valor permitido
														echo $idvalores['value']."</br>";
														
														//se a propriedade for diferente de nulo executa a query e mostra o resultado 
														if(!is_null($pf['unit_type_id']))
														{
															//variavel que guarda a query que compara o id e retorna o nome
															$nomeunidade = mysqli_query($link, "SELECT `name` FROM `prop_unit_type` WHERE ".$pf['unit_type_id']." = prop_unit_type.id");
															
															//array associativo que recebe os resultados da query
															$recebenomeunidade = mysqli_fetch_assoc($nomeunidade);
															
															//vai buscar o name ao array associativo
															echo $recebenomeunidade['name']."</br>";
									
														}
													}
													echo "</br>";	
												}
												
												//se o form_field_name que esta no array $pf for igual a checkbox
												else if($pf['form_field_type'] == "checkbox")
												{											
													while($idvalores = mysqli_fetch_assoc($valorespremitidos))
													{
														echo "<input type='checkbox' name='".$pf['form_field_name']."'/> ";
														
														echo $idvalores["value"]."</br>";
														
														if(!is_null($pf['unit_type_id']))
														{
															//variavel que guarda a query que compara o id e retorna o nome
															$nomeunidade = mysqli_query($link, "SELECT `name` FROM `prop_unit_type` WHERE ".$pf['unit_type_id']." = prop_unit_type.id");
															
															//array associativo que recebe os resultados da query
															$recebenomeunidade = mysqli_fetch_assoc($nomeunidade);
															
															//vai buscar o name ao array associativo
															echo $recebenomeunidade['name']."</br>";
														}		
													}	
													echo "</br>";
												}
												
												//se o form_field_name que esta no array $pf for igual a selectbox
												else if($pf['form_field_type'] == "selectbox")
												{
													//inicio da lista dinamica que recebe como nome o form_field_name que vem do array
													echo '<select name="'.$pf['form_field_name'].'">';
													
													while($idvalores = mysqli_fetch_assoc($valorespremitidos))
													{
														//opções dinamicas da lista que recebe o value que vem no array associativo
														echo '<option>'.$idvalores['value'].'</option>' ;
																							
													}
													echo '</select>';
													
													if(!is_null($pf['unit_type_id']))
													{
														//variavel que guarda a query que compara o id e retorna o nome
														$nomeunidade = mysqli_query($link, "SELECT `name` FROM `prop_unit_type` WHERE ".$pf['unit_type_id']." = prop_unit_type.id");
														
														//array associativo que recebe os resultados da query
														$recebenomeunidade = mysqli_fetch_assoc($nomeunidade);
														
														//vai buscar o name ao array associativo
														echo $recebenomeunidade['name']."</br>";
								
													}		
													echo "</br>";
												}
												
											//fecho do caso enum
											break;
											
											//tipo for comp_ref
											case 'comp_ref':
											
												echo "<label for='".$pf['form_field_name']."'>'".$pf['form_field_name']."':</label></br>";
												
												echo '<select name="'.$pf['name'].'">';
												
												//vai buscar todos as referencias a componentes que tem como chave estrangeira uma referenca a outra componente
												$selecionaFK = mysqli_query($link, "SELECT `comp_fk_id` FROM `property` WHERE ".$_SESSION["comp_id"]." = component_id");
												
												while($FK = mysqli_fetch_assoc($selecionaFK))
												{
													// vai buscar o id e o nome da instancia do componente que tem uma referencia de outro compoenente
													$selecionainstancia = mysqli_query($link, "SELECT `id`, `component_id`, `component_name` FROM `comp_inst` WHERE component_id = ".$FK['comp_fk_id']."");
																								 
													while($nomeinstancia = mysqli_fetch_assoc($selecionainstancia))
													{
														echo '<option>'.$nomeinstancia['component_name'].'</option>';									
													}
												}
												echo "</select></br>";
											
											//fecho do caso comp_ref
											break;
											
											//para o ciclo while
											default:break;
										}								
									}
								}
								echo "<label for='nome'>nome para instância do componente:</label></br><input type='text' name = 'instancia'/></br>";
								echo "<input type='hidden' value='validar'/></br>";
								echo "<button type='submit'>Submeter</button>";
								echo "</form>";
								
							}
				
						}
						
						//se o estado de execução for igual a validar
						else if($_REQUEST["estado"] == "validar")
						{
							//para verificar se os campos que sao obrigatorios estao todos preenchidos
							$obrigatoriofalta = false;
							
							//variavel goBack para voltar atras é iniciada a false
							$goBack = false;
							
							//se for vazio entao é uma componente
							if(empty($_REQUEST['form']))
							{
								//validações para componentes
								echo "<h3>Inserção de valores - ".$_SESSION["comp_name"]." - validar </h3>";
								
								//validar os dados do tipo double
								//executa a query que verifica se os tipos de valores sao double, compara os id´s e imprime toda a informação sobre uma propriedade
								$executavalidacao = mysqli_query($link,"SELECT * FROM property WHERE value_type = 'double' AND component_id = ".$_SESSION['comp_id']."");
								
								//array associativo guarda o resultado da query
								while($tipoDouble =mysqli_fetch_assoc($executavalidacao))
								{
									//verifica se o array REQUEST tem alguma coisa associada á chave que faz parte do array $tipoDouble
									//que pode ser acedida pelo form_field_name
									if(isset($_REQUEST[$tipoDouble['form_field_name']]))
									{
										// declaração da variável que guarda a função que converte os caracteres em strings
										$convDouble = mysqli_real_escape_string($link,$_REQUEST[$tipoDouble['form_field_name']]);
										
										//se a variavel for um número
										if(is_numeric($convDouble))
										{
											//converte a variavel no valor float
											$convDouble = floatval($convDouble);
											
											//quando o request tem um double e trata o double,actualiza esse valor com esse valor tratado
											$_REQUEST[$tipoDouble['form_field_name']] = $convDouble;
											
										}
										else
										{
											//passa a mensagem caso o utilizador nao tenha introduzido um dado double e vai buscar o nome ao array
											echo 'Certifique-se que introduziu um valor numérico no campo '.$tipoDouble['name'];
											
											//variável goBack passa a true
											$goBack = true;
										}
									}
								}
								
								//validar os dados do tipo int
								//execução da query que verifica se  se os tipos de valores sao int, compara os id´s e imprime toda a informação sobre uma propriedade
								$executavalidacao = mysqli_query($link,"SELECT * FROM property WHERE value_type = 'int' AND component_id = ".$_SESSION['comp_id']."");
								
								//guarda a query num array associativo
								while($tipoInt =mysqli_fetch_assoc($executavalidacao))
								{
									//verifica se o array REQUEST tem alguma coisa associada á chave que faz parte do array $tipoDouble
									//que pode ser acedida pelo form_field_name
									if(isset($_REQUEST[$tipoInt['form_field_name']]))
									{
										// declaração da variável que guarda a função que converte os caracteres em strings
										$convInt = mysqli_real_escape_string($link,$_REQUEST[$tipoInt['form_field_name']]);
										
										//verifica se são caracteres numéricos
										if(ctype_digit($convInt))
										{
											$convInt = (int)$convInt;
											
											//quando o request tem um int e trata o int,actualiza esse valor com esse valor tratado
											$_REQUEST[$tipoInt['form_field_name']] = $convInt;
											
										}
										else
										{
											echo 'Certifique-se que introduziu um valor numérico no campo '.$tipoInt['name'];
											$goBack = true;
										}
									}
								}
								
								// query seleciona todas as propriedades associadas ao componente cujo o id esta na session
								$propriedadescomponente = mysqli_query($link, "SELECT `id`, `name`, `component_id`, `value_type`, `form_field_name`, `form_field_type`, `unit_type_id`, `form_field_size`, `form_field_order`, `mandatory`, `state`, `comp_fk_id` FROM `property` WHERE state = 'active' AND component_id = ".$_SESSION["comp_id"]."");
								
								//array associativo que guarda o que vem da query, vai carregar linha por linha
								while( $propriedades = mysqli_fetch_assoc($propriedadescomponente))
								{
									//query seleciona para cada propriedade todas as caracteristicas dessa propriedade
									$propriedadesobrigatorias = mysqli_query($link, "SELECT * FROM `property` WHERE form_field_name = '".$propriedades['form_field_name']."' AND component_id = ".$_SESSION["comp_id"]."");
									
									$propO = mysqli_fetch_assoc($propriedadesobrigatorias);
									
									//caso essa propriedade seja obrigatoria e o campo nao tiver sido preenchido apresenta a mensagem de erro
									if($propO['mandatory'] == 1 && empty($_REQUEST[$propriedades['form_field_name']]))
									{
										//mensagem de erro, vai buscar o nome da propriedade
										echo "A propriedade ".$propriedades['name'].", é de preenchimento obrigatório.";
										
										//variavel passa a true
										$obrigatoriofalta = true;
									}
								}
								if($obrigatoriofalta)
								{
									goBack();
								}
								else
								{
									if($goBack == false)
									{
										//inicialização do formulário
										echo "<form method='POST' action='?estado=inserir&comp=".$_SESSION["comp_id"]."'>";
										
										echo "Estamos prestes a inserir os dados abaixo na base de dados. Confirma que os dados estão correctos e pretende submeter os mesmos?";
										
										//os dados aparecem em forma de lista
										echo "<ul>";
										echo "<li>".$_SESSION["comp_name"]."</li>";
										echo "<ul>";
										
										// query seleciona todas as propriedades associadas ao componente cujo o id esta na session
										$propriedadescomponente = mysqli_query($link, "SELECT `id`, `name`, `component_id`, `value_type`, `form_field_name`, `form_field_type`, `unit_type_id`, `form_field_size`, `form_field_order`, `mandatory`, `state`, `comp_fk_id` FROM `property` WHERE state = 'active' AND component_id = ".$_SESSION["comp_id"]."");
										
										//array associativo que guarda o que vem da query, vai carregar linha por linha
										while( $propriedades = mysqli_fetch_assoc($propriedadescomponente))
										{
											echo "<li>";
											
											//imprime o valor que o utilizador introduzio no formulario anterior para cada propriedade
											echo "'".$propriedades['name']."': ".$_REQUEST[$propriedades['form_field_name']];
										
											echo "</li>";
												
											//manda para o estado seguinte as propriedades e os valores introduzidos
											echo "<input type='hidden' name='".$propriedades['form_field_name']."' value='".$_REQUEST[$propriedades['form_field_name']]."'/>";
										}
										
										//se for diferente de vazio 
										if(!empty($_REQUEST['instancia']))
										{
											echo "<li>";
											
											//nome da instancia é a intancia que vem no REQUEST
											echo "nome para instância do componente: ".$_REQUEST['instancia']."</li>";
											
											//manda para o estado seguinte os valores das instancias
											echo "<input type='hidden' name='instancia' value='".$_REQUEST['instancia']."'/>";
										}
										else
										{						
											echo "<input type='hidden' name='instancia' value=''/>";
										}
											
										echo "</ul>";
										
										echo "<button type='submit'>Submeter</button>";
									}
									else
									{
										goBack();
									}
									echo "</form>";
								}
							}
							//caso contrario é um formulário
							else
							{
								echo "<h3>Inserção de valores - ".$_SESSION["form_name"]." - validar </h3>";						
								
								//percorre o array REQUEST e extrai cada par chave-valor
								foreach($_REQUEST as $key => $value)
								{
									//validar os dados do tipo double
									//execução da query que verifica se os tipos de valores sao double, compara as chaves e imprime toda a informação sobre uma propriedade
									$executavalidacao = mysqli_query($link,"SELECT * FROM property WHERE value_type = 'double' AND form_field_name = '".$key."'");
									
									while($tipoDouble =mysqli_fetch_assoc($executavalidacao))
									{
										//verifica se o array REQUEST tem alguma coisa associada á chave que faz parte do array $tipoDouble
										//que pode ser acedida pelo form_field_name
										if(isset($_REQUEST[$tipoDouble['form_field_name']]))
										{
											$convDouble = mysqli_real_escape_string($link,$_REQUEST[$tipoDouble['form_field_name']]);
											
											if(is_numeric($convDouble))
											{
												$convDouble = floatval($convDouble);
												
												//quando o request tem um double e trata o double,actualiza esse valor com esse valor tratado
												$_REQUEST[$tipoDouble['form_field_name']] = $convDouble;
											}
											else
											{
												echo 'Certifique-se que introduziu um valor numérico no campo '.$tipoDouble['name'];
												$goBack = true;
											}
										}
									}	   
								}
								//percorre o array REQUEST e extrai cada par chave-valor
								foreach($_REQUEST as $key => $value)
								{
									//validar os dados do tipo int
									//execução da query que verifica se os tipos de valores sao int, compara as chaves e imprime toda a informação sobre uma propriedade
									$executavalidacao = mysqli_query($link,"SELECT * FROM property WHERE value_type = 'int' AND form_field_name = '".$key."'");
									while($tipoInt =mysqli_fetch_assoc($executavalidacao))
									{
										//verifica se o array REQUEST tem alguma coisa associada á chave que faz parte do array $tipoDouble
										//que pode ser acedida pelo form_field_name
										if(isset($_REQUEST[$tipoInt['form_field_name']]))
										{
											$convInt = mysqli_real_escape_string($link,$_REQUEST[$tipoInt['form_field_name']]);
											
											if(ctype_digit($convInt))
											{
												$convInt = (int)$convInt;
												$_REQUEST[$tipoInt['form_field_name']] = $convInt;
											}
											else
											{
												echo 'Certifique-se que introduziu um valor numérico no campo '.$tipoInt['name'];
												$goBack = true;
											}
										}
									}
								}
								//execução da query que compara os id´s
								$idpropriedadesformulario = mysqli_query($link, "SELECT `custom_form_id`, `property_id`, `field_order` FROM `custom_form_has_property` WHERE ".$_SESSION["form_id"]." = custom_form_id ");
								
								echo mysqli_error($link);
								
								while($idpropform = mysqli_fetch_assoc($idpropriedadesformulario))
								{
									// query seleciona todas as propriedades associadas ao formulario cujo o id esta no array
									$propriedadesformulario = mysqli_query($link, "SELECT `id`, `name`, `component_id`, `value_type`, `form_field_name`, `form_field_type`, `unit_type_id`, `form_field_size`, `form_field_order`, `mandatory`, `state`, `comp_fk_id` FROM `property` WHERE state = 'active' AND id = ".$idpropform['property_id'] ."");
									
									while( $pf = mysqli_fetch_assoc($propriedadesformulario))
									{
										//query seleciona para cada propriedade todas as caracteristicas dessa propriedade
										$propriedadesobrigatorias = mysqli_query($link, "SELECT * FROM `property` WHERE name = '".$pf['name']."' AND id = ".$pf["id"]."");
										
										$propO = mysqli_fetch_assoc($propriedadesobrigatorias);
										
										//caso essa propriedade seja obrigatoria e o campo nao tive sido preenchido apresenta a mensagem de erro
										if($propO['mandatory'] == 1 && empty($_REQUEST[$pf['form_field_name']]))
										{
											//mensagem de erro, vai buscar o nome da propriedade
											echo "A propriedade ".$pf['name'].", é de preenchimento obrigatório.";
											$obrigatoriofalta = true;
										}
									
									}
								}
								if($obrigatoriofalta)
								{
									goBack();
								}
								else
								{
									if($goBack == false)
									{
										echo "<form method='POST' action='?estado=inserir&form=".$_SESSION["form_id"]."'>";
										echo "Estamos prestes a inserir os dados abaixo na base de dados. Confirma que os dados estão correctos e pretende submeter os mesmos?";
										echo "<ul>";
										echo "<li>".$_SESSION["form_name"]."</li>";
										echo "<ul>";
										
										$idpropriedadesformulario = mysqli_query($link, "SELECT `custom_form_id`, `property_id`, `field_order` FROM `custom_form_has_property` WHERE ".$_SESSION["form_id"]." = custom_form_id ");
										
										echo mysqli_error($link);
										
										while($idpropform = mysqli_fetch_assoc($idpropriedadesformulario))
										{
											// query seleciona todas as propriedades associadas ao formuario cujo o id esta no array
											$propriedadesformulario = mysqli_query($link, "SELECT `id`, `name`, `component_id`, `value_type`, `form_field_name`, `form_field_type`, `unit_type_id`, `form_field_size`, `form_field_order`, `mandatory`, `state`, `comp_fk_id` FROM `property` WHERE state = 'active' AND id = ".$idpropform['property_id'] ."");
											
											while( $pf = mysqli_fetch_assoc($propriedadesformulario))
											{
												echo "<li>";
												
												//imprime o valor que o utilizador introduzio no formulario anterior para cada propriedade
												echo $pf['name'].": ".$_REQUEST[$pf['form_field_name']];
												
												echo "</li>";
												
												//manda para o estado seguinte as propriedades e os valores introduzidos
												echo "<input type='hidden' name='".$pf['form_field_name']."' value='".$_REQUEST[$pf['form_field_name']]."'/>";
											}
										}
											
										if(!empty($_REQUEST['instancia']))
										{
											echo "<li>";
											
											
											echo "nome para instância do componente: ".$_REQUEST['instancia']."</li>";
											
											//manda para o estado seguinte os valores que vem no array
											echo "<input type='hidden' name='instancia' value='".$_REQUEST['instancia']."'/>";
										}
										else
										{
											echo "<input type='hidden' name='instancia' value=''/>";
										}
												
										echo "</ul>";
										echo "<button type='submit'>Submeter</button>";
										echo "</form>";
									}
								}
							}
						}
						
						//se o estado de execução for inserir
						else if($_REQUEST["estado"] == "inserir")
						{
							//se for um componente
							if(empty($_REQUEST['form']))
							{
								echo "<h3>Inserção de valores - ".$_SESSION["comp_name"]." - inserção </h3>";
							
								//transação, $link é a conexão á base de dados, e o modo retorna falso
								//evitar o comit automatico das querys, gua, evita guardar logo o resultado da query, rda o resultado da query
								mysqli_autocommit($link, false);
								
								//iniciar uma transação
								mysqli_begin_transaction($link);
								
								//insere na tabela comp_inst o id do componente que vem na variavel session e a instancia que vem no array REQUEST
								$insereinstancia = mysqli_query($link, "INSERT INTO `comp_inst`(`id`, `component_id`, `component_name`) VALUES (NULL,".$_SESSION["comp_id"].",'".$_REQUEST['instancia']."')");
								
								//devolve o ultimo id a ser introduzido na base de dados
								$idCompInts = mysqli_insert_id($link);
								
								//caso tenha erros, mostra a mensagem e nao insere nada na base de dados
								if(!$insereinstancia)
								{
									//se tiver erros
									echo mysqli_error($link);
									
									//volta para tras quando a query nao e bem sucedida
									mysqli_rollback($link);
									
									//passa a mensagem de erro e nao insere nada na base de dados
									echo "Erro na criação da instância.";					
									
								}
								else
								{
									// query seleciona todas as propriedades associadas ao componente cujo o id esta na session
									$propriedadescomponente = mysqli_query($link, "SELECT `id`, `name`, `component_id`, `value_type`, `form_field_name`, `form_field_type`, `unit_type_id`, `form_field_size`, `form_field_order`, `mandatory`, `state`, `comp_fk_id` FROM `property` WHERE state = 'active' AND component_id = ".$_SESSION["comp_id"]."");
									
									if(!$propriedadescomponente)
									{
										echo mysqli_error($link);
										
										//volta para tras quando a query nao e bem sucedida
										mysqli_rollback($link);
										
										//passa a mensagem de erro e nao insere nada na base de dados
										echo "Erro na selação da propriedade.";
										
									}
									else
									{
										//declaração de uma variavel booleana com inicialização a false
										$sucesso = false;
										
										//array associativo que guarda o que vem da query, vai carregar linha por linha, query seleciona todas as propriedades associadas ao componente cujo o id esta na session
										while( $propriedades = mysqli_fetch_assoc($propriedadescomponente))
										{
											// insere na tabela value o ultimo id a ser introduzido na base de dados, o id que vem no array associativo, form_field_name que vem no request e aparece a data, a hora da inserção e o utilizador que a fez
											$inserevalue = mysqli_query($link, "INSERT INTO `value`(`id`, `comp_inst_id`, `property_id`, `value`, `date`, `time`, `producer`) VALUES (NULL,".$idCompInts.",".$propriedades['id'].",'".$_REQUEST[$propriedades['form_field_name']]."','".date("Y-m-d")."','".date("H:i:s")."','".wp_get_current_user()->user_login."')");
											
											//se for diferente da query
											if(!$inserevalue)
											{								
												echo mysqli_error($link);
												
												//voltar para tras quando a query nao e bem sucedida
												mysqli_rollback($link);
												
												//mensagem de erro
												echo "Erro na criação da propriedade.";
												
												//variavel booleana passa a falso
												$sucesso = false;
														
											}
											//caso contrario
											else
											{
												//confirma a transação para a base de dados, recebe como parametro a conexao
												mysqli_commit($link);
												
												//variavel booleana passa a true
												$sucesso = true;
											}								
										}
										//se a variavel for igual a true
										if($sucesso == true)
										{
											//imprime a mensagem 
											echo "Inseriu o(s) valor(es) com sucesso.</br>";
											
											//ligação para voltar atras ou escolher outro componente
											echo 'Clique em <a href="/insercao-de-valores">Voltar</a> para voltar ao início da inserção de valores e poder escolher outro componente ou em <a href="?estado=introducao&comp='.$_SESSION["comp_id"].'">Continuar a inserir valores neste componente</a> se quiser continuar a inserir valores';
										}
										
										else
										{
											//caso contrario, passa a mensagem de que ocorreu um erro
											echo"Lamentamos, mas ocorreu um erro.";
											goBack();
										}
									}							
								}
							}
							
							// se for inserção dos formulários
							else
							{
								echo "<h3>Inserção de valores - ".$_SESSION["form_name"]." - inserção </h3>";
							
								//evitar o comit automatico das querys, evita guardar logo o resultado da query, rda o resultado da query
								mysqli_autocommit($link, false);
								
								//iniciar uma transação
								mysqli_begin_transaction($link);
								
								//declaração de uma variável vazia
								$id = 0;
								
								//declaração de uma variavel inicializada a false
								$sucesso = false;
								
								//declaração de um array vazio
								$armazenaid = array();
								
								//percorre o array request de modo a obter todos os pares chave valor
								foreach($_REQUEST as $key => $value)
								{
									//execução da query que compara os id´s das duas tabelas, compara as chaves e seleciona o id do componente
									$selecionacomponente = mysqli_query($link, "SELECT component.id FROM `component`, `property` WHERE component.id = property.component_id AND property.form_field_name = '".$key."'");
									
									//guarda a query num array associativo
									$guardacompselec = mysqli_fetch_assoc($selecionacomponente);
									
									//se o array associativo receber alguma coisa da query, se for diferente de vazio
									if(!empty($guardacompselec))
									{
										//vai buscar o id da componente
										$novoID = $guardacompselec['id'];
										
										//variavel booleana
										$naoexiste = false;
										
										//percorre o array que armazena todos os id´s de componentes de que vou criar instancias
										foreach($armazenaid as $ids)
										{
											// se o novo id for diferente dos que ja existem no array
											if($novoID != $ids)
											{
												//variavel booleana passa a true
												$naoexiste = true;
											}
											
											//caso seja igual, pára de percorrer o array
											else
											{
												//variavel booleana passa a false
												$naoexiste = false;
												
												//para de percorrer o array
												break;
											}
										}
										
										//se o id nao existir no array ou o array estiver vazio, coloco esse id no array e crio uma instancia
										if($naoexiste || empty($armazenaid))
										{
											//colocar o id no array 
											array_push($armazenaid, $novoID);
											
											//o id passa a ser o novo id introduzido no array
											$id = $novoID;
											
											//query para criar nova instancia							
											$insereinstancia = mysqli_query($link, "INSERT INTO `comp_inst`(`id`, `component_id`, `component_name`) VALUES (NULL,".$id.",'".$_REQUEST['instancia']."')");
											
											//devolve o ultimo id a ser introduzido na base de dados
											$idCompInts = mysqli_insert_id($link);
										
											//caso tenha erros, mostra a mensagem e nao insere nada na base de dados
											if(!$insereinstancia)
											{
												echo mysqli_error($link);
												
												//voltar para tras quando a query nao e bem sucedida
												mysqli_rollback($link);
												
												//mostra a mensagem de erro
												echo "Erro na criação da instância.";
											
												
											}
										}
										//caso o id esteja no array, significa que foi criada uma instancia, uso na inserção das propriedades
										else
										{	
											//seleciona o id da componenete ja criada
											$selecionaidinstancia = mysqli_query($link, "SELECT id FROM comp_inst WHERE component_id ='".$novoID."'");
											
											$guardaidinstanciaselecionada = mysqli_fetch_assoc($selecionaidinstancia);
											
											$idCompInts = $guardaidinstanciaselecionada['id'];
										}
										
										//para obter o id da propriedade correspondente ao valor da key do REQUEST que esta a ser analisado
										$selecionaKEY = mysqli_query($link, "SELECT `id` FROM `property` WHERE form_field_name = '".$key."'");
										
										//guarda o resultado da query num array 
										$guardaKEYselecionada = mysqli_fetch_assoc($selecionaKEY);
												
										//aparece a data e a hora da inserção e o utilizador que o fez
										$inserevalue = mysqli_query($link, "INSERT INTO `value`(`id`, `comp_inst_id`, `property_id`, `value`, `date`, `time`, `producer`) VALUES (NULL,".$idCompInts.",".$guardaKEYselecionada['id'].",'".$value."','".date("Y-m-d")."','".date("H:i:s")."','".wp_get_current_user()->user_login."')");
										
										//se tiver erros
										if(!$inserevalue)
										{
											echo mysqli_error($link);
											
											//volta a tras quando a query nao e bem sucedida
											mysqli_rollback($link);
											
											//mostra a mensagem de erro
											echo "Erro na criação da propriedade.";
										}
										else
										{
											//confirma a transação para a base de dados
											mysqli_commit($link);
											$sucesso = true;
										}				
									}
								}
								if($sucesso)
								{
									echo "Inseriu o(s) valor(es) com sucesso.</br>";
									echo 'Clique em <a href="/insercao-de-valores">Voltar</a> para voltar ao início da inserção de valores e poder escolher outro componente ou em <a href="?estado=introducao&form='.$_SESSION["form_id"].'">Continuar a inserir valores neste componente</a> se quiser continuar a inserir valores';
								}
							}					
				
						}
					}
					
				}
			}
			else
			{
				echo"Não tem autorização para aceder a esta página.";
				
			}
		}		
			
		else
		{
			echo"O utilizador não tem sessão iniciada.";
		}
					
			?>
		</body>
	</html>