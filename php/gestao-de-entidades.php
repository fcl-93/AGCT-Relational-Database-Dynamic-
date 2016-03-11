<?php
	require_once("custom/php/common.php");
	
	$bd = new Db_op();
	
	if ( is_user_logged_in() )
	{
        if(current_user_can('manage_entities'))
		{
			if(empty($_REQUEST['estado']))
			{
				//Apresentar tabela
				$res_EntType = $bd->runQuery("SELECT * FROM ent_type");
				//verifica se hÁ ou nÃo entidades
				if(!$res_EntType)
				{
?>
				<html>
					<table>
						<tbody>
							<tr>
								<td> ID</td>
								<td> Nome</td>
								<td> Estado</td>
								<td> Ação</td>
							</tr>
<?php				
				
					while($read_EntType = $res_EntType->fetch_assoc())
					{
						//printa a restante tabela
?>						
						<tr>
							<td><?php $read_EntType['id']; ?></td>
							<td><?php $read_EntType['name']?></td>
							<td><?php $read_EntType['state']?></td>
							<td>[editar][desativar]</td>
						</tr>
<?php 
					}	
?>
							</tbody>
						</table>
					</html>
							
<?php 			
				}
				else
				{
?>
					<html>
						<p> Não há componentes.</p>
					</html>
<?php 			}
				
			$entity = new entidade();
			$entity->form($bd);
 		}
			else if($_REQUEST['estado'] == 'inserir')
			{
				//Validações php
			}
			
		}
		else
		{
?>
			<html>
				<p> Não tem autorização para aceder a esta página.</p>
			</html>
<?php 
		}		
	}
	else
	{
	?>
		<html>
			<p> O utilizador não se encontra logado.</p>
		</html>
	<?php
	}
	
 ?>

<?php

class entidade
{
	//This method will be responsable for the print of the form
	//this method will receive a Dp_OpObject (instance from the class in common)
	public function form($Dp_OpObject)
	{
?>
		<html>
			<h3>Gestão de Componentes - Introdução</h3>
			<form>
				<label>Nome:</label>
				<br>
				<input type="text" name="nome" required>
				<br>
				<label>Estado:</label><br>
<?php 
			$stateEnumValues = $Dp_OpObject->getEnumValues('ent_type','state'); //this function is in common.php
			print_r($stateEnumValues);
			
			foreach($stateEnumValues as $value)
			{
?>
				<html>
					<input type="radio" name="atv_int" value="<?php $value ?>" required><?php $value?>
				</html>
<?php 
			}
?>
				<br>
				<input type="hidden" name="estado" value="inserir">
				<input type="submit" value="Inserir Componente">
				</form>
				</html>
<?php 	
	}
	//This method will do the server side validation
	public function ssvalidation()
	{
	
	}
	
} 
?>
