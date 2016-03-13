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
        
    }
    
    /**
     * This method is responsible to control the flow execution when state is "inserir"
     */
    private function estadoInserir() {
        
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
        
    }
    
    /**
     * This method checks if there are already relations in database
     * @return boolean (true if there are relations otherwise it will return false)
     */
    private function checkRelations() {
        
    }
    
    /**
     * This method creates the table that presents to the user all the relations type already created
     */
    private function createTable() {
        
    }
    
    /**
     * This method creates the form that user must fill to insert a new relation type
     */
    private function formInsert() {
        
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
        
    }
}

new RelationManage();

?>