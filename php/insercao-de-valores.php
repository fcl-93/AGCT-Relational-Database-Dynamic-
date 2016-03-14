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
        <h3>Inserção de valores - escolher entidade/formulário customizado</h3>
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
                    echo'<li><a href="insercao-de-valores?estado=introducao&ent='.$arrayEntity['id'].'">['.$arrayEntity['name'].']</a>';
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
                    echo'<li><a href="insercao-de-valores?estado=introducao&form='.$arrayCustForm['id'].'">['.$arrayCustForm['name'].']</a>';
            }
?>   
            </ul>
            
<?php
        }
    }
    
    /**
     * This method is responsible to control the flow execution when state is "introducao".
     * This method creates the dynamic form created from the proiperties associated to the entiity or form selected before
     */
    private function estadoIntroducao() {
        if (!empty($_REQUEST["ent"]))
        {
            $tipo = "ent";
        }
        else {
            $tipo = "form";
        }
        
        $_SESSION[$tipo."_id"] = $_REQUEST[$tipo];
        
        // need to get the name form the entity or from the form according with the selection form the last state
        if ($tipo === "ent"){
            $queryNome = "SELECT name FROM ent_type WHERE id = ".$_SESSION[$tipo."_id"];
        }
        else{
            $queryNome = "SELECT name FROM custom_form WHERE id = ".$_SESSION[$tipo."_id"];
        }
        $name = $this->db->runQuery($queryNome);
        $_SESSION[$tipo."_name"] = $name->fetch_assoc()["name"];
?>
        <h3>Inserção de valores - <?php echo $_SESSION[$tipo."_name"];?></h3>
        <form name="<?php echo $tipo."_".$_SESSION[$tipo."_id"];?>" action="insercao-de-valores?estado=validar&ent=<?php echo $_SESSION[$tipo."_id"];?>">
<?php
       if ($tipo === "ent"){
           $queryProp = "SELECT * FROM property WHERE ent_type_id = ".$_SESSION[$tipo."_id"]." AND state = 'active' ORDER BY form_field_order ASC";
       }
       else {
           $queryProp = "SELECT * FROM property AS prop, custom_form_has_prop, AS cfhp "
                   . "WHERE cfhp.rel_type_id = ".$_SESSION[$tipo."_id"]."and prop.id = cfhp.property_id AND prop.state = 'active'";
       }
       $execQueryProp = $this->db->runQuery($queryProp);
       while ($arrayProp = $execQueryProp->fetch_assoc())
       {
           if(!is_null($arrayProp["unit_type_id"]))
           {
                $queryUn = "SELECT put.name FROM prop_unit_type AS put WHERE put.id = ".$arrayProp["unit_type_id"];
                $resUn = $this->db->runQuery($queryUn);
                $un = $resUn->fetch_assoc();
           }
           else
           {
               $un["name"] = "";
           }
           
?>
            <label><?php echo $arrayProp["name"];?></label><br>
<?php
            switch ($arrayProp["value_type"])
            {
                case "text":
                    if ($arrayProp["form_field_type"] === "text")
                    {
?>
                        <input type="text" name="<?php echo $arrayProp["form_field_name"];?>"> <?php echo $un["name"];?><br><br>
<?php
                    }
                    else if ($arrayProp["form_field_type"] === "textbox")
                    {
?>
                        <input type="textbox" name="<?php echo $arrayProp["form_field_name"];?>"> <?php echo $un["name"];?><br><br>
<?php
                    }                    
                    break;
                case "bool":
?>
                    <input type="radio" name="<?php echo $arrayProp["form_field_name"];?>" value="true">Sim<br>
                    <input type="radio" name="<?php echo $arrayProp["form_field_name"];?>" value="false">Não<br><br>
<?php                    
                    break;
                case "int":
                case "double":
?>
                    <input type="text" name="<?php echo $arrayProp["form_field_name"];?>"> <?php echo $un["name"];?><br><br>
<?php
                    break;
                case "enum":
                    $querySelVal = "SELECT * FROM prop_allowed_value WHERE state = 'active' AND property_id = ".$arrayProp["id"];
                    $relSelVal = $this->db->runQuery($querySelVal);
                    if ($arrayProp["form_field_type"] === "selectbox")
                    {
?>
                        <select name="<?php echo $arrayProp["form_field_name"];?>">
<?php
                    }
                    while ($allowVal = $relSelVal->fetch_assoc())
                    {
                        if ($arrayProp["form_field_type"] === "radio")
                        {
?>
                            <input type="radio" name="<?php echo $arrayProp["form_field_name"];?>" value="<?php echo $allowVal["value"];?>"><?php echo $allowVal["value"];?> <?php echo $un["name"];?><br>
<?php
                        }
                        else if ($arrayProp["form_field_type"] === "checkbox")
                        {
?>
                            <input type="checkbox" name="<?php echo $arrayProp["form_field_name"];?>" value="<?php echo $allowVal["value"];?>"><?php echo $allowVal["value"];?> <?php echo $un["name"];?><br>
<?php
                        }
                        else if ($arrayProp["form_field_type"] === "selectbox")
                        {
?>
                            <option value="<?php echo $allowVal["value"];?>"><?php echo $allowVal["value"];?></option> <?php echo $un["name"];?>
<?php
                        }
                    }
                    if ($arrayProp["form_field_type"] === "selectbox")
                    {
?>
                        </select>
<?php
                    }
                    echo '<br><br>';
                    break;
                case "ent_ref":
                    break;
                default :
                    break;
            }
       }
?>
            <!-- <label>Nome para instância da entidade</label><br>
            <input type="text" name="nomeInst"><br><br> -->
            <input hidden="hidden" name="estado" value="validar">
            <input type="submit" value="Submeter">           
        </form>
<?php  
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
// instantiation of an object from the class PropertyManage. This instantiation is responsable to get the script work as expected.
new InsertValues();