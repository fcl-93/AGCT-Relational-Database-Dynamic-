<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8"/>
	<title>Gestão de Unidades</title>
	<!--<link href="css/styles.css" type="text/css" rel="stylesheet" />-->
	
</head>
<body>
	<?php
	require_once("custom/php/common.php");
	
	$capability='manage_unit_types';
	
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
					//EXEMPLO E VERIFICAÇÃO de uma query
			$result = mysqli_query($link, "SELECT * FROM prop_unit_type ORDER BY name ASC");
			if ($result) 
			{
				/* determine number of rows result set */
				$row_cnt = mysqli_num_rows($result);
				if($row_cnt == 0)
				{
					echo 'Não há tipos de unidades.';
					//1ºtitulo 
						echo "<h3>Gestão de unidades - introdução</h3>";
						//inicialização do formulário
						echo '<form  method="post">
						<label>Inserir nova unidade:</label> <input type="text" name="nome"/>
						<input type ="hidden" name ="estado" value ="inserir"/>
						<input type="submit" name="submit" value ="Inserir tipo de unidade"/>
						</form>';
				}
				else
				{
					//se o campo for hidden, ou seja, se estiver vazio, mostra a tabela e o formulário
					if(empty($_REQUEST["estado"]))
					{
						// construção da tabela
						$result = mysqli_query($link, "SELECT * FROM prop_unit_type ORDER BY name ASC");
						echo "<table border='1'>
						<tr>
						<th>id</th>
						<th>unidade</th>
						</tr>";
						
						while($row = mysqli_fetch_array($result)) 
						{
							echo "<tr>";
							echo "<td>" . $row['id'] . "</td>";
							//name é o nome que está na base de dados
							echo "<td>" . $row['name'] . "</td>";
							echo "</tr>";
						}
						
						//fechar a tabela
						echo "</table>";
						
						
						//1ºtitulo 
						echo "<h3>Gestão de unidades - introdução</h3>";
						//inicialização do formulário
						echo '<form  method="post">
						<label>Inserir nova unidade:</label> <input type="text" name="nome"/>
						<input type ="hidden" name ="estado" value ="inserir"/>
						<input type="submit" name="submit" value ="Inserir tipo de unidade"/>
						</form>';
					}
					
					else if($_REQUEST["estado"] == "inserir")
					{
						$result = mysqli_query($link, "INSERT INTO `prop_unit_type`(`id`, `name`) VALUES (null,'".mysqli_real_escape_string($link,$_REQUEST['nome'])."')");  
						
						if(!$result)
						{
							echo 'Erro ao inserir os dados na tabela.';
						}
						else
						{
							echo "<h3>Gestão de unidades - introdução</h3>";
							echo "Inseriu os dados de novo tipo de unidade com sucesso. </br>";
							echo "Clique em <a href='/gestao-de-unidades/'> Continuar </a> para avançar.";						
						}
					}		
				}
			}
			
			else
			{
				echo mysqli_error($link);
			} 
		}
		else
		{
			echo 'Não tem autorização para a aceder a esta página.';
		}
	}
	else
	{
		echo 'Não tem sessão iniciada.';
	}
	?>		
</body>
</html>