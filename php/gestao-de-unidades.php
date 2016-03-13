<?php 
require_once("custom/php/common.php");
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
	}
	/**
	 * This method will print the table that shows all the unit values that you've inserted 
	 * previously in the database
	 */
	public function tablePrint(){
?>
	<html>
		<thead>
			<tr>
				<th>ID </th>
				<th></th>
			<tr>
		</thead>
		<tbody>
<?php 
				
?>			
		</tbody>
	</html>
<?php
	}
	/**
	 * This method will print the form that will be used to insert a new unit type.
	 */
	public function insertFormPrint(){}
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