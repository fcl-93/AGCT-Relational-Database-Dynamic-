<?php 
require_once("custom/php/common.php");
//instance of a new object from class Unidade the website will run here
	$novaUnidade = new Unidade();


/**
 * 
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
		$this->$bd = new Db_Op();
		$this->tablePrint();
	}
	/**
	 * This method will print the table that shows all the unit values that you've inserted 
	 * previously in the database
	 */
	public function tablePrint(){
		$res_Unit = $this->$bd->runQuery("SELECT * FROM prop_unit_type ORDER BY name ASC");
		$row_NumUnit = $res_Unit->num_rows;
		if($row_NumUnit  == 0)
		{
?>
			<html>
			Ola
				<p>Não há tipos de unidades</p>
			</html>
<?php 
			$this->insertFormPrint();	//call insertFormPrint method prints the form
		}
		else
		{
?>
Ola
			<html>
				<table>
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
			<form  method="post">
				<label>Inserir nova unidade:</label> <input type="text" id ="nome" name="nome"/>
				<br>
				<label class="error" for="nome"></label>
				<input type ="hidden" name ="estado" value ="inserir"/>
				<input type="submit" name="submit" value ="Inserir tipo de unidade"/>
			</form>
<?php 
	}
	/**
	 * Validations server side for the user submissions
	 */
	public function ssvalidation(){}
	/**
	 * If everything is ok with the input this method will eun the query to insert the user input into the database
	 */
	public function insertState(){}
	
}
?>