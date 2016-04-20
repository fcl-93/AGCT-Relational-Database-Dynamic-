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
                                else if($_REQUEST['estado'] == 'desativar')
                                {
                                    $this->desactivate();
                                }
                                else if($_REQUEST['estado'] == 'activar')
                                {
                                    $this->activate();
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
                                 <p>Clique <a href="/login">aqui</a> para iniciar sessão.</p>
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
                            <table id="sortedTable" class="table">
					<thead>
						<tr>
							<th>Id</th>
							<th>Unidade</th>
                                                        <th>Estado</th>
                                                        <th>Ação</th>
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
<?php
                                                            if($read_Units['state']  == 'active')
                                                            {
?>
                                                                <td>Ativo</td>
                                                                <td><a href="gestao-de-unidades?estado=desativar&unit_id=<?php echo $read_Units['id'];?>">[Desativar]</a></td>
<?php
                                                            }
                                                            else if($read_Units['state'] =='inactive')
                                                            {
?>
                                                                <td>Inativo</td>
                                                                <td><a href="gestao-de-unidades?estado=activar&unit_id=<?php echo $read_Units['id'];?>">[Ativar]</a></td>

                                                                
<?php
                                                            }
?>

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
         * This method will disable unit-types that enabled.
         */
        private function desactivate(){
            $this->atualizaHistorico();
            if($this->bd->runQuery("UPDATE prop_unit_type SET state = 'inactive', updated_on = '".date("Y-m-d H:i:s",time())."' WHERE id=".$_REQUEST['unit_id']))
            {
?>
                        <html>
                            <p>A unidade <?php echo $this->bd->runQuery("SELECT name FROM prop_unit_type WHERE id=".$_REQUEST['unit_id'])->fetch_assoc()['name'];?> foi desativada</p>
                            <p>Clique em <a href="/gestao-de-unidades"/>Continuar</a> para avançar</p>
                        </html>
<?php
            }
        }
        /**
         * This method will activate unit-types that are disabled
         */
        private function activate(){
            $this->atualizaHistorico();
            if($this->bd->runQuery("UPDATE prop_unit_type SET state = 'active' WHERE id=".$_REQUEST['unit_id']))
            {
?>
                        <html>
                            <p>A unidade <?php echo $this->bd->runQuery("SELECT name FROM prop_unit_type WHERE id=".$_REQUEST['unit_id'])->fetch_assoc()['name'];?> foi ativada.</p>
                            <p>Clique em <a href="/gestao-de-unidades"/>Continuar</a> para avançar</p>
                        </html>
<?php
            }
        }
        
        private function atualizaHistorico () {
            $selectAtributos = "SELECT * FROM prop_unit_type WHERE id = ".$_REQUEST['unit_id'];
            $selectAtributos = $this->bd->runQuery($selectAtributos);
            $atributos = $selectAtributos->fetch_assoc();
            $updateHist = "INSERT INTO `hist_prop_unit_type`(`name`, `state`, `active_on`,`inactive_on`, `prop_unit_type_id`) "
                    . "VALUES ('".$atributos["name"]."','".$atributos["state"]."','".$atributos["updated_on"]."','".date("Y-m-d H:i:s",time())."',".$_REQUEST["unit_id"].")";
            $updateHist = $this->bd->runQuery($updateHist);
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
			//print_r($_REQUEST);
?>
			<p>Clique em para <?php goBack(); ?></p>
<?php 
		}
		else
		{
			//print_r($_REQUEST);
			$sanitizedName =  $this->bd->userInputVal($_REQUEST['nome']);
			$this->bd->runQuery("INSERT INTO `prop_unit_type`(`id`, `name`, `updated_on`) VALUES (null,'".$sanitizedName."','".date("Y-m-d H:i:s",time())."')");
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