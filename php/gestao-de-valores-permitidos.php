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
						$array = array();
						while($valoresEnum = $res_NProp->fetch_assoc())
						{
?>
							<tr>
<?php 				
							$queryPropAllowed = "SELECT * FROM prop_allowed_value WHERE property_id = ".$valoresEnum['id'];
							$propAllowed = $this->bd->runQuery($queryPropAllowed);

							$queryGetValores = "SELECT id, name FROM ent_type WHERE id = ".$valoresEnum['ent_type_id'];
							$nomeComponente = $this->bd->runQuery($queryGetValores);
							$nomeComponente = $nomeComponente->fetch_assoc();

							//Acerto dos rowspan caso exista valores repetidos como tv.
							$acertaRowSpan = "SELECT * FROM prop_allowed_value as pav ,property as prop, ent_type as comp WHERE comp.id = ".$nomeComponente['id']." AND  prop.ent_type_id = ".$nomeComponente['id']." AND prop.value_type = 'enum' AND prop.id = pav.property_id";
							$acerta = $this->bd->runQuery($acertaRowSpan);

							$verificaNumComp = "SELECT * FROM property WHERE ent_type_id = ".$valoresEnum['ent_type_id']." AND value_type = 'enum'";
							//echo $verificaNumComp;
							$getVerificaComp = $this->bd->runQuery($verificaNumComp);

							//Verifica se o nome que vou escrever já foi escrito alguma vez
							$conta = 0;
							for($i = 0; $i < count($array); $i++)
							{
								if($array[$i] == $nomeComponente['name'])
								{
									$conta++;
								}
							}

							if($conta == 0)
							{
								echo '<td rowspan='.$acerta->num_rows.'>';	
								echo $nomeComponente['name'];
								$array[] = $nomeComponente['name'];
							}
							else
							{
								//echo '<td rowspan='.mysqli_num_rows($acerta).'>';	

							}

							echo '<td rowspan='.$propAllowed->num_rows.'>';
							echo ''.$valoresEnum['id'];
							echo '</td>';
							//Nome da propriedade
							echo '<td rowspan='.$propAllowed->num_rows.'>';
							echo '<a href="gestao-de-valores-permitidos?estado=introducao&propriedade='.$valoresEnum['id'].'">['.$valoresEnum['name'].']</a>';
							//echo '['.$valoresEnum['name'].']';
							echo '</td>';	

							
							//$propAllowedArray = mysqli_fetch_assoc($propAllowed);
							while($propAllowedArray =$propAllowed->fetch_assoc())
							{											
								if($propAllowed->num_rows == 0)
								{	
?>
									<td colspan=4> Não há valores permitidos definidos </td>	
<?php 							}
								else
								{
?> 
									<td> $propAllowedArray['id'];</td>
									<td> $propAllowedArray['value'];</td>
									<td> $propAllowedArray['state'];</td>
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
