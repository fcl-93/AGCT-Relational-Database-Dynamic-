<?php 
require_once("custom/php/common.php");


//instance of a new object from class Unidade the website will run here
	$novaUnidade = new Unidade();


/**
 * Class has all the method that are responsable for the management of the entity_type page
 * @author fabio
 *
 */
class Unidade
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
	 * This method will check if the user as the permission to acess this page
	 * and will handle all the Requests states
	 */
	public function checkUser()
	{
		if(is_user_logged_in())
		{
			if(current_user_can('manage_unit_types'))
			{
				if(empty($_REQUEST))
				{
					$this->tablePrint();
				}
				else if($_REQUEST['estado'] == 'inserir') 
				{
					$this->insertState();
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
	 * This method will print the table that shows all the unit values that you've inserted 
	 * previously in the database
	 */
	public function tablePrint(){
		$res_Unit = $this->bd->runQuery("SELECT * FROM prop_unit_type ORDER BY name ASC");
		$row_NumUnit = $res_Unit->num_rows;
		if($row_NumUnit  == 0)
		{
?>
			<html>
				<p>Não há tipos de unidades</p>
			</html>
<?php 
			$this->insertFormPrint();	//call insertFormPrint method prints the form
		}
		else
		{
?>
			<html>
				<table class="table">
					<thead>
						<tr>
							<th>Id</th>
							<th>Unidade</th>
						</tr>
					</thead>
					<tbody>
<?php 
					while($read_Units = $res_Unit->fetch_assoc())
					{
?>
						<tr>
							<td><?php echo $read_Units['id']; ?></td>
							<td><?php echo $read_Units['name']; ?></td>
						</tr>
<?php 						
					}
?>									
					</tbody>
				</table>
			</html>
		<?php 
		$this->insertFormPrint(); //call insertFormPrint method prints the form
		}
	}
	/**
	 * This method will print the form that will be used to insert a new unit type.
	 */
	public function insertFormPrint(){
?>
		<h3>Gestão de unidades - introdução</h3>
			<form id="insertForm" method="post">
				<label>Inserir nova unidade:</label> 
				<br>
				<input type="text" id ="nome" name="nome"/>
				<br>
				<label class="error" for="nome"></label>
				<br>
				<input type ="hidden" name ="estado" value ="inserir"/>
				<input type="submit" name="submit" value ="Inserir tipo de unidade"/>
			</form>
<?php 
	}
	/**
	 * Check if the tried yo submit with an empty field.
	 */
	public function ssvalidation(){
		if(empty($_REQUEST['nome']))
		{
?>
			<html>
				<p>O campo nome é de preenchimento obrigatório.</p>
			</html>
<?php
			return false;
		}
		else
		{
			return true;
		}
	}
	/**
	 * If everything is ok with the input this method will eun the query to insert the user input into the database
	 */
	public function insertState(){
		if(!$this->ssvalidation())
		{
			print_r($_REQUEST);
?>
			<p>Clique em para <?php goBack(); ?></p>
<?php 
		}
		else
		{
			print_r($_REQUEST);
			$sanitizedName =  $this->bd->userInputVal($_REQUEST['nome']);
			$this->bd->runQuery("INSERT INTO `prop_unit_type`(`id`, `name`) VALUES (null,'".$sanitizedName."')");
?>
			<html>
				<h3>Gestão de unidades - introdução</h3>
				<p>Inseriu os dados de novo tipo de unidade com sucesso.</p>
				<p>Clique em <a href='/gestao-de-unidades/'> Continuar </a> para avançar.</p>
			</html>
<?php 
		}
		
	}
	
}
?>