<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<script type="text/javascript" src="js/jquery-1.12.1.js"></script>
		<script type="text/javascript" src="js/jquery.validate.js"></script>
		<script type="text/javascript" src="js/enTypeFormValid.js"></script>
	</head>
</html>
<?php
	require_once("custom/php/common.php");	
	$bd = new Db_op();
	$entity = new Entidade();
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
							
							
<?php 						if($read_EntType['state'] === 'active')
							{
								
?>								
								<td> Ativo </td>
								<td>
									<a href="gestao-de-entidades?estado=editar&ent_id=<?php echo $read_EntType['id'];?>">[Editar]</a>  
									<a href="gestao-de-entidades?estado=desativar&ent_id=<?php echo $read_EntType['id'];?>">[Desativar]</a>
								</td>
<?php			 				
							}
							else
							{
?>
								<td> Inativo </td>
								<td>
									<a href="gestao-de-entidades?estado=editar&ent_id=<?php echo $read_EntType['id'];?>">[Editar]</a>  
									<a href="gestao-de-entidades?estado=ativar&ent_id=<?php echo $read_EntType['id'];?>">[Ativar]</a>
								</td>	
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
 				$entity->editEntity($_REQUEST['ent_id'],$bd);
 			}
 			else if($_REQUEST['estado'] == 'ativar')
 			{
				$entity->enableEnt($bd);
 			}
 			else if($_REQUEST['estado'] == 'desativar')
 			{
 				$entity->disableEnt($bd);
 			}
 			else if($_REQUEST['estado']=='alteracao')
 			{
 				$entity->changeEnt($bd);
 				
 			}
			else if($_REQUEST['estado'] == 'inserir')
			{
				$entity->insertEnt($bd);
				
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
/**
 * Very importante $Dp_OpObject is an object from the class in common Dp_Op.
 * This method present in this class will handle all the operations that we can do in 
 * Entity page.
 * @author fabio
 *
 */
class Entidade
{
	/**
	 * This method will be responsable for the print of the form
	 * this method will receive a  Dp_OpObject (instance from the class 
	 * Dp_Op in common that allow database operation)
	*/
	public function form($Dp_OpObject)
	{
?>
		<html>
			<h3>Gestão de Componentes - Introdução</h3>
			<form id="insertForm">
				<label for="nome">Nome:</label>
				<br>
				<input type="text" id="nome" name="nome">
				<br>
				<label for="atv_int">Estado:</label><br>
<?php 
			$stateEnumValues = $Dp_OpObject->getEnumValues('ent_type','state'); //this function is in common.php
			//print_r($stateEnumValues);
			
			foreach($stateEnumValues as $value)
			{
				if($value == 'active')
				{
?>				
					<html>
						<input type="radio" id="atv_int" name="atv_int" value="active" >Ativo
						<br>
					</html>
<?php 	
				}
				else 
				{
?>
					<html>
						<input type="radio" name="atv_int" value="inactive" >Inativo
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
	/**
	 * This method will do the server side validation
	 */
	public function ssvalidation($Dp_OpObject)
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
			$sanitizeName = $Dp_OpObject->userInputVal($_REQUEST['nome']);
			$res_checkRep = $Dp_OpObject->runQuery("SELECT * FROM ent_type WHERE name like '".$sanitizeName."'");
			if($res_checkRep->num_rows)
			{
?>
				<html><p>Já existe uma entidade do tipo que está a introduzir.</p></html>
<?php 
				return false;
			}
			else
			{
				return true;
			}
		}
	}
	/**
	 * This method will be responsable for populated the form for the user to be able to  edit a selected entity
	 * this method will receive a Dp_OpObject (instance from the class Dp_Op in common that allow database operation)
	 * and it will receive the id from the selected entity
	*/
	public function editEntity($ent_id,$Dp_OpObject)
	{
		$res_EntEdit = $Dp_OpObject->runQuery("SELECT * FROM ent_type WHERE id='".$ent_id."'");
		$read_EntToEdit = $res_EntEdit->fetch_assoc();
		
?>		
		<html>
			<h3>Gestão de Componentes - Edição</h3>
				<form id="editForm">
					<label>Nome:</label>
					<br>
					<input type="text" name="nome" value="<?php echo $read_EntToEdit['name'] ?>" required>
					<br>
<?php 
		$stateEnumValues = $Dp_OpObject->getEnumValues('ent_type','state');
		foreach($stateEnumValues as $value)
		{
			if($value == 'active')
			{
				if(	$read_EntToEdit['state'] == 'active' )
				{
?>
					<input type="radio" name="atv_int" value="active" checked="checked" required>Ativo
					<br>
<?php 	
				}
				else
				{
?>
					<input type="radio" name="atv_int" value="active" required>Ativo
					<br>
<?php 
				}
			  }
			  else 
			  {
			  	if($read_EntToEdit['state'] == 'inactive')
			  	{
?>
					<input type="radio" name="atv_int" value="inactive" checked="checked" required>Inativo
					<br>
<?php 			
			  	}
			  	else 
			  	{
?>
					<input type="radio" name="atv_int" value="inactive" required>Inativo
					<br>	
<?php 
			  	}
			  }
		}//fim for each
?>
				<input type="hidden" name="ent_id" value="<?php echo $read_EntToEdit['id'] ?>">
				<input type="hidden" name="estado" value="alteracao">
				<input type="submit" value="Alterar Componente">
			</form>
		</html>
<?php 	}
		
	/**
	 *  This method will check if is everything ok with the submited data and if really is
	 *  it will update the existing entity
	 */
	public function changeEnt($Dp_OpObject) 
	{
		if ($this->ssvalidation ($Dp_OpObject)) // / verifies if all the field are filled and if the name i'm trying to submit exists in ent_type
		{
			$sanitizeName = $Dp_OpObject->userInputVal($_REQUEST['nome']);

		//	print_r($_REQUEST);
		//	echo "UPDATE `ent_type` SET `name`=".$sanitizeName.",`state`=".$_REQUEST['atv_int']." WHERE id = ".$_REQUEST['ent_id']."";
			$res_EntTypeAS =  $Dp_OpObject->runQuery("UPDATE `ent_type` SET `name`='".$sanitizeName."',`state`='".$_REQUEST['atv_int']."' WHERE id = ".$_REQUEST['ent_id']."");
?>
			<p>Alterou os dados da entidade com sucesso.</p>
			<p>Clique em <a href="/gestao-de-entidades"/>Continuar</a> para avançar</p>
<?php 
		}
		else
		{
			goBack ();
		}
	}

	/**
	 * This method will disable an enttity when we click in desactivar button 
	 * @param unknown $Dp_OpObject
	 */
	public function disableEnt($Dp_OpObject)
	{
		$res_EntTypeD = $Dp_OpObject->runQuery("SELECT name FROM ent_type WHERE id = ".$_REQUEST['ent_id']);
		$read_EntTypeD = $res_EntTypeD->fetch_assoc();
		$Dp_OpObject->runQuery("UPDATE ent_type SET state='inactive' WHERE id =".$_REQUEST['ent_id']);
?>
			<p>A entidade <?php echo $read_EntTypeD['name'] ?>  foi desativada</p>
			<p>Clique em <a href="/gestao-de-entidades"/>Continuar</a> para avançar</p>
<?php 		
	}
	
	/**
	 * This method will enable the entity when we click in then activate button 
	 * @param unknown $Dp_OpObject
	 */
	public function enableEnt($Dp_OpObject)
	{
		$res_EntTypeA = $Dp_OpObject->runQuery("SELECT name FROM ent_type WHERE id = ".$_REQUEST['ent_id']);
		$read_EntTypeA = $res_EntTypeA->fetch_assoc();
		$Dp_OpObject->runQuery("UPDATE ent_type SET state='active' WHERE id =".$_REQUEST['ent_id']);
?>
		<html>
		 	<p>A entidade <?php echo $read_EntTypeA['name'] ?> foi ativada</p>
		 	<p>Clique em <a href="/gestao-de-entidades"/>Continuar</a> para avançar</p>
		</html>
<?php 		
	}
	
	/**
	 * This method will insert a new entity in the database
	 * @param unknown $Dp_OpPbject
	 */
	public function insertEnt($Dp_OpObject)
	{
		if($this->ssvalidation($Dp_OpObject)) 
		{
			//print_R($_REQUEST);
			$sanitizeName = $Dp_OpObject->userInputVal($_REQUEST['nome']);
			$queryInsert = "INSERT INTO `ent_type`(`id`, `name`, `state`) VALUES (NULL,'".$sanitizeName."','".$_REQUEST['atv_int']."')";
			$res_querState = $Dp_OpObject->runQuery($queryInsert);
?>
				<p>Inseriu os dados de uma nova entidade com sucesso</p>
				<p>Clique em <a href="/gestao-de-entidades"/>Continuar</a> para avançar</p>
<?php 	
		}
		else
		{
			goBack();
		}

	}
}
?>
