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
                    <form method="GET">
                        Verificar propriedades existentes no dia : 
                        <input type="text"  class="datepicker" id="datepicker" name="data" placeholder="Introduza uma data"> 
                        <input type="hidden" name="estado" value="historico">
                        <input type="hidden" name="histAll" value="true">
                        <input type="submit" value="Apresentar propriedades">
                    </form>
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
                                            <a href="gestao-de-unidades?estado=historico&unit_id=<?php echo $read_Units['id'];?>">[Histórico]</a>
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
         * Check for properties with the selected unit type
         * @return boolean (true if there are already any properties wut the selected unit type)
         */
        private function checkForProp () {
            $checkProp = "SELECT * FROM property WHERE unit_type_id = ".$_REQUEST["unit_id"];
            $checkProp = $this->bd->runQuery($checkProp);
            if ($checkProp->num_rows > 0) {
                return true;
            }
            else {
                return false;
            }
        }        
        
        /**
         * This method will disable unit-types that enabled.
         */
        private function desactivate(){
            if (!$this->checkForProp()) {
                if($this->gereHist->atualizaHistorico($this->bd)) {
                    if($this->bd->runQuery("UPDATE prop_unit_type SET state = 'inactive', updated_on = '".date("Y-m-d H:i:s",time())."' WHERE id=".$_REQUEST['unit_id']))
                    {
                        $this->bd->getMysqli()->commit();
?>
                        <html>
                            <p>A unidade <?php echo $this->bd->runQuery("SELECT name FROM prop_unit_type WHERE id=".$_REQUEST['unit_id'])->fetch_assoc()['name'];?> foi desativada</p>
                            <p>Clique em <a href="/gestao-de-unidades"/>Continuar</a> para avançar</p>
                        </html>
<?php
                    }
                    else {
                        $this->bd->getMysqli()->rollback();
?>
                        <p>Não foi possível desativar a unidade pretendida.</p>
<?php
                        goBack();
                    }
                }
                else {
                    $this->bd->getMysqli()->rolback();
?>
                    <p>Não foi possível desativar a unidade pretendida.</p>
<?php
                    goBack();              
                }
            }
            else {
?>
                <p>Não é possível desativar a unidade pretendida, uma vez que já existem propriedades com essa unidade associada.</p>
                <p>Para desativar ou editar essa propriedades clique em <a href="/gestao-de-propriedades">Gestão de propriedades</a> ou clique em <?php goBack();?> para voltar à página anterior.</p>
<?php  
            }
        }
        /**
         * This method will activate unit-types that are disabled
         */
        private function activate(){
            if ($this->gereHist->atualizaHistorico($this->bd)) {
                if($this->bd->runQuery("UPDATE prop_unit_type SET updated_on = '".date("Y-m-d H:i:s",time())."', state = 'active' WHERE id=".$_REQUEST['unit_id']))
                {
                    $this->bd->getMysqli()->commit();
?>
                    <html>
                        <p>A unidade <?php echo $this->bd->runQuery("SELECT name FROM prop_unit_type WHERE id=".$_REQUEST['unit_id'])->fetch_assoc()['name'];?> foi ativada.</p>
                        <p>Clique em <a href="/gestao-de-unidades"/>Continuar</a> para avançar</p>
                    </html>
<?php
                }
                else {
                    $this->bd->getMysqli()->rollback();
?>
                    <p>Não foi possível ativar a unidade pretendida.</p>
<?php
                    goBack();
                }
            }
            else {
?>
                <p>Não foi possível ativar a unidade pretendida.</p>
<?php
                goBack();              
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
        if ($this->atualizaHistorico($db)) {
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
        else if (empty($_REQUEST["selData"]) || (!empty($_REQUEST["selData"]) && $db->validaDatas($_REQUEST['data']))) {
        //meto um datepicker        
?>
        <form method="GET">
            Verificar histórico:<br>
            <input type="radio" name="controlDia" value="ate">até ao dia<br>
            <input type="radio" name="controlDia" value="aPartir">a partir do dia<br>
            <input type="radio" name="controlDia" value="dia">no dia<br>
            <input type="text"  class="datepicker" id="datepicker" name="data" placeholder="Introduza uma data">
            <input type="hidden" name="selData" value="true">
            <input type="hidden" name="estado" value="historico">
            <input type="hidden" name="unit_id" value="<?php echo $_REQUEST["unit_id"]; ?>">
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
                <td colspan="4">Não existe registo referente à unidade selecionada no histórico</td>
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
     * @return boolean (true if the insertion in the history was correct)
     */
    public function atualizaHistorico ($bd) {
        $bd->getMySqli()->autocommit(false);
        $bd->getMySqli()->begin_transaction();
        $selectAtributos = "SELECT * FROM prop_unit_type WHERE id = ".$_REQUEST['unit_id'];
        $selectAtributos = $bd->runQuery($selectAtributos);
        $atributos = $selectAtributos->fetch_assoc();
        $updateHist = "INSERT INTO `hist_prop_unit_type`(`name`, `state`, `active_on`,`inactive_on`, `prop_unit_type_id`) "
                . "VALUES ('".$atributos["name"]."','".$atributos["state"]."','".$atributos["updated_on"]."','".date("Y-m-d H:i:s",time())."',".$_REQUEST["unit_id"].")";
        $updateHist = $bd->runQuery($updateHist);
        if ($updateHist) {
            return true;
        }
        else {
            return false;
        }        
    }
    
    /**
     * This method creates a table with a view of all the unit types in the selected day
     * @param type $tipo (indicates if we are working with relations or entities)
     * @param type $db (object form the class Db_Op)
     */
    private function apresentaHistTodas ($db) {
        if ($db->validaDatas($_REQUEST['data'])) {
?>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Unidade</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
<?php
                // Queries that select the verion present in the history or in the main table in the given date
                $selecionaHist = "SELECT * FROM hist_prop_unit_type WHERE ('".$_REQUEST["data"]."' > active_on AND '".$_REQUEST["data"]."' < inactive_on) OR ((active_on LIKE '".$_REQUEST["data"]."%' AND inactive_on < '".$_REQUEST["data"]."') OR inactive_on LIKE '".$_REQUEST["data"]."%') GROUP BY prop_unit_type_id ORDER BY inactive_on DESC";
                $selecionaUnit = "SELECT * FROM prop_unit_type WHERE updated_on < '".$_REQUEST["data"]."' OR updated_on LIKE '".$_REQUEST["data"]."%'";
                echo $selecionaUnit.$selecionaHist;
                
                $resultSelecionaUnit = $db->runQuery($selecionaUnit);
                $resultSelecionaHist = $db->runQuery($selecionaHist);
?>
                <tr>
<?php
                    // Creates a temporary table with the results of the previous queries, this will be the table that should be printed.
                    $creatTempTable = "CREATE TEMPORARY TABLE temp_table (`id` INT UNSIGNED NOT NULL,
                            `name` VARCHAR(128) NOT NULL DEFAULT '',
                            `state` ENUM('active','inactive') NOT NULL)";
                    $creatTempTable = $db->runQuery($creatTempTable);
                    
                    while ($unit = $resultSelecionaUnit->fetch_assoc()) {
                        $db->runQuery("INSERT INTO temp_table VALUES (".$unit['id'].",'".$unit['name']."','".$unit['state']."')");
                    }
                    while ($hist = $resultSelecionaHist->fetch_assoc()) {
                       $db->runQuery("INSERT INTO temp_table VALUES (".$hist['prop_unit_type_id'].",'".$hist['name']."','".$hist['state']."')");
                    }
                    $resultSeleciona = $db->runQuery("SELECT * FROM temp_table GROUP BY id ORDER BY id ASC");
                    
                    while($arraySelec = $resultSeleciona->fetch_assoc())
                    {
?>
                        <td><?php echo $arraySelec["id"]; ?></td>
                        <td><?php echo $arraySelec["name"]; ?></td>
                        <td>
<?php
                        if ($arraySelec["state"] === "active")
                        {
                            echo 'Ativo';
                        }
                        else
                        {
                            echo 'Inativo';
                        }
?>
                        </td>
                    </tr>
<?php
                    }
                    $db->runQuery("DROP TEMPORARY TABLE temp_table");
                
?>
            </tbody>
        </table>
<?php
        }
    }
}
?>