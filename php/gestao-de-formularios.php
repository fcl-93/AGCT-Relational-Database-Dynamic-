<?php
require_once("custom/php/common.php");

$gerencia = new gereForms();

class gereForms
{
	private $bd;
	private $numProp; //printed properties in the table
	/**
	 * Constructor
	 */
	public function __construct(){
		$this->bd = new Db_Op();
		$this->numProp = 0;
		$this->checkUser();
	}
	
	/**
	 *  This method will check if the user as the permission to acess this page
	 * and will handle all the Requests states
	 */
	public function checkUser(){
		$capability = 'manage_custom_forms';
	
		if ( is_user_logged_in() )
		{
			if(current_user_can($capability))
			{
				if(empty($_REQUEST["estado"]))
				{
					$this->tablePrint();
				}
				else if($_REQUEST['estado'] == 'inserir')
				{
					$this->insertState();
				}
				else if($_REQUEST['estado'] == 'editar_form')
				{
					$this->formEdit();
				}
				else if ($_REQUEST['estado'] == 'updateForm')
				{
					
				}
				else if($_REQUEST['estado'] == 'ativar')
				{
					$this->activate();
				}
				else if($_REQUEST['estado'] == 'desativar')
				{
					$this->desactivate();
				}
			}
			else
			{
?>
				<html>
					<p>Não tem autorização para a aceder a esta página.</p>
				</html>
<?php 
			}
		}
		else 
		{
?>
			<html>
				<p>O utilizador não tem sessão iniciada.</p>
			</html>
<?php			
		}
	}
	
