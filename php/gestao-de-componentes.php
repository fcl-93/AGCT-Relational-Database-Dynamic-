<?php
require_once("custom/php/common.php");
$link = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);

 if ( is_user_logged_in() ) 
 {
		if(current_user_can('manage_components'))
		{
			$query = 'SELECT * FROM component';
			$result = mysqli_query($link,$query);
			//Verificar se o post vem com estado
			if(empty($_REQUEST['estado']))
			{
				if($result)
				{     //Query ok
				  if(mysqli_num_rows($result) > 0)
				  {
	?>
				 <html>
					<table>
					<tbody>
					<tr>
							<td>tipo</td>
							<td>id</td>
							<td>nome</td>
							<td>ativo</td>
							<td>acção</td>
						  </tr>
	<?php
					//Getting data from comp_type table
					$querynumTipos = 'SELECT DISTINCT name FROM comp_type';
					$resultnumTipos = mysqli_query($link, $querynumTipos);


					while ($assArrTipos = mysqli_fetch_assoc($resultnumTipos)) 
					{ 
						$readedValue = $assArrTipos["name"];  
						
						$querynumComp = 'SELECT c.id, c.name, c.state
										 FROM component as c, comp_type as ct 
										 WHERE c.comp_type_id = ct.id and \''.$readedValue.'\' like ct.name ORDER BY c.name ASC ';
						
						$resultnumComp = mysqli_query($link,$querynumComp);
						//Check if querie has errors
						if(!$resultnumComp)
						{echo mysqli_error($link);}
						//Number os components por row
						$numComp = mysqli_num_rows($resultnumComp);

						echo '<tr>';
						echo '<td colspan="1" rowspan="'.$numComp.'">'.$readedValue.'</td>';

						while($assArrComp = mysqli_fetch_assoc($resultnumComp))
						{ 

								echo'<td>'.$assArrComp["id"].'</td>';
								echo'<td>'.$assArrComp["name"].'</td>';
								echo'<td>'.$assArrComp["state"].'</td>';
								echo'<td>[editar] [desativar]</td>';
							echo'</tr>';
						}
					}
	?>
		 </tbody>
		</table>
<?php 
				  }
				  else
				  {
					  echo 'Não há componentes';
				  }
	   ?>
	   <h3>Gestão de componentes - introdução</h3>
		<form>
			<label>Nome:</label><br>
			<input type="text" name="nome" required>
			<br>
			
			<br>
			<label>Tipo:</label>
			<br>
	<?php
			$queryTypes = 'SELECT DISTINCT * FROM comp_type';
			$queryGetTypes = mysqli_query($link, $queryTypes);
			while ($assArrTipos = mysqli_fetch_assoc($queryGetTypes)) 
			{
				echo '<input type="radio"  name="tipo" value="'.$assArrTipos['id'].'" required >'.$assArrTipos['name'] ;
				echo '<br>';
			}
	?>
			<br>
			<label>Estado:</label><br>
	<?php
			$table = 'component';
			$field = 'state';
			$enumTipos = getEnumValues($table, $field, $link);
			foreach($enumTipos as $value)
			{
				echo '<input type="radio"  name="atv_int" value="'.$value.'" required>'.$value;
				echo '<br>';
			}
	?>        
			<br>
			<input type="hidden" name="estado" value="inserir">
			<input type="submit" value="Inserir Componente">
		</form>
	</html>
	<?php
			   
				}
				else
				{//Query retorna falso
				die("Error description: " . mysqli_error());
				}
			}
			else
			{
				mysqli_autocommit($link,false);
				if($_REQUEST['estado'] == 'inserir') //strcmp 
				{
					 echo '<h3>Gestão de componentes - inserção</h3>';
					 if(empty($_REQUEST['nome']))
					 {
							echo 'O campo nome é de preenchimento obrigatório.';
							goBarck();
					 }
					 elseif(empty($_REQUEST['tipo']))
					 {
							echo 'Deve escolhe uma das opções do campo tipo.';
							goBarck();
					 }
					 elseif(empty($_REQUEST['atv_int']))
					 {
							echo 'Deve escolhe uma das opções do campo estado.';
							goBarck();
					 }
					 else
					 {
							$nomeSanitize = mysqli_real_escape_string($link,$_REQUEST['nome']);
							$queryInserer = 'INSERT INTO `component`(`id`, `name`, `comp_type_id`, `state`)
							VALUES (NULL
									  ,\''.$nomeSanitize.'\'
									  ,\''.$_REQUEST['tipo'].'\'
									  ,\''.$_REQUEST['atv_int'].'\'
								   )';
						   
							$result = mysqli_query($link,$queryInserer);
							if(!$result)
							{
								   echo "Erro na query" . mysqli_error($link);
								   mysqli_rollback($link);
							}
							else
							{
							   echo 'Inseriu os dados de novo componente com sucesso.';
							   echo 'Clique em <a href="/gestao-de-componentes"/>Continuar</a> para avançar';
							   mysqli_commit($link);
							}  
					 }
					 
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
?>
