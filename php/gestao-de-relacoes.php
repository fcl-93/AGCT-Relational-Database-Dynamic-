<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<script type="text/javascript" src="<?php echo get_bloginfo('wpurl');?>/custom/js/jquery-1.12.1.js"></script> 
		<script type="text/javascript" src="<?php echo get_bloginfo('wpurl');?>/custom/js/jquery.validate.js"></script>
 		<script type="text/javascript" src="<?php echo get_bloginfo('wpurl');?>/custom/js/propertyFormValid.js"></script>
 		<link rel="stylesheet" type="text/css" href="<?php echo get_bloginfo('wpurl');?>/custom/css/screen.css">
                <link rel="stylesheet" type="text/css" href="<?php echo get_bloginfo('wpurl');?>/custom/css/table.css">
	</head>
</html>
<?php
require_once("custom/php/common.php");

/**
 * Class that handle all the methods that are necessary to execute this component
 */
class RelationManage
{
    private $db;            // Object from DB_Op that contains the access to the database
    private $capability;    // Wordpress's Capability for this component

    /**
     * Constructor method
     */
    public function __construct(){
        $this->db = new Db_Op();
        $this->capability = "manage_relations";
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
            $this->validarDados();
            $this->estadoInserir();
        }
        elseif($_REQUEST['estado'] =='editar')
        {
            $this->estadoEditar();
        }
        elseif($_REQUEST['estado'] =='update')
        {
            $this->validarDados();
            $this->estadoUpdate();
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
            $this->checkRelations();
            $this->createTable();
        }
    }
    
    /**
     * This method is responsible to control the flow execution when state is "inserir"
     */
    private function estadoInserir() {
        $queryInsert = "INSERT INTO `rel_type`(`ent_type1_id`, `ent_type2_id`) VALUES (".$_REQUEST["ent1"].",".["ent2"].")";
        $insert = $this->db->runQuery($queryInsert);
        if(!$insert)
        {
            echo "Ocorreu um erro ao intoduzir a nova relação.";
            goBack();
        }
        else
        {
            echo 'Inseriu os dados de nova propriedade com sucesso.';
            echo 'Clique em <a href="/gestao-de-relacoes/">Continuar</a> para avançar.';
        }
    } 
    
    /**
     * This method is responsible to control the flow execution when state is "editar"
     */
    private function estadoEditar() {
        
    }
    
    /**
     * This method is responsible to control the flow execution when state is "update"
     */
    private function estadoUpdate() {
        
    }
    
    /**
     * This method is responsible to control the flow execution when state is "ativar" or "desativar"
     */
    private function estadoAtivarDesativar() {
        
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
        <table id="table">
            <thead>
                <tr>
                    <th><span>ID</span></th>
                    <th><span>Entidade 1</span></th>
                    <th><span>Entidade 2</span></th>
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
                    <td>
                        <a href="gestao-de-relacoes?estado=editar&prop_id=<?php echo $arraySelec['id'];?>">[Editar]</a>  
                        <a href="gestao-de-relacoes?estado=desativar&prop_id=<?php echo $arraySelec['id'];?>">[Desativar]</a>
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
<?php
                $this->getEntities();
?>
            </select><br>
            <label class="error" for="ent1"></label><br>
            <label>Entidade 2</label><br>
            <select id="ent2" name="ent2">
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
        
    }
    
    /**
     * This method does the PHP-side validation of the forms
     * @return boolean (true if all the data is in correct format)
     */
    private function validarDados() {
        if (empty($_REQUEST["ent1"]) || empty($_REQUEST["ent2"]))
        {
            echo '<p>Deve selecionar uma entidade em ambos os campos!<p>';
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
    
    private function getEntities() {
        $queryEnt = "SELECT id, name FROM ent_type WHERE id = ".$id;
        $resNome = $this->db->runQuery($queryEnt);
        while ($nome = $resNome->fetch_assoc())
        {
?>
            <option value="<?php echo $resNome["id"];?>"><?php echo $resNome["name"];?></option>
<?php        
        }
    }
}

new RelationManage();

?>