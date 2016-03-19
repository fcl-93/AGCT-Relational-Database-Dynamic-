<?php
require_once("custom/php/common.php");

/**
 * Class that handle all the methods that are necessary to execute this component
 */
class ImportValues{
    private $db;            // Object from DB_Op that contains the access to the database
    private $capability;    // Wordpress's Capability for this component

    /**
     * Constructor method
     */
    public function __construct(){
        $this->db = new Db_Op();
        $this->capability = "import_values";
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
            $this->estadoIntroducao();
            
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
        <h3>Importação de valores - escolher entidade/formulário customizado</h3>
<?php
        $entidades = $this->db->runQuery("SELECT * FROM ent_type ORDER BY name ASC");
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
                    echo'<li><a href="importacao-de-valores?estado=introducao&ent='.$arrayEntity['id'].'">['.$arrayEntity['name'].']</a>';
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
            while($arrayCustForm= $executaCustForm->fetch_assoc())
            {
                    //ligação de cada item ao endereço Inserção de Valores
                    echo'<li><a href="importcao-de-valores?estado=introducao&form='.$arrayCustForm['id'].'">['.$arrayCustForm['name'].']</a>';
            }
?>   
            </ul>
            
<?php
        }
    }
    
    private function estadoIntroducao() {
?>
	<table>
            <tr>
<?php
		if(isset($_REQUEST['form']))
		{
                    $selPropQuery = "SELECT p.id FROM property AS p, custom_form AS cf, custom_form_has_property AS cfhp 
                                    WHERE cf.id=".$_REQUEST['form']." AND cf.id = cfhp.custom_form_id AND cfhp.property_id = p.id";
		}
		else
		{
                    $selPropQuery = "SELECT p.id FROM property AS p, ent_type AS e 
                                    WHERE e.id=".$_REQUEST['ent']." AND p.ent_type_id = e.id";
		}
		$selProp = $this->db->runQuery($selPropQuery);
		while($prop = $selProp->fetch_assoc())
		{
                    $selFormFieldNamesQuery = "SELECT value_type, form_field_name FROM property WHERE id = ".$prop['id'];
                    $selFormFieldNames = $this->db->runQuery($selFormFieldNamesQuery);
                    while($formfieldnames = $selFormFieldNames->fetch_assoc())
                    {
                        if($formfieldnames['value_type'] == 'enum')
                        {
                            $querySelfAllowed = "SELECT * FROM prop_allowed_value WHERE property_id = ".$prop['id'];
                            $selfAllowed = $this->db->runQuery($querySelfAllowed);
                            while($linha = $selfAllowed->fetch_assoc())
                            {
?>
                                <td><?php echo $formfieldnames['form_field_name'];?></td>
<?php
                            }
                        }
                        else
                        {
?>
                            <td><?php echo $formfieldnames['form_field_name'];?></td>
<?php
                        }
                    }
		}
?>
            </tr>
            <tr>
<?php
		$selProp = $this->db->runQuery($selPropQuery);
		while($prop = $selProp->fetch_assoc())
		{
                    $selFormFieldNamesQuery = "SELECT value_type, form_field_name FROM property WHERE id = ".$prop['id'];
                    $selFormFieldNames = $this->db->runQuery($selFormFieldNamesQuery);
                    while($formfieldnames = $selFormFieldNames->fetch_assoc())
                    {
                        if($formfieldnames['value_type'] == 'enum')
                        {
                            $querySelfAllowed = "SELECT * FROM prop_allowed_value WHERE property_id = ".$prop['id'];
                            $selfAllowed = $this->db->runQuery($querySelfAllowed);
                            while($linha = $selfAllowed->fetch_assoc($selfAllowed))
                            {
?>
                                <td><?php echo $linha['value'];?></td>	
<?php
                            }
                        }
                        else
                        {
?>
                            <td></td>
<?php
                        }
                    }
		}
?>
            </tr>
	</table>
	
        Caro utilizador,<br>
	Deverá copiar estas linhas para um ficheiro excel e introduzir os valores a importar,sendo que no caso das propriedades enum, 
	deverá constar um 0 quando esse valor permitido não se aplique à instância em causa e um 1 quando esse valor se aplica.<br>

	<form name="import" method="POST" enctype="multipart/form-data">
	    	<input type="file" name="file">
	    	<input type="hidden" name="estado" value="insercao">
	        <input type="submit" name="submit" value="Submeter" />
	</form>
<?php
    }

}
// instantiation of an object from the class ImportValues. This instantiation is responsable to get the script work as expected.
new ImportValues();