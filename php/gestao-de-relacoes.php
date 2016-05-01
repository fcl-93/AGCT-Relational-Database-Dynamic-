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
        $queryInsert = "INSERT INTO `rel_type`(`ent_type1_id`, `ent_type2_id`, `updated_on`) VALUES (".$_REQUEST["ent1"].",".$_REQUEST["ent2"].",'".date("Y-m-d H:i:s",time())."')";
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
        $queryUpdate = "UPDATE `rel_type` SET ent_type1_id = ".$_REQUEST["ent1"].", ent_type2_id = ".$_REQUEST["ent2"].",updated_on ='".date("Y-m-d H:i:s",time())."' WHERE id = ".$_REQUEST["rel_id"];
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
        $getNomes = $this->db->runQuery("SELECT * FROM rel_type WHERE id = ".$_REQUEST['rel_id']);
        $nomes = $getNomes->fetch_assoc();
        $idNome1 = $nomes["ent_type1_id"];
        $idNome2 = $nomes["ent_type2_id"];
        $this->gereHist->atualizaHistorico($this->db);
        $queryUpdate = "UPDATE rel_type SET state=";
        if ($_REQUEST["estado"] === "ativar")
        {
            $queryUpdate .= "'active'";
            $estado = "ativada";
        }
        else
        {
            $queryUpdate .= "'inactive'";
            $estado = "desativada";
        }
        $queryUpdate .= ",updated_on ='".date("Y-m-d H:i:s",time())."' WHERE id =".$_REQUEST['rel_id'];
        $this->db->runQuery($queryUpdate);
?>
        <html>
            <p>A relação <?php echo $this->getEntityName($idNome1)."-".$this->getEntityName($idNome2) ?> foi <?php echo $estado ?></p>
            <br>
            <p>Clique em <a href="/gestao-de-relacoes"/>Continuar</a> para avançar</p>
        </html>
<?php 
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
            <table id="sortedTable" class="table">
            <thead>
                <tr>
                    <th><span>ID</span></th>
                    <th><span>Entidade 1</span></th>
                    <th><span>Entidade 2</span></th>
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
?>
                <tr>
                    <td><?php echo $rel["id"];?></td>
                    <td><?php echo $this->getEntityName($rel["ent_type1_id"]);?></td>
                    <td><?php echo $this->getEntityName($rel["ent_type2_id"]);?></td>
<?php
            if ($rel["state"] === "active")
            {
?>
                <td>Ativo</td>
                <td>
                    <a href="?estado=editar&rel_id=<?php echo $rel['id'];?>">[Editar]</a>  
                    <a href="?estado=desativar&rel_id=<?php echo $rel['id'];?>">[Desativar]</a>
                </td>
<?php
            }
            else
            {
?>
                <td>Inativo</td>
                <td>
                    <a href="?estado=editar&rel_id=<?php echo $rel['id'];?>">[Editar]</a>  
                    <a href="?estado=ativar&rel_id=<?php echo $rel['id'];?>">[Ativar]</a>
                </td>
<?php
            }
?>
            </td>
                </tr>
<?php
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
        <h3>Gestão de relações - introdução </h3>
        <form id="insertRelation" method="POST">
            <label>Entidade 1</label><br>
            <select id="ent1" name="ent1">
                <option></option>
<?php
                $this->getEntities();
?>
            </select><br>
            <label class="error" for="ent1"></label><br>
            <label>Entidade 2</label><br>
            <select id="ent2" name="ent2">
                <option></option>
<?php
                $this->getEntities();
?>                
            </select><br>
            <label class="error" for="ent2"></label><br>
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
        $getRel = "SELECT * FROM rel_type WHERE (ent_type1_id = ".$_REQUEST["ent1"]." AND ent_type2_id = ".$_REQUEST["ent2"].") "
                . "OR (ent_type1_id = ".$_REQUEST["ent2"]." AND ent_type2_id = ".$_REQUEST["ent1"].")";
        if ($this->db->runQuery($getRel)->num_rows > 0) {
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
    
    /**
     * This method checks if the user did any changes in the state editar
     * @return boolean  
     */
    private function checkforChanges () {
        $getProp = "SELECT * FROM rel_type WHERE id = ".$_REQUEST["rel_id"];
        $getProp = $this->db->runQuery($getProp)->fetch_assoc();
        if ($_REQUEST['nome'] != $getProp["name"]) {
            return true;
        }
        else if ($_REQUEST['ent1'] != $getProp["ent_type1_id"]) {
            return true;
        }
        else if ($_REQUEST['ent2'] != $getProp["ent_type2_id"]) {
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
     * Method that returns the entity name with the given ID
     * @param int $id of the entity
     * @return string the name od the entity
     */
    private function getEntityName($id) {
        $queryEnt = "SELECT name FROM ent_type WHERE id = ".$id;
        $nome = $this->db->runQuery($queryEnt)->fetch_assoc()["name"];
        return $nome;
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
    
    public function __construct(){
        
    }
    
    public function atualizaHistorico ($bd) {
        $selectAtributos = "SELECT * FROM rel_type WHERE id = ".$_REQUEST['rel_id'];
        $selectAtributos = $bd->runQuery($selectAtributos);
        $atributos = $selectAtributos->fetch_assoc();
        $updateHist = "INSERT INTO `hist_rel_type`(`ent_type1_id`,`ent_type2_id`, `state`, `active_on`,`inactive_on`, `rel_type_id`) "
                . "VALUES ('".$atributos["ent_type1_id"]."','".$atributos["ent_type2_id"]."','".$atributos["state"]."','".$atributos["updated_on"]."','".date("Y-m-d H:i:s",time())."',".$_REQUEST["rel_id"].")";
        $updateHist =$bd->runQuery($updateHist);
    }   
}
//instantiate a new object from the class RelationManage that is responsible to do all the necessary scripts in this page
new RelationManage();

?>