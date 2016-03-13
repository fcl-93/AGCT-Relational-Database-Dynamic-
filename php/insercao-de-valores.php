<?php
require_once("custom/php/common.php");

/**
 * Class that handle all the methods that are necessary to execute this component
 */
class InsertValues{
    private $db;            // Object from DB_Op that contains the access to the database
    private $capability;    // Wordpress's Capability for this component

    /**
     * Constructor method
     */
    public function __construct(){
        $this->db = new Db_Op();
        $this->capability = "insert_values";
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
        elseif ($_REQUEST["estado"] === "introducao")
        {
            $this->estadoInserir();
            
        }
        elseif($_REQUEST['estado'] =='validar')
        {
            $this->estadoValidar();
        }
        elseif($_REQUEST['estado'] =='inserir')
        {
            $this->estadoInserir();
        }
    }
    
    /**
     * This method is responsible to control the flow execution when state is empty
     */
    private function estadoEmpty() {
?>
        <h3>Inserção de valores - escolher entidade/formulário customizado</h3>
<?php
        $entidades = $this->db->runQuery("SELECT * FROM component ORDER BY name ASC");
        $row_cnt = $entidades->num_rows;
        //check if there are any entities in the DB
        if($row_cnt == 0)
        {
            echo 'Não pode inserir valores uma vez que ainda não foram introduzidas entidades.';
        }
        else
	{
?>
            <!--create a list with all the entities-->
            <ul>
            <li>Entidade:</li>
            </ul>
<?php            
            
            // get all the entities to list                    
            $queryEntity = "SELECT * FROM `ent_type`";

            $executaEntity = $this->db->runQuery($queryEntity);
            // guarda um array associativo que recebe a informação da query, 
            while($arrayEntity = $executaEntity->fetch_assoc())
            {
                    //ligação de cada item ao endereço Inserção de Valores
                    echo'<li><a href="insercao-de-valores?estado=introducao&comp='.$arrayEntity['id'].'">['.$arrayEntity['name'].']</a>';
            }
?>
            
            </ul>
             <!--create a list with all the entities-->
            <ul>
            <li>Formulários customizados:</li>
            </ul>
<?php            
            
            // get all the entities to list                    
            $queryCustForm = "SELECT * FROM `custom_form`";

            $executaCustForm = $this->db->runQuery($queryCustForm);
            // guarda um array associativo que recebe a informação da query, 
            while($arrayEntity = $executaEntity->fetch_assoc())
            {
                    //ligação de cada item ao endereço Inserção de Valores
                    echo'<li><a href="insercao-de-valores?estado=introducao&form='.$arrayEntity['id'].'">['.$arrayEntity['name'].']</a>';
            }
?>   
            </ul>
            
<?php
        }
    }
    
    /**
     * This method is responsible to control the flow execution when state is "inserir"
     */
    private function estadoInserir() {
        
    }
    
    /**
     * This method is responsible to control the flow execution when state is "validar"
     */
    private function estadoValidar() {
        
    }
    
    
    
}
