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
        private $gereHist;
	
	/**
	 * Contructor
	 */
	public function __construct(){
		$this->bd = new Db_Op();
                $this->gereHist= new UnidadeHist();
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
                                else if($_REQUEST['estado'] == 'historico')
                                {
                                    $this->gereHist->showHist($this->bd);
                                }
                                else if($_REQUEST['estado'] == 'voltar')
                                {
                                    $this->gereHist->estadoVoltar($this->bd);
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
                                                                <td>
                                                                    <a href="gestao-de-unidades?estado=desativar&unit_id=<?php echo $read_Units['id'];?>">[Desativar]</a>
                                                                    <a href="gestao-de-unidades?estado=historico&unit_id=<?php echo $read_Units['id'];?>">[Histórico}</a>
                                                                </td>
<?php
                                                            }
                                                            else if($read_Units['state'] =='inactive')
                                                            {
?>
                                                                <td>Inativo</td>
                                                                <td>
                                                                    <a href="gestao-de-unidades?estado=activar&unit_id=<?php echo $read_Units['id'];?>">[Ativar]</a>
                                                                    <a href="gestao-de-unidades?estado=historico&unit_id=<?php echo $read_Units['id'];?>">[Histórico]</a>
                                                                </td>

                                                                
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
            $this->gereHist->atualizaHistorico();
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
            $this->gereHist->atualizaHistorico();
            if($this->bd->runQuery("UPDATE prop_unit_type SET updated_on = '".date("Y-m-d H:i:s",time())."' state = 'active' WHERE id=".$_REQUEST['unit_id']))
            {
?>
                        <html>
                            <p>A unidade <?php echo $this->bd->runQuery("SELECT name FROM prop_unit_type WHERE id=".$_REQUEST['unit_id'])->fetch_assoc()['name'];?> foi ativada.</p>
                            <p>Clique em <a href="/gestao-de-unidades"/>Continuar</a> para avançar</p>
                        </html>
<?php
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

class UnidadeHist
{
    public function _construct() {
        
    }
    
    /**
     * This method controls the excution flow when the state is Voltar
     * Basicly he does all the necessary queries to reverse a property to an old version
     * saved in the history
     * @param type $db (object form the class Db_Op)
     */
    public function estadoVoltar ($db) {
        $this->atualizaHistorico($db);
        $selectAtributos = "SELECT * FROM hist_prop_unit_type WHERE id = ".$_REQUEST['hist'];
        $selectAtributos = $db->runQuery($selectAtributos);
        $atributos = $selectAtributos->fetch_assoc();
        $updateHist = "UPDATE prop_unit_type SET ";
        foreach ($atributos as $atributo => $valor) {
            if ($atributo != "id" && $atributo != "inactive_on" && $atributo != "active_on" && $atributo != "prop_unit_type_id" && !is_null($valor)) {
                $updateHist .= $atributo." = '".$valor."',"; 
            }
        }
        $updateHist .= " updated_on = '".date("Y-m-d H:i:s",time())."' WHERE id = ".$_REQUEST['unit_id'];
        echo $updateHist;
        $updateHist =$db->runQuery($updateHist);
        if ($updateHist) {
            $db->getMysqli()->commit();
?>
            <p>Atualizou a unidade com sucesso para uma versão anterior.</p>
            <p>Clique em <a href="/gestao-de-unidades/">Continuar</a> para avançar.</p>
<?php
        }
        else {
?>
            <p>Não foi possível reverter a unidade para a versão selecionada</p>
<?php
            $db->getMysqli()->rollback();
            goBack();
        }
    }
    
    /**
     * This method is responsible for the execution flow when the state is Histórico.
     * He starts by presenting a datepicker with options to do a kind of filter of 
     * all the history of the selected unit type.
     * After that he presents a table with all the versions presented in the history
     * @param type $db (object form the class Db_Op)
     */
    public function showHist ($db) {
        if (isset($_REQUEST["histAll"])) {
            $this->apresentaHistTodas($db);
        }
        else {
        //meto um datepicker        
?>
        <form method="GET">
            Verificar histórico:<br>
            <input type="radio" name="controlDia" value="ate">até ao dia<br>
            <input type="radio" name="controlDia" value="aPartir">a partir do dia<br>
            <input type="radio" name="controlDia" value="dia">no dia<br>
            <input type="text" id="datepicker" name="data" placeholder="Introduza uma data">
            <input type="hidden" name="estado" value="historico">
            <input type="hidden" name="id" value="<?php echo $_REQUEST["unit_id"]; ?>">
            <input type="submit" value="Apresentar histórico">
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>Data de Ativação</th>
                    <th>Data de Desativação</th>
                    <th>Unidade</th>
                    <th>Estado</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
<?php
        if (empty($_REQUEST["data"])) {
            $queryHistorico = "SELECT * FROM hist_prop_unit_type WHERE prop_unit_type_id = ".$_REQUEST["unit_id"]." ORDER BY inactive_on DESC";
        }
        else {
            if (isset($_REQUEST["controlDia"]) && $_REQUEST["controlDia"] == "ate") {
                $queryHistorico = "SELECT * FROM hist_prop_unit_type WHERE prop_unit_type_id = ".$_REQUEST["unit_id"]." AND inactive_on <= '".$_REQUEST["data"]."' ORDER BY inactive_on DESC";
            }
            else if (isset($_REQUEST["controlDia"]) && $_REQUEST["controlDia"] == "aPartir") {
                $queryHistorico = "SELECT * FROM hist_prop_unit_type WHERE prop_unit_type_id = ".$_REQUEST["unit_id"]." AND inactive_on >= '".$_REQUEST["data"]."' ORDER BY inactive_on DESC";
            }
            else if (isset($_REQUEST["controlDia"]) && $_REQUEST["controlDia"] == "dia"){
                $queryHistorico = "SELECT * FROM hist_prop_unit_type WHERE prop_unit_type_id = ".$_REQUEST["unit_id"]." AND inactive_on < '".date("Y-m-d",(strtotime($_REQUEST["data"]) + 86400))."' AND inactive_on >= '".$_REQUEST["data"]."' ORDER BY inactive_on DESC";
            }
            else {
                $queryHistorico = "SELECT * FROM hist_prop_unit_type WHERE prop_unit_type_id = ".$_REQUEST["unit_id"]." AND inactive_on < '".date("Y-m-d",(strtotime($_REQUEST["data"]) + 86400))."' AND inactive_on >= '".$_REQUEST["data"]."' ORDER BY inactive_on DESC";
            }
        }
        $queryHistorico = $db->runQuery($queryHistorico);
        if ($queryHistorico->num_rows == 0) {
?>
            <tr>
                <td colspan="11">Não existe registo referente à unidade selecionada no histórico</td>
                <td><?php goBack(); ?></td>
            </tr>
<?php
        }
        else {
            while ($hist = $queryHistorico->fetch_assoc()) {
?>
                <tr>
                    <td><?php echo $hist["active_on"];?></td>
                    <td><?php echo $hist["inactive_on"];?></td>
                    <td><?php echo $hist["name"];?></td>
                    <td>
<?php
                    if ($hist["state"] === "active")
                    {
                        echo 'Ativo';
                    }
                    else
                    {
                        echo 'Inativo';
                    }
?>
                    </td>
                    <td><a href ="?estado=voltar&hist=<?php echo $hist["id"];?>&unit_id=<?php echo $_REQUEST["unit_id"];?>">Voltar para esta versão
                        </a>
                    </td>
                </tr>
<?php
            }
        }
?>
            <tbody>
        </table>
<?php
        
    }
    }
    
    
    /**
     * This method will update the history of the unit type.
     */
    private function atualizaHistorico ($bd) {
        $selectAtributos = "SELECT * FROM prop_unit_type WHERE id = ".$_REQUEST['unit_id'];
        $selectAtributos = $bd->runQuery($selectAtributos);
        $atributos = $selectAtributos->fetch_assoc();
        $updateHist = "INSERT INTO `hist_prop_unit_type`(`name`, `state`, `active_on`,`inactive_on`, `prop_unit_type_id`) "
                . "VALUES ('".$atributos["name"]."','".$atributos["state"]."','".$atributos["updated_on"]."','".date("Y-m-d H:i:s",time())."',".$_REQUEST["unit_id"].")";
        $updateHist = $bd->runQuery($updateHist);
    }
}
?>