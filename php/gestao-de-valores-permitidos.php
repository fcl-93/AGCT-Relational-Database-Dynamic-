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
		// gets all properties with enum in value_type.
		$res_NProp = $this->bd->runQuery("SELECT * FROM property WHERE value_type = 'enum'"); 
		$num_Prop = $res_NProp->num_rows;
		if($num_Prop > 0)
		{
?>
			<html>
				<table>
					<thead>
						<tr>
							<th>Entidade</th>
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
						$printedNames = array();
						while($read_PropWEnum = $res_NProp->fetch_assoc())
						{
?>
							<tr>
<?php 				
								//Get all enum values for the property that in will start printing now
								$res_Enum = $this->bd->runQuery("SELECT * FROM prop_allowed_value WHERE property_id = ".$read_PropWEnum['id']);
								
								//Get the entity name and id that is related to the property we are printing
								$res_Ent = $this->bd->runQuery("SELECT id, name FROM ent_type WHERE id = ".$read_PropWEnum['ent_type_id']);
								$read_EntName = $res_Ent->fetch_assoc();
								
								//Get the number of properties with that belonh to the etity I'm printing and have enum tipe
								$res_NumProps= $this->bd->runQuery("SELECT * FROM property WHERE ent_type_id = ".$read_PropWEnum['$ent_type_id']." AND value_type = 'enum'");
								
								
								
								
							//Verifica se o nome que vou escrever já foi escrito alguma vez
							$conta = 0;
							for($i = 0; $i < count($printedNames); $i++)
							{
								if($printedNames[$i] == $read_EntName['name'])
								{
									$conta++;
								}
							}

							if($conta == 0)
							{
								echo '<td rowspan='.$res_NumProps->num_rows.'>';	
								echo $read_EntName['name'];
								$printedNames[] = $read_EntName['name'];
							}
							else
							{
								//echo '<td rowspan='.mysqli_num_rows($acerta).'>';	

							}
?>
							<td rowspan="<?php echo $res_Enum->num_rows;?>"><?php echo $read_PropWEnum['id'];?></td>
							<!-- Nome da propriedade -->
							<td rowspan="<?php echo $res_Enum->num_rows;?>"><a href="gestao-de-valores-permitidos?estado=introducao&propriedade=<?php echo $read_PropWEnum['id'];?>">[<?php echo $read_PropWEnum['name'];?>]</a></td>

<?php 							
							//$propAllowedArray = mysqli_fetch_assoc($propAllowed);
							while($read_EnumValues =$res_Enum->fetch_assoc())
							{											
								if($res_Enum->num_rows == 0)
								{	
?>
									<td colspan=4> Não há valores permitidos definidos </td>	
<?php 							}
								else
								{
?> 
									<td><?php  $read_EnumValues['id'];?></td>
									<td><?php $read_EnumValues['value'];?></td>
									<td><?php $read_EnumValues['state'];?></td>
									<td>[editar][desativar]</td>';
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
