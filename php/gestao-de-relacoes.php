<?php
require_once("custom/php/common.php");

/**
 * Class that handle all the methods that are necessary to execute this component
 */
class RelationManage
{
    private $db;            // Object from DB_Op that contains the access to the database
    private $capability;    // Wordpress's Capability for this component
    private $gereHist;

    /**
     * Constructor method
     */
    public function __construct(){
        $this->db = new Db_Op();
        $this->capability = "manage_relations";
        $this->gereHist = new RelHist();
        $this->executaScript();
    }

    /**
     * Main method that controls the capability of the current user to acces this component
     */
    public function executaScript()
    {
        // Check if any user is logged in
        if ( is_user_logged_in() )
        {
            // Check if the current user as the capability to use this component
            if(current_user_can($this->capability))
            {
                $this->verificaEstado();
            }
            else
            {
        ?>
            <html>
                <p>Não tem autorização para aceder a esta página</p>
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
     * Method that controls the execution flow of this component
     */
    private function verificaEstado()
    {
        if (empty($_REQUEST["estado"]))
        {
            $this->estadoEmpty();
        }
        elseif ($_REQUEST["estado"] === "inserir")
        {
            if($this->validarDados())
            {
                 $this->estadoInserir();
            }
        }
        elseif($_REQUEST['estado'] =='editar')
        {
            $this->estadoEditar();
        }
        elseif($_REQUEST['estado'] =='historico')
        {
             $this->gereHist->estadoHistorico($this->db);
        }
        elseif($_REQUEST['estado'] =='voltar')
        {
             $this->gereHist->estadoVoltar($this->db);
        }
        elseif($_REQUEST['estado'] =='update')
        {
            if($this->validarDados())
            {
                 $this->estadoUpdate();
            }
        }
        elseif($_REQUEST['estado'] == 'ativar' || $_REQUEST['estado'] == 'desativar')
        {
            $this->estadoAtivarDesativar();		
        }
    }
    
    /**
     * This method is responsible to control the flow execution when state is empty
     */
    private function estadoEmpty() {
        if($this->checkEntidades())
        {
            if($this->checkRelations())
            {
                $this->createTable();
            }
            $this->formInsert();
        }
    }
    
    /**
     * This method is responsible to control the flow execution when state is "inserir"
     */
    private function estadoInserir() {
        $ent1 = $this->db->userInputVal($_REQUEST['ent1']);
        $ent2 = $this->db->userInputVal($_REQUEST['ent2']);
        if (empty($_REQUEST['nome'])) {
            $queryNome1 = "SELECT name FROM ent_type WHERE id = ".$ent1;
            $queryNome2 = "SELECT name FROM ent_type WHERE id = ".$ent2;
            $nome = $this->db->criaNomeRel($queryNome1, $queryNome2);
        } 
        else {
            $nome = $this->db->userInputVal($_REQUEST['nome']);
        }
        $queryInsert = "INSERT INTO `rel_type`(`name`, `ent_type1_id`, `ent_type2_id`, `updated_on`) VALUES ('".$nome."',".$ent1.",".$ent2.",'".date("Y-m-d H:i:s",time())."')";
        $insert = $this->db->runQuery($queryInsert);
        if(!$insert)
        {
            echo "Ocorreu um erro ao intoduzir a nova relação.";
            goBack();
        }
        else
        {
            echo 'Inseriu os dados de nova relação com sucesso.';
            echo 'Clique em <a href="/gestao-de-relacoes/">Continuar</a> para avançar.';
        }
    } 
    
    /**
     * This method is responsible to control the flow execution when state is "editar"
     */
    private function estadoEditar() {
        $this->formEdit();
    }
    
    /**
     * This method is responsible to control the flow execution when state is "update"
     */
    private function estadoUpdate() {
        $this->gereHist->atualizaHistorico($this->db);
        $ent1 = $this->db->userInputVal($_REQUEST['ent1']);
        $ent2 = $this->db->userInputVal($_REQUEST['ent2']);
        if (empty($_REQUEST['nome'])) {
            $queryNome1 = "SELECT name FROM ent_type WHERE id = ".$ent1;
            $queryNome2 = "SELECT name FROM ent_type WHERE id = ".$ent2;
            $nome = $this->db->criaNomeRel($queryNome1, $queryNome2);
        } 
        else {
            $nome = $this->db->userInputVal($_REQUEST['nome']);
        }
        $queryUpdate = "UPDATE `rel_type` SET name = '".$nome."', ent_type1_id = ".$ent1.", ent_type2_id = ".$ent2.",updated_on ='".date("Y-m-d H:i:s",time())."' WHERE id = ".$_REQUEST["rel_id"];
        $update = $this->db->runQuery($queryUpdate);
        if(!$update)
        {
            echo "Ocorreu um erro ao editar a relação.";
            goBack();
        }
        else
        {
            echo 'Atualizou os dados da relação com sucesso.';
            echo 'Clique em <a href="/gestao-de-relacoes/">Continuar</a> para avançar.';
        }
    }
    
    /**
     * This method is responsible to control the flow execution when state is "ativar" or "desativar"
     */
    private function estadoAtivarDesativar() {
        $getNomes = $this->db->runQuery("SELECT * FROM rel_type WHERE id = ".$this->db->userInputVal($_REQUEST['rel_id']));
        $nomes = $getNomes->fetch_assoc();
        $idNome1 = $nomes["ent_type1_id"];
        $idNome2 = $nomes["ent_type2_id"];
        $avanca = false;
        $queryUpdate = "UPDATE rel_type SET state=";
        if ($_REQUEST["estado"] === "desativar"){
            if (!$this->verificaInst ($_REQUEST['rel_id'])) {
                if ($this->gereHist->atualizaHistorico($this->db) == false) {
?>
                    <p>Não foi possível desativar a propriedade pretendida.</p>
<?php 
                    goBack();
                }
                else {
                    $queryUpdate .= "'inactive'";
                    $estado = "desativada";
                    $avanca = true;
                }
            }
        }
        else {
            if ($this->gereHist->atualizaHistorico($this->db) == false) {
?>
                <p>Não foi possível ativar a propriedade pretendida.</p>
<?php 
                goBack();
            }
            else {
                $queryUpdate .= "'active'";
                $estado = "ativada";
                $avanca = true;
            }
        }
        if ($avanca) {
            $queryUpdate .= ",updated_on ='".date("Y-m-d H:i:s",time())."' WHERE id =".$_REQUEST['rel_id'];
            $this->db->runQuery($queryUpdate);
?>
            <html>
                <p>A relação <?php echo $this->db->getEntityName($idNome1)."-".$this->db->getEntityName($idNome2) ?> foi <?php echo $estado ?></p>
                <br>
                <p>Clique em <a href="/gestao-de-relacoes"/>Continuar</a> para avançar</p>
            </html>
<?php 
        }
       
    }
    
    /**
     * This method verifies if there is any instance of the rel_type selected
     * @param type $idRel (id of the rel_type we want ti check)
     * @return boolean (true if already exists)
     */
    private function verificaInst ($idRel) {
        $queryCheck = "SELECT * FROM relation WHERE state = 'active' AND rel_type_id = ".$idRel;
        $queryCheck = $this->db->runQuery($queryCheck);
        if ($queryCheck->num_rows > 0) {
?>
            <p>Não pode desativar este tipo de relação sem antes desativar todas as instâncias do mesmo.</p>
            <p>Para fazê-lo deve dirigir-se à página <a href = "/insercao-de-relacoes">Inserção de Relações</a></p>
<?php
            return true;
        }
        else {
            return false;
        }
    }
    
    /**
     * This method checks if there are 2 or more entities, if so the user can continue the process of add a new realtion
     * @return boolean (true if there are entities otherwise it will return false)
     */
    private function checkEntidades() {
        $querySelEnt = "SELECT * FROM ent_type";
        $ent = $this->db->runQuery($querySelEnt);
        if($ent->num_rows >= 2){
            return true;
        }
        else
        {
    ?>
            <p>Não existem entidades que se possam relacionar.</p>
    <?php
            return false;
        }
    }
    
    /**
     * This method checks if there are already relations in database
     * @return boolean (true if there are relations otherwise it will return false)
     */
    private function checkRelations() {
        $querySelEnt = "SELECT * FROM rel_type";
        $ent = $this->db->runQuery($querySelEnt);
        if($ent->num_rows > 0){
            return true;
        }
        else
        {
    ?>
            <p>Não há tipos de relações.</p>
    <?php
            return false;
        }
    }
    
    /**
     * This method creates the table that presents to the user all the relations type already created
     */
    private function createTable() {
?>
        <form method="GET">
            Verificar propriedades existentes no dia : 
            <input type="text" class="datepicker" id="datepicker" name="data" placeholder="Introduza uma data"> 
            <input type="hidden" name="estado" value="historico">
            <input type="hidden" name="histAll" value="true">
            <input type="submit" value="Apresentar propriedades">
        </form>
            <table id="sortedTable" class="table">
            <thead>
                <tr>
                    <th><span>ID</span></th>
                    <th><span>Nome da Relação</span></th>
                    <th><span>Entidade 1</span></th>
                    <th><span>Entidade 2</span></th>
                    <th><span>Propriedade</span></th>
                    <th><span>Tipo de Valor</span></th>
                    <th><span>Estado</span></th>
                    <th><span>Ação</span></th>
                </tr>
            </thead>
            <tbody>
<?php
            $querySelRel = "SELECT * FROM rel_type";                    
            $resSelRel = $this->db->runQuery($querySelRel);
            while ($rel = $resSelRel->fetch_assoc())
            {
                $selProp =$this->db->runQuery("SELECT * FROM property WHERE state = 'active' AND rel_type_id = ".$rel["id"]);
                $numProp = $selProp->num_rows;
?>
                <tr>    
                    <td rowspan="<?php echo $numProp?>"><?php echo $rel["id"];?></td>
                    <td rowspan="<?php echo $numProp?>"><?php echo $rel["name"];?></td>                    
                    <td rowspan="<?php echo $numProp?>"><?php echo $this->db->getEntityName($rel["ent_type1_id"]);?></td>
                    <td rowspan="<?php echo $numProp?>"><?php echo $this->db->getEntityName($rel["ent_type2_id"]);?></td>
<?php
                $primeiraVez=true;
                if ($numProp == 0) {
?>
                    <td colspan="2">Não existem propriedades associadas a este tipo de relação</td>
<?php
                    if ($rel["state"] === "active")
                    {
?>
                        <td rowspan="<?php echo $numProp?>">Ativo</td>
                        <td rowspan="<?php echo $numProp?>">
                            <a href="?estado=editar&rel_id=<?php echo $rel['id'];?>">[Editar]</a>  
                            <a href="?estado=desativar&rel_id=<?php echo $rel['id'];?>">[Desativar]</a>
                            <a href="?estado=historico&id=<?php echo $rel["id"];?>">[Histórico]</a>
                        </td>
<?php
                    }
                    else
                    {
?>
                        <td rowspan="<?php echo $numProp?>">Inativo</td>
                        <td rowspan="<?php echo $numProp?>">
                            <a href="?estado=editar&rel_id=<?php echo $rel['id'];?>">[Editar]</a>  
                            <a href="?estado=ativar&rel_id=<?php echo $rel['id'];?>">[Ativar]</a>
                            <a href="?estado=historico&id=<?php echo $rel["id"];?>">[Histórico]</a>
                        </td>
<?php
                    }
                }
                while ($prop = $selProp->fetch_assoc()) {
                    if ($primeiraVez) {
                        $primeiraVez = false;
?>                   
                        <td><?php echo $prop["name"];?></td>
                        <td><?php echo $prop["value_type"];?></td>
<?php
                        if ($rel["state"] === "active")
                        {
?>
                            <td rowspan="<?php echo $numProp?>">Ativo</td>
                            <td rowspan="<?php echo $numProp?>">
                                <a href="?estado=editar&rel_id=<?php echo $rel['id'];?>">[Editar]</a>  
                                <a href="?estado=desativar&rel_id=<?php echo $rel['id'];?>">[Desativar]</a>
                                <a href="?estado=historico&id=<?php echo $rel["id"];?>">[Histórico]</a>
                            </td>
<?php
                        }
                        else
                        {
?>
                            <td rowspan="<?php echo $numProp?>">Inativo</td>
                            <td rowspan="<?php echo $numProp?>">
                                <a href="?estado=editar&rel_id=<?php echo $rel['id'];?>">[Editar]</a>  
                                <a href="?estado=ativar&rel_id=<?php echo $rel['id'];?>">[Ativar]</a>
                                <a href="?estado=historico&id=<?php echo $rel["id"];?>">[Histórico]</a>
                            </td>
<?php
                        }
                    }
                    else {
?>                   
                    <tr>
                        <td><?php echo $prop["name"];?></td>
                        <td><?php echo $prop["value_type"];?></td>
                    </tr>
<?php                        
                    }
                }
            }
?>
            </tbody>
        </table>
<?php       
    }
    
    /**
     * This method creates the form that user must fill to insert a new relation type
     */
    private function formInsert() {
?>
        <h3 align="center">Gestão de relações - Introdução </h3>
        <form id="insertRelation" method="POST" align="center" class="tablegr">
            <table>
                <tr>
                    <td><label>Entidade 1</label></td>
                    <td><label>Entidade 2</label></td>
                    <td><label>Nome para o tipo de relação*</label></td>
                </tr>  
                <tr>
                    <td>
                
                    <select id="ent1" name="ent1">
                        <option></option>
<?php
                            $this->getEntities();
?>
                    </select><br>
                    </td>
                    <td>
                    
                        <select id="ent2" name="ent2">
                            <option></option>
<?php
                            $this->getEntities();
?>             
                        </select><br>
                    </td>
                    <td><input type="text" name="nome"><br></td>
                </tr> 
                <tr>
                    <td> <label class="error" for="ent1"></label><br></td>
                    <td> <label class="error" for="ent2"></label><br> </td>
                    <td> <label>* Não é obrigatório</label></td>
                </tr>
            </table>
           
            <br>
            
            <input type="hidden" name="estado" value="inserir"><br>
            <input type="submit" value="Inserir tipo de relação">
        </form>
<?php
    }
    
    /**
     * This method creates the form that user must fill to edit a relation type
     */
    private function formEdit() {
        $queryRelEdit = $this->db->runQuery("SELECT * FROM rel_type WHERE id = ".$_REQUEST["rel_id"]);
        $relEdit = $queryRelEdit->fetch_assoc();
?>
        <h3>Gestão de relações - edição </h3>
        <form id="insertRelation" method="POST">
            <label>Entidade 1</label><br>
            <select id="ent1" name="ent1">
                <option></option>
<?php
                $this->getEntitiesEdit($relEdit["ent_type1_id"]);
?>
            </select><br>
            <label class="error" for="ent1"></label><br>
            <label>Entidade 2</label><br>
            <select id="ent2" name="ent2">
                <option></option>
<?php
                $this->getEntitiesEdit($relEdit["ent_type2_id"]);
?>                
            </select><br>
            <label class="error" for="ent2"></label><br>
            <label>Nome para o tipo de relação</label><br>
            <input type="text" name="nome"><br>
            <input type="hidden" name="estado" value="update"><br>
            <input type="submit" value="Alterar tipo de relação">
        </form>
<?php
    }
    
    /**
     * This method does the PHP-side validation of the forms
     * @return boolean (true if all the data is in correct format)
     */
    private function validarDados() {
        if (empty($_REQUEST["ent1"]) || empty($_REQUEST["ent2"]))
        {
?>
        <p>Deve selecionar uma entidade em ambos os campos!<p>
<?php
            return false;
        }
        if($_REQUEST['estado'] == 'update'&& !$this->checkforChanges()) {
            return false;
        }
        if ($this->verificaRelSemelhante()) {
            return false;
        }
        return true;
    }
    
    /**
     * This method checks if there is already a simetric relation in the databse.
     * For example the relation type Casa-Serviço should be inserted in the 
     * database if there is already a relation type Serviço-Casa
     * @return boolean  
     */
    private function verificaRelSemelhante () {
        $getRel = "SELECT * FROM rel_type WHERE (ent_type1_id = ".$this->db->userInputVal($_REQUEST["ent1"])." AND ent_type2_id = ".$this->db->userInputVal($_REQUEST["ent2"]).") "
                . "OR (ent_type1_id = ".$this->db->userInputVal($_REQUEST["ent2"])." AND ent_type2_id = ".$this->db->userInputVal($_REQUEST["ent1"]).")";
        $getRel = $this->db->runQuery($getRel);
        if ($getRel->num_rows > 0) {
            $rel = $getRel->fetch_assoc();
            if ($rel["name"] == $_REQUEST["nome"]) {
?>
                <p>Já existe um tipo de relação com as entidades selecionadas</p>
<?php
                goBack();
                return true;
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }
    
    /**
     * This method checks if the user did any changes in the state editar
     * @return boolean  
     */
    private function checkforChanges () {
        $getProp = "SELECT * FROM rel_type WHERE id = ".$this->db->userInputVal($_REQUEST["rel_id"]);
        $getProp = $this->db->runQuery($getProp)->fetch_assoc();
        if ($_REQUEST['ent1'] != $getProp["ent_type1_id"]) {
            return true;
        }
        else if ($_REQUEST['ent2'] != $getProp["ent_type2_id"]) {
            return true;
        }
        else if ($_REQUEST['nome'] != $getProp["name"]) {
            return true;
        }
        else {
?>
            <p>Não efetuou qualquer alteração aos valores já existentes.</p><br>
<?php
            goBack();
            return false;
        }
    }
    
    /**
     * This method inserts in the selectboxes all the entities that exists in DB
     */
    private function getEntities() {
        $queryEnt = "SELECT id, name FROM ent_type";
        $resNome = $this->db->runQuery($queryEnt);
        while ($nome = $resNome->fetch_assoc())
        {
?>
            <option value="<?php echo $nome["id"];?>"><?php echo $nome["name"];?></option>
<?php        
        }
    }
    
    /**
     * This method inserts in the selectboxes all the entities that exists in DB
     */
    private function getEntitiesEdit($idSel) {
        $queryEnt = "SELECT id, name FROM ent_type";
        $resNome = $this->db->runQuery($queryEnt);
        while ($nome = $resNome->fetch_assoc())
        {
            if ($idSel == $nome["id"]) {
?>
                <option value="<?php echo $nome["id"];?>" selected="selected"><?php echo $nome["name"];?></option>
<?php        
            }
            else {
?>
                <option value="<?php echo $nome["id"];?>"><?php echo $nome["name"];?></option>
<?php   
            }
        }
    }
}

class RelHist{
  
    /**
     * Constructor method
     */
    public function __construct(){
    }
    
    /**
     * This method is responsible for insert into the history a copy of the property
     * before being updated
     * @param type $db (object form the class Db_Op)
     */
    public function atualizaHistorico ($db) {
        $selectAtributos = "SELECT * FROM rel_type WHERE id = ".$db->userInputVal($_REQUEST['rel_id']);
        $selectAtributos = $db->runQuery($selectAtributos);
        $atributos = $selectAtributos->fetch_assoc();
        $updateHist = "INSERT INTO `hist_rel_type`(`name`,`ent_type1_id`,`ent_type2_id`, `state`, `active_on`,`inactive_on`, `rel_type_id`) "
                . "VALUES ('".$atributos["name"]."','".$atributos["ent_type1_id"]."','".$atributos["ent_type2_id"]."','".$atributos["state"]."','".$atributos["updated_on"]."','".date("Y-m-d H:i:s",time())."',".$db->userInputVal($_REQUEST["rel_id"]).")";
        $updateHist =$db->runQuery($updateHist);
        if(!$updateHist)
        {
            return false;
        }
        return true;
    }
    
    /**
     * This method controls the excution flow when the state is Voltar
     * Basicly he does all the necessary queries to reverse a relation type to an old version
     * saved in the history
     * @param type $db (object form the class Db_Op)
     */
    public function estadoVoltar ($db) {
        $this->atualizaHistorico($db);
        $selectAtributos = "SELECT * FROM hist_rel_type WHERE id = ".$db->userInputVal($_REQUEST['hist']);
        $selectAtributos = $db->runQuery($selectAtributos);
        $atributos = $selectAtributos->fetch_assoc();
        $updateHist = "UPDATE rel_type SET ";
        foreach ($atributos as $atributo => $valor) {
            if ($atributo != "id" && $atributo != "inactive_on" && $atributo != "active_on" && $atributo != "rel_type_id" && !is_null($valor)) {
                $updateHist .= $atributo." = '".$valor."',"; 
            }
        }
        $updateHist .= " updated_on = '".date("Y-m-d H:i:s",time())."' WHERE id = ".$db->userInputVal($_REQUEST['rel_id']);
        $updateHist =$db->runQuery($updateHist);
        if ($updateHist) {
?>
            <p>Atualizou o tipo de relação com sucesso para uma versão anterior.</p>
            <p>Clique em <a href="/gestao-de-relacoes/">Continuar</a> para avançar.</p>
<?php
        }
        else {
?>
            <p>Não foi possível reverter o tipo de relação para a versão selecionada</p>
<?php
            goBack();
        }
    }
    
    /**
     * This method is responsible for the execution flow when the state is Histórico.
     * He starts by presenting a datepicker with options to do a kind of filter of 
     * all the history of the selected relation type.
     * After that he presents a table with all the versions presented in the history
     * @param type $db (object form the class Db_Op)
     */
    public function estadoHistorico ($db) {
        if (isset($_REQUEST["histAll"])) {
            $this->apresentaHistTodas($db);
        }
        else if (empty($_REQUEST["selData"]) || (!empty($_REQUEST["selData"]) && $db->validaDatas($_REQUEST['data']))){
        //meto um datepicker
?>
        <form method="GET">
            Verificar histórico:<br>
            <input type="radio" name="controlDia" value="ate">até ao dia<br>
            <input type="radio" name="controlDia" value="aPartir">a partir do dia<br>
            <input type="radio" name="controlDia" value="dia">no dia<br>
            <input type="text" id="datepicker" name="data" placeholder="Introduza uma data">
            <input type="hidden" name="selData" value="true">
            <input type="hidden" name="estado" value="historico">
            <input type="hidden" name="id" value="<?php echo $_REQUEST["id"]; ?>">
            <input type="submit" value="Apresentar histórico">
        </form>
        <table class="table">
            <thead>
                <tr>
                    <th>Data de Ativação</th>
                    <th>Data de Desativação</th>
                    <th>Nome da Relação</th>
                    <th>Entidade 1</th>
                    <th>Entidade 2</th>
                    <th>Propriedade</th>
                    <th>Tipo de Valor</th>
                    <th>Estado</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
<?php
            $idRel = $db->userInputVal($_REQUEST["id"]);
            if (empty($_REQUEST["data"])) {
                $queryHistorico = "SELECT * FROM hist_rel_type WHERE rel_type_id = ".$idRel." ORDER BY inactive_on DESC";
            }
            else {
                $data = $db->userInputVal($_REQUEST["data"]);
                if (isset($_REQUEST["controlDia"]) && $_REQUEST["controlDia"] == "ate") {
                    $queryHistorico = "SELECT * FROM hist_rel_type WHERE rel_type_id = ".$idRel." AND inactive_on <= '".$data."' ORDER BY inactive_on DESC";
                }
                else if (isset($_REQUEST["controlDia"]) && $_REQUEST["controlDia"] == "aPartir") {
                    $queryHistorico = "SELECT * FROM hist_rel_type WHERE rel_type_id = ".$idRel." AND inactive_on >= '".$data."' ORDER BY inactive_on DESC";
                }
                else if (isset($_REQUEST["controlDia"]) && $_REQUEST["controlDia"] == "dia"){
                    $queryHistorico = "SELECT * FROM hist_rel_type WHERE rel_type_id = ".$idRel." AND inactive_on < '".date("Y-m-d",(strtotime($data) + 86400))."' AND inactive_on >= '".$data."' ORDER BY inactive_on DESC";
                }
                else {
                    $queryHistorico = "SELECT * FROM hist_rel_type WHERE rel_type_id = ".$idRel." AND inactive_on < '".date("Y-m-d",(strtotime($data) + 86400))."' AND inactive_on >= '".$data."' ORDER BY inactive_on DESC";
                }
            }
            $queryHistorico = $db->runQuery($queryHistorico);
            if ($queryHistorico->num_rows == 0) {
?>
                <tr>
                    <td colspan="8">Não existe registo referente ao tipo de relação selecionado no histórico</td>
                    <td><?php goBack(); ?></td>
                </tr>
<?php
            }
            else {
                while ($hist = $queryHistorico->fetch_assoc()) {
                    $selProp =$db->runQuery("SELECT * FROM property WHERE updated_on < '".$hist["inactive_on"]."' AND rel_type_id = ".$idRel);
                    $selPropHist =$db->runQuery("SELECT * FROM hist_property WHERE inactive_on >= '".$hist["inactive_on"]."' AND active_on <= '".$hist["inactive_on"]."' AND rel_type_id = ".$idRel);
                    
                    $numProp = $selProp->num_rows+$selPropHist->num_rows;
                    echo $numProp;
?>
                    <tr>    
                        <td rowspan="<?php echo $numProp?>"><?php echo $hist["active_on"];?></td>
                        <td rowspan="<?php echo $numProp?>"><?php echo $hist["inactive_on"];?></td>
                        <td rowspan="<?php echo $numProp?>"><?php echo $hist["name"];?></td>                    
                        <td rowspan="<?php echo $numProp?>"><?php echo $db->getEntityName($hist["ent_type1_id"]);?></td>
                        <td rowspan="<?php echo $numProp?>"><?php echo $db->getEntityName($hist["ent_type2_id"]);?></td>
<?php
                    $conta=0;
                    if ($numProp == 0) {
?>
                        <td colspan="2">Não existem propriedades associadas a este tipo de relação</td>
<?php 
                        if ($hist["state"] === "active")
                        {
?>
                            <td rowspan="<?php echo $numProp?>">Ativo</td>
                            <td rowspan="<?php echo $numProp?>">
                                <td><a href ="?estado=voltar&hist=<?php echo $hist["id"];?>&rel_id=<?php echo $idRel;?>">Voltar para esta versão</a></td>
                            </td>
                        </tr>
<?php
                        }
                        else
                        {
?>
                            <td rowspan="<?php echo $numProp?>">Inativo</td>
                            <td rowspan="<?php echo $numProp?>">
                                <td><a href ="?estado=voltar&hist=<?php echo $hist["id"];?>&rel_id=<?php echo $idRel;?>">Voltar para esta versão</a></td> 
                            </td>
                        </tr>
<?php
                        }
                    }
                    while ($prop = $selProp->fetch_assoc()) {
                        echo "#1 "."conta ".$conta." prop ".$prop["name"]."<br>";
                        if ($conta = 0) {
?>                   
                            <td><?php echo $prop["name"];?></td>
                            <td><?php echo $prop["value_type"];?></td>
<?php
                            if ($hist["state"] === "active")
                            {
                                echo "#3 "."conta ".$conta." prop ".$prop["name"]."<br>";
?>
                                <td rowspan="<?php echo $numProp?>">Ativo</td>
                                <td rowspan="<?php echo $numProp?>">
                                    <td><a href ="?estado=voltar&hist=<?php echo $hist["id"];?>&rel_id=<?php echo $idRel;?>">Voltar para esta versão</a></td>
                                </td>
                            </tr>
<?php
                            }
                            else
                            {
                                echo "#4 "."conta ".$conta." prop ".$prop["name"]."<br>";
?>
                                <td rowspan="<?php echo $numProp?>">Inativo</td>
                                <td rowspan="<?php echo $numProp?>">
                                    <td><a href ="?estado=voltar&hist=<?php echo $hist["id"];?>&rel_id=<?php echo $idRel;?>">Voltar para esta versão</a></td> 
                                </td>
                            </tr>
<?php
                            }
                        }
                        else {
?>                   
                            <tr>
                                <td><?php echo $prop["name"];?></td>
                                <td><?php echo $prop["value_type"];?></td>
                            </tr>
<?php                        
                        }
                        $conta++;
                    } 
                    while ($prop = $selPropHist->fetch_assoc()) {
                        echo "#2";
                        if ($conta = 0) {
?>                   
                            <td><?php echo $prop["name"];?></td>
                            <td><?php echo $prop["value_type"];?></td>
<?php
                            if ($hist["state"] === "active")
                            {
                                echo "#5 "."conta ".$conta." prop ".$prop["name"]."<br>";
?>
                                <td rowspan="<?php echo $numProp?>">Ativo</td>
                                <td rowspan="<?php echo $numProp?>">
                                    <td><a href ="?estado=voltar&hist=<?php echo $hist["id"];?>&rel_id=<?php echo $idRel;?>">Voltar para esta versão</a></td>
                                </td>
                            </tr>
<?php
                            }
                            else
                            {
                                echo "#6 "."conta ".$conta." prop ".$prop["name"]."<br>";
?>
                                <td rowspan="<?php echo $numProp?>">Inativo</td>
                                <td rowspan="<?php echo $numProp?>">
                                    <td><a href ="?estado=voltar&hist=<?php echo $hist["id"];?>&rel_id=<?php echo $idRel;?>">Voltar para esta versão</a></td> 
                                </td>
                            </tr>
<?php
                            }
                        }
                        else {
?>                   
                            <tr>
                                <td><?php echo $prop["name"];?></td>
                                <td><?php echo $prop["value_type"];?></td>
                            </tr>
<?php                        
                        }
                        $conta++;
                    }
                }
            }
?>
                <tbody>
            </table>
<?php
        }
    }
    
    /**
     * This method creates a table with a view of all the rel_types in the selected day
     * @param type $db (object form the class Db_Op)
     */
    private function apresentaHistTodas ($db) {
        if ($db->validaDatas($_REQUEST['data'])){
?>
        <table id="sortedTable" class="table">
            <thead>
                <tr>
                    <th><span>ID</span></th>
                    <th><span>Nome da Relação</span></th>
                    <th><span>Entidade 1</span></th>
                    <th><span>Entidade 2</span></th>
                    <th><span>Estado</span></th>
                </tr>
            </thead>
            <tbody>
<?php
                $selecionaHist = "SELECT * FROM hist_rel_type WHERE ('".$_REQUEST["data"]."' > active_on AND '".$_REQUEST["data"]."' < inactive_on) OR ((active_on LIKE '".$_REQUEST["data"]."%' AND inactive_on < '".$_REQUEST["data"]."') OR inactive_on LIKE '".$_REQUEST["data"]."%') GROUP BY rel_type_id ORDER BY inactive_on DESC";
                $selecionaRel = "SELECT * FROM rel_type WHERE updated_on < '".$_REQUEST["data"]."' OR updated_on LIKE '".$_REQUEST["data"]."%'";
                
                $resultSelecionaRel = $db->runQuery($selecionaRel);
                $resultSelecionaHist = $db->runQuery($selecionaHist);
?>
                <tr>
<?php
                    // Creates a temporary table with the results of the previous queries, this will be the table that should be printed.
                    $creatTempTable = "CREATE TEMPORARY TABLE temp_table (`id` INT UNSIGNED NOT NULL,
                            `name` VARCHAR(128),
                            `ent_type1_id` INT UNSIGNED NOT NULL,
                            `ent_type2_id` INT UNSIGNED NOT NULL,
                            `state` ENUM('active','inactive') NOT NULL)";
                    $creatTempTable = $db->runQuery($creatTempTable);
                    while ($rel = $resultSelecionaRel->fetch_assoc()) {
                        $db->runQuery("INSERT INTO temp_table VALUES (".$rel['id'].",'".$rel['name']."',".$rel['ent_type1_id'].",".$rel['ent_type2_id'].",'".$rel['state']."')");
                    }
                    while ($hist = $resultSelecionaHist->fetch_assoc()) {
                       $db->runQuery("INSERT INTO temp_table VALUES (".$hist['rel_type_id'].",'".$hist['name']."',".$hist['ent_type1_id'].",".$hist['ent_type2_id'].",'".$hist['state']."')");
                    }
                    
                    $resultSeleciona = $db->runQuery("SELECT * FROM temp_table GROUP BY id ORDER BY id ASC");
                    
                    while($arraySelec = $resultSeleciona->fetch_assoc())
                    {
?>
                        <td><?php echo $arraySelec["id"]; ?></td>
                        <td><?php echo $arraySelec["name"]; ?></td>
                        <td><?php echo $db->getEntityName($arraySelec["ent_type1_id"]); ?></td>
                        <td><?php echo $db->getEntityName($arraySelec["ent_type2_id"]); ?></td>
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
//instantiate a new object from the class RelationManage that is responsible to do all the necessary scripts in this page
new RelationManage();

?>