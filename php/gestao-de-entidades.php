<?php
	require_once("custom/php/common.php");
	
	$bd = new Db_op();
	$entity = new entidade();
	if ( is_user_logged_in() )
	{
        if(current_user_can('manage_entities'))
		{
			if(empty($_REQUEST['estado']))
			{
				//Apresentar tabela
				$res_EntType = $bd->runQuery("SELECT * FROM ent_type");
				//verifica se há ou não entidades
				if($res_EntType->num_rows > 0)
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
					{	//print_r($read_EntType);
						//printa a restante tabela
?>						
						<tr>
							<td><?php echo $read_EntType['id']; ?></td>
							<td><?php echo $read_EntType['name']?></td>
							<td><?php echo $read_EntType['state']?></td>
							<td>
							<a href="insercao-de-valores?estado=editar&ent_id=<?php echo $read_EntType['id'];?>">[Editar]</a>  
<?php 						if($read_EntType['state'] === 'active')
							{
?>
								<a href="gestao-de-entidades?estado=desativar&ent_id=<?php echo $read_EntType['id'];?>">[Desativar]</a>
<?php			 			}
							else
							{
?>
								<a href="gestao-de-entidades?estado=ativar&ent_id=<?php echo $read_EntType['id'];?>">[Ativar]</a>
<?php 						}
?>
							</td>
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
				
			
			$entity->form($bd); // object lead the method to print the form 
 			}
 			else if($_REQUEST['estado'] =='editar')
 			{
 				
 			}
 			else if($_REQUEST['estado'] == 'ativar')
 			{
				$res_EntTypeA = $bd->runQuery("SELECT name FROM ent_type WHERE id = ".$_REQUEST['ent_id']);
				$read_EntTypeA = $res_EntTypeA->fetch_assoc();
				$bd->runQuery("UPDATE ent_type SET state='active' WHERE id =".$_REQUEST['ent_id']);
?>
				<html>
 					<p>A entidade <?php echo $read_EntTypeA['name'] ?> foi ativada</p>;
 					<p>Clique em <a href="/gestao-de-entidades"/>Continuar</a> para avançar</p>;
				</html>
<?php 		
 			}
 			else if($_REQUEST['estado'] == 'desativar')
 			{
 				$res_EntTypeD = $bd->runQuery("SELECT name FROM ent_type WHERE id = ".$_REQUEST['ent_id']);
 				$read_EntTypeD = $res_EntTypeD->fetch_assoc();
 				$bd->runQuery("UPDATE ent_type SET state='inactive' WHERE id =".$_REQUEST['ent_id']);
?>			
				<p>A entidade <?php echo $read_EntTypeD['name'] ?>  foi desativada</p>;
 				<p>Clique em <a href="/gestao-de-entidades"/>Continuar</a> para avançar</p>;
<?php 
 			}
			else if($_REQUEST['estado'] == 'inserir')
			{
				
				if($entity->ssvalidation()) //serverside validations
				{
					$sanitizeName = $bd->userInputVal($_REQUEST['nome']);
					$res_checkRep = $bd->runQuery("SELECT * FROM ent_type WHERE name like '".$sanitizeName."'");
					if($res_checkRep->num_rows)
					{
?>
						<html><p>Já existe uma entidade do tipo que está a criar.</p></html>
<?php 
						goBack();
					}
					else 
					{
						$queryInsert = "INSERT INTO `ent_type`(`id`, `name`, `state`) VALUES (NULL,'".$sanitizeName."','".$_REQUEST['atv_int']."')";
						$res_querState = $bd->runQuery($queryInsert);
						
						echo 'Inseriu os dados de novo componente com sucesso.';
						echo 'Clique em <a href="/gestao-de-entidades"/>Continuar</a> para avançar';
					}
					
				}
				else
				{
					goBack();
				}
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
			//print_r($stateEnumValues);
			
			foreach($stateEnumValues as $value)
			{
				if($value == 'active')
				{
?>				
					<html>
						<input type="radio" name="atv_int" value="active" required>Ativo
						<br>
					</html>
<?php 	
				}
				else 
				{
?>
					<html>
						<input type="radio" name="atv_int" value="inctive" required>Desativo
						<br>
					</html>
<?php 				
				}
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
		echo '<h3>Gestão de componentes - inserção</h3>';
		if(empty($_REQUEST['nome']))
		{
?>
			<html><p>O campo nome é de preenchimento obrigatório.</p></html>
<?php 
			return false;
		}
		elseif(empty($_REQUEST['atv_int']))
		{
?>
			<html><p>Deve escolhe uma das opções do campo estado.</p></html>
<?php 	
			return false;
		}
		else
		{
				return true;
		}
	}
	
} 
?>
