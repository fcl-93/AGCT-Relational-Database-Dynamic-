<?php

require_once("custom/php/common.php");
$addValues = new ValoresPermitidos();
/**
 *
 * @author fabio
 *
 */
class ValoresPermitidos
{
	private $bd;
	/**
	 * Contructor
	 */
	public function __construct(){
		$this->bd = new Db_Op();
		$this->checkUser();
	}
	/**
	 *  This method will check if the user as the permission to acess this page
	 * and will handle all the Requests states
	 */
	public function checkUser(){

		if(is_user_logged_in())
		{
			if(current_user_can('manage_allowed_values'))
			{
				if(empty($_REQUEST))
				{
					$this->tablePrint();
				}
				else if($_REQUEST['estado'] == 'introducao') 
				{
					
				}
				else if($_REQUEST['estado'] == 'inserir')
				{
					
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
				<p>Não tem sessão iniciada.</p>
			</html>
<?php
		}
	}
	/**
	 * This method will be responsable for the table print that will show properties with enum value 
	 * and the diferent values assigned to that field
	 */
	public function tablePrint()
	{
		$res_NProp = $this->bd->runQuery("SELECT * FROM property WHERE value_type = 'enum'"); // gets all properties with enum in value_type.
		$num_Prop = $res_NProp->num_rows;
		if($num_Prop > 0)
		{
?>
			<html>
				<table>
					<thead>
						<tr>
							<th>Componente</th>
							<th>Id</th>
							<th>Propriedade</th>
							<th>Id</th>
							<th>Valores permitidos</th>
							<th>Estado</th>
							<th>Ação</th>
						<tr>
					</thead>
					<tbody>
<?php
						$savePrintedNames = array();
						while($read_Prop = $res_NProp->fetch_assoc())
						{
?>
							<tr>
<?php
								// gets all enum value for the property we will print now
								$res_EnumToPrint = $this->bd->runQuery("SELECT * FROM prop_allowed_value WHERE property_id = ".$read_Prop['id']); 
							
								//get the entity name and id for the preperty that we will print
								$result_entName =	$this->bd->runQuery("SELECT id, name FROM ent_type WHERE id = ".$read_Prop['ent_type_id']); 
								$read_entName = $result_entName->fetch_assoc();
								
								//rowspan reconstruction if there is any repeated entity
								$res_fit = $this->bd->runQuery("SELECT * FROM prop_allowed_value as pav ,property as prop, entity_type as ent_tp WHERE ent_tp.id = ".$read_entName['id']." AND  ent_type_id = ".$read_entName['id']." AND prop.value_type = 'enum' AND prop.id = pav.property_id");	
								$read_fit = $res_fit->fetch_assoc();
								
								$this->bd->runQuery("SELECT * FROM property WHERE ent_type_id = ".$read_Prop['ent_type_id']." AND value_type = 'enum'");				
								
								//Checks if the name has been written before
								$conta = 0;
								for($i = 0; $i < count($array); $i++)
								{
									if($array[$i] == $read_entName['name'])
									{
										$conta++;
									}
								}
								//the entity name I'm strating to write has never writtem before							
								if($conta == 0)
								{
?>
									<td rowspan="<?php echo $read_fit->num_rows;?>">
									
<?php 
									echo $read_entName['name'];
									$array[] = $read_entName['name'];
								}
								else
								{
									
								}
								
?>			
								<td rowspan="<?php echo $res_EnumToPrint->num_rows; ?>"><?php echo $read_Prop['id'];  ?></td>


								<!-- Nome da propriedade-->
								<td rowspan="<?php echo $res_EnumToPrint->num_rows; ?>"><a href="gestao-de-valores-permitidos?estado=introducao&propriedade='<?php echo $read_Prop['id'];?>'">['<?php echo $read_Prop['name']; ?>']</a></td>
<?php 								
								
									
								while($read_EnumToPrint = $res_EnumToPrint->fetch_assoc())
								{
									if($res_EnumToPrint->numrows == 0)
									{
?>
										<td colspan=4>Não há valores permitidos definidos</td>
<?php 
									}
									else
									{
?>
										<td><?php echo $read_EnumToPrint['id'];?></td>
										<td><?php $read_EnumToPrint['value'];?></td>
										<td><?php $read_EnumToPrint['state'];?></td>
										<td>[editar][desativar]</td>
						</tr>
<?php 
									}
								}
?>	
							</tr>
<?php 
						}
?>
					<tbody>
				</table>
			<html>	
<?php 										
		}
		else
		{
?>
			<html>
				<p>Não há propriedades especificadas cujo tipo de valor seja enum. <br>
				Especificar primeiro nova(s) propriedade(s) e depois voltar a esta opção</p>
			</html>
<?php 						
		}
	}
	
	public function insertionForm(){}
	
	public function editFrom(){}
	public function activate(){}
	public function desactivate(){}
	public function ssvalidation(){}
	public function insertState(){}
	
}