	/**
	 * This method will print the table that will be showing the forms and their state 
	 * in this table the user will be able to desactivate a edit forms.
	 */
	public function tablePrint(){
		$resForm = $this->bd->runQuery("SELECT * FROM custom_form ORDER BY name ASC");
		if($resForm->num_rows == 0)
		{
?>	
			<html>
				<p>Não existem formulários costumizados</p>
			</html>
<?php 
                        $this->intForm();
		}
		else
		{
?>

			<html>
				<table class="table">
					<thead>
						<tr>
							<th>Id</th>
							<th>Nome do formulário customizado</th>
							<th>Estado</th>
							<th>Ação</th>
						</tr>
					</thead>
					<tbody>
<?php 
						while($readForm = $resForm->fetch_assoc())
						{
?>
							<tr>
								<td><?php echo $readForm['id']; ?></td>
								<td><?php echo $readForm['name']; ?></td>
								<td>
<?php
									if($readForm['state'] === 'active')
									{
?>
										Ativo
<?php 
									}
									else
									{
?>
										Inativo
<?php 								}
?>
								</td>
								<td>
									<a href="gestao-de-formularios?estado=editar_form&form_id=<?php echo $readForm['id']; ?>">[Editar]</a>
<?php 
										if($readForm['state'] === 'active')
										{
?>
											<a href="gestao-de-formularios?estado=desativar&form_id=<?php echo $readForm['id'];?>">[Desativar]</a>
<?php 
										}
										else 
										{
?>
											<a href="gestao-de-formularios?estado=ativar&form_id=<?php echo $readForm['id'];?>">[Ativar]</a>
<?php 
										}
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
			$this->intForm();
		}
	}
	/**
	 * Prints the form composed by a table to create customized forms.
	 */
	public function intForm(){
?>
		<h3>Gestão de formulários customizados - Introdução</h3>
		<br>
<?php 
		//Get all ent_types that have at least one ent_type_id this will unecessary entities from the table form
		$resEnt = $this->bd->runQuery("SELECT DISTINCT ent_type.id, ent_type.name FROM ent_type , property WHERE property.ent_type_id=ent_type.id");

		if($resEnt->num_rows == 0)
		{
?>	
			<html>
				<p>Não pode criar formulários uma vez que ainda não foram inseridas entidades.</p>
			</html>
<?php 
		}
		else 
		{
?>
		<html>
			<form method="POST">
				<input type="hidden" name="estado" value="inserir">
				<label>Nome do formulário customizado:</label> <input type="text" name="nome">
				<label id="nome" class="error" for="nome"></label>
				<br><br>

				<table class="table">
					<thead>
						<tr>
							<th>Entidade</th>
							<th>Id</th>
							<th>Propriedade</th>
							<th>Tipo de valor</th>
							<th>Nome do campo no formulário</th>
							<th>Tipo do campo no formulário</th>
							<th>Tipo de unidade</th>
							<th>Ordem do campo no formulário</th>
							<th>Tamanho do campo no formulário</th>
							<th>Obrigatório</th>
							<th>Estado</th>
							<th>Escolher</th>
							<th>Ordem</th>
						</tr>
					</thead>
					<tbody>
<?php 
					while($readEnt = $resEnt->fetch_assoc())
					{
						$res_GetProps = $this->bd->runQuery("SELECT p.id, p.name, p.value_type, p.form_field_name, p.form_field_type, p.unit_type_id, p.form_field_order,  p.mandatory, p.state FROM property AS p, ent_type AS e WHERE p.ent_type_id = e.id AND e.name LIKE '".$readEnt['name']."' ORDER BY p.name ASC");
						//echo "SELECT p.id, p.name, p.value_type, p.form_field_name, p.form_field_type, p.unit_type_id, p.form_field_order, p.form_field_size, p.mandatory, p.state FROM property AS p, ent_type AS e WHERE p.ent_type_id = e.id AND e.name LIKE ".$readEnt['name']." ORDER BY p.name ASC";
?>						
						<tr>
							<td rowspan="<?php echo $res_GetProps->num_rows ?>" style="vertical-align: top;">'<?php echo $readEnt["name"]?>'</td>		
<?php 							
							
							while($readGetProps = $res_GetProps->fetch_assoc())
							{
								$this->numProp++;
?>								
								<td><?php echo $readGetProps['id'];  ?></td>
								<td><?php echo $readGetProps['name'];?></td>
								<td><?php echo $readGetProps['value_type'];?></td>
								<td><?php echo $readGetProps['form_field_name']?></td>
								<td><?php echo $readGetProps['form_field_type']?></td>
								<td>
<?php 
									if(is_null($readGetProps["unit_type_id"]))
									{
?>
										-
<?php 
									}
									else
									{
										$res_UnitName = $this->bd->runQuery("SELECT name FROM prop_unit_type WHERE id = '".$readGetProps['unit_type_id']."'");
										while ($read_UnitName = $res_UnitName->fetch_assoc())
										{
											echo $read_UnitName['name'];
										}
									}
?>
								</td>
								<td><?php echo $readGetProps['form_field_order'];  ?></td>
								<!--<td><?php echo $readGetProps['form_field_size'];  ?></td>-->
								<td>?</td>					
								<td>
<?php 	
								if($readGetProps['mandatory'] == 1)
								{
									echo 'Sim';
								}
								else 
								{
									echo 'Não';
								}
?>
								</td>
								<td>
<?php
								if($readGetProps['state'] == 'active' )
								{
?>
									Ativo									
<?php 
								}
								else
								{
?>
									Inativo
<?php 									
								}
?>
								
								
								
								</td>
								<td><input type="checkbox" name="idProp<?php echo $this->numProp;?>" value="<?php echo $readGetProps['id'];?>"></td>
								
								<td><input type="text" name="ordem<?php echo $this->numProp; ?>"></td>
							</tr>
<?php 				
							}
?>						
						
<?php 						
					}
?>
					</tbody>
				</table>
				
				<input type="submit" value="Inserir formulário">
			</form>
		</html>
<?php 	
		}
		$_SESSION['propSelected'] = $this->numProp;
	}
	/**
	 * Server side validation when JQuery is disabled
	 */
	public function ssvalidation(){
		if($_REQUEST['estado'] == 'inserir')
		{
                    
                    if(empty($_REQUEST['nome']))
                    {
?>
			<html>	
				<p>Deve introduzir o nome para um novo formulário costumizado.</p>
			</html>
<?php	
                            return false;
                    }
                    //number of check boxes
                    $controlaCheck = 0;
                    for($i = 1; $i <= $_SESSION['propSelected']; $i++)
                    {
                            if(empty($_REQUEST["idProp".$i]))
                            {
                                    $controlaCheck++;
                            }
                    }
                    if($controlaCheck == $_SESSION['propSelected'])
                    {
?>   
                        <html>
                            <p> Deve selecionar pelo menos uma propriedade para o seu formulário <p>
                        </html>
<?php
                    }
                    //
                    for($i = 1; $i <= $_SESSION['propSelected']; $i++)
                    {
                            if((!is_numeric($_REQUEST["ordem".$i]) || $_REQUEST["ordem".$i] < 1) && isset($_REQUEST["idProp".$i]))
                            {
?> 
                                <html>
                                    <p>O campo ordem deve ser numérico e deve introduzir um valor superior a zero</p><br>
                                </html>
<?php    
                                return false;
                            }
                    }
                    return true;

                }	
                else 
                {
                    return true;
                }
	}
	
	public function formEdit()
	{
		$this->numProp = 0;
                $res_Nome = $this->bd->runQuery("SELECT name FROM custom_form WHERE id = ".$_REQUEST['form_id']);
                $read_Name = $res_Nome->fetch_assoc();
 ?>
<html>
        	<form method="POST">
                    <input type="hidden" name="estado" value="editar_form">
                    <label>Nome do formulário customizado:</label><input type="text" name="nome" value="<?php echo $read_Name['name']; ?>">
                        
                    <table  class="table">
                        <thead>
                            <tr>
                                <th>Entidade</th>
                                <th>Id</th>
                                <th>Propriedade</th>
                                <th>Tipo de valor</th>
                                <th>Nome do campo no formulário</th>
                                <th>Tipo do campo no formulário</th>
                                <th>Tipo de unidade</th>
                                <th>Ordem do campo no formulário</th>
                                <th>Tamanho do campo no formulário</th>
                                <th>Obrigatório</th>
                                <th>Estado</th>
                                <th>Escolher</th>
                                <th>Ordem</th>
                            </tr>
                        </thead>
                        <tbody>    
<?php			
                        $res_Ent = $this->bd->runQuery("SELECT DISTINCT ent_type.id, ent_type.name FROM ent_type , property WHERE property.ent_type_id=ent_type.id");
			while($arrayRes = $res_Ent->fetch_assoc())
			{
				$res_getProp = $this->bd->runQuery("SELECT p.id, p.name, p.value_type, p.form_field_name, p.form_field_type, p.unit_type_id, p.form_field_order, p.mandatory, p.state FROM property AS p, ent_type AS e WHERE  p.ent_type_id = e.id AND e.name LIKE '".$arrayRes["name"]."' ORDER BY p.name ASC");
				                                
				$numLinhas = $res_getProp->num_rows;
                ?>
                            <tr>
				<td colspan="1" rowspan="<?php echo $numLinhas; ?>" style="vertical-align: top;"><?php echo $arrayRes['name']; ?></td>
                <?php
				while($read_Props = $res_getProp->fetch_assoc())
				{
					$this->numProp++;
             ?>
					<td><?php echo $read_Props["id"];?></td>
					<td><?php echo $read_Props["name"];?></td>
					<td><?php echo $read_Props["value_type"];?></td>
					<td><?php echo $read_Props["form_field_name"];?></td>
					<td><?php echo $read_Props["form_field_type"];?></td>
					<td>
<?php
                                        if(is_null($read_Props["unit_type_id"]))
					{
?>
						-
<?php
                                        }
					else
					{
						$res_Unit = $this->bd->runQuery("SELECT name FROM prop_unit_type WHERE id = ".$read_Props["unit_type_id"]);
												
						while($arrayNome = $res_Unit->fetch_assoc())
						{
							echo $arrayNome['name'];
						}
					}
?>
					</td>
                                        
                                        <td><?php echo $read_Props["form_field_order"]; ?></td>
                                        <!-- // echo $read_Props["form_field_size"]; ?-->
					<td><?php echo '?'; ?></td>
					<td>
<?php					
                                        if($read_Props["mandatory"] == 1)
					{
						echo 'Sim';
					}
					else
					{
						echo 'Não';
					}
?>                                        
					</td>
					<td><?php echo $read_Props["state"]; ?></td>
<?php
						$res_Checkd = $this->bd->runQuery("SELECT * FROM custom_form_has_prop AS cfhp WHERE cfhp.custom_form_id = ".$_REQUEST['form_id']." AND cfhp.property_id = ".$read_Props["id"]);


						if($res_Checkd->num_rows == 1)
						{
							$arrayChecks = $res_Checkd->fetch_assoc();
                                                                
?>
							<td><input type="checkbox" name="idProp<?php echo $numProp; ?>" value="<?php echo $read_Props["id"]; ?>" checked></td>
							<td><input type="text" name="ordem<?php echo $numProp; ?>" value="<?php echo $arrayChecks["field_order"]?>"></td>
<?php
                                                }
						else
						{
?>
                                                        <td><input type="checkbox" name="idProp<?php echo $numProp;?>" value="<?php echo $read_Props["id"];?>"></td>';
                                                        <td><input type="text" name="ordem<?php echo $numProp ?>"></td>
<?php
                                                }
?>
						<input type="hidden" name="id" value="<?php echo $_REQUEST['form_id']; ?>">
                            </tr>	
<?php
                                }
			}
 ?>
                        </tbody>
                <table>
            <input type="submit" value="Atualizar formulário">
        </form>
                                </html>
<?php
	$_SESSION['propSelected'] = $this->numProp;

        }
                                                
	
	/**
	 * This method will activate the custom form the user selected.
	 */
	public function activate(){
		$this->bd->runQuery("UPDATE `custom_form` SET state='active' WHERE id=".$_REQUEST['form_id']);
		$res_formName = $this->bd->runQuery("SELECT name FROM custom_form WHERE id=".$_REQUEST['form_id']);
		$read_formName = $res_formName->fetch_assoc();
		?>
		<html>
		 	<p>O formulário <?php echo $read_formName['name'] ?> foi ativado</p>
		 	<p>Clique em <a href="/gestao-de-formularios"/>Continuar</a> para avançar</p>
		</html>
	<?php
		}
	/**
	  * This method will desactivate the custom form the user selected
	  */
	public function desactivate(){
			$this->bd->runQuery("UPDATE `custom_form` SET state='inactive' WHERE id=".$_REQUEST['form_id']);
			$res_formName = $this->bd->runQuery("SELECT name FROM custom_form WHERE id=".$_REQUEST['form_id']);
			$read_formName = $res_formName->fetch_assoc();
	?>
			<html>
			 	<p>O formulário <?php echo $read_formName['name'] ?> foi desativado</p>
			 	<p>Clique em <a href="/gestao-de-formularios"/>Continuar</a> para avançar</p>
			</html>
<?php
		}
	
	/**
	 * This method will handle the insertion that a user will make in the database.
	 */
	public function insertState(){
		if($this->ssvalidation())
		{
                    //echo $_SESSION['propSelected'];
			//Begin Transaction
			$this->bd->getMysqli()->autocommit(false);
			$this->bd->getMysqli()->begin_transaction();
			
			//Starts the insertion in the "database"
			$sanitizedInput = $this->bd->userInputVal($_REQUEST["nome"]);
			$this->bd->runQuery("INSERT INTO `custom_form`(`id`, `name`, `state`)VALUES(NULL,'".$sanitizedInput."','active')");
		
			$getLastId = $this->bd->getMysqli()->insert_id;
			$control = true;
			for($i = 1; $i <= $_SESSION['propSelected'] ; $i++)
			{
				if(isset($_REQUEST["idProp".$i]) && isset($_REQUEST["ordem".$i]))
				{
					if(!$this->bd->runQuery("INSERT INTO `custom_form_has_prop`(`custom_form_id`, `property_id`, `field_order`) VALUES (".$getLastId.",".$_REQUEST["idProp".$i].",'".$this->bd->userInputVal($_REQUEST["ordem".$i])."')"))
					{
                                            $control = false;
?>						
						<html>
							<p>A inserção de do novo formulário falhou</p>
						</html>
<?php 					
						$this->bd->getMysqli()->rollback();
					}

					
				}
			}
                        
			if($control == true)
                        {
?>		
						<html>
							<p>Inseriu um novo formulário com sucesso</p>
							<p>Clique em <a href="/gestao-de-formularios/">Continuar</a> para avançar</p>
						</html>
<?php 
						$this->bd->getMysqli()->commit();
			}
		
		}
		else 
		{
			goBack();	
		}
	}
		
	
}
?>
