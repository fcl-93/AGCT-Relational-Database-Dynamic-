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
            $_SESSION["tipo"] = "ent";
        }
        else {
            $_SESSION["tipo"] = "form";
        }
        
        $tipo = $_SESSION["tipo"];
        
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
        <form method="POST" name="<?php echo $tipo."_".$_SESSION[$tipo."_id"];?>" action="?estado=validar&ent=<?php echo $_SESSION[$tipo."_id"];?>">
<?php
       if ($tipo === "ent"){
           $queryProp = "SELECT * FROM property WHERE ent_type_id = ".$_SESSION[$tipo."_id"]." AND state = 'active' ORDER BY form_field_order ASC";
       }
       else {
           $queryProp = "SELECT * FROM property AS prop, custom_form_has_prop AS cfhp "
                   . "WHERE cfhp.custom_form_id = ".$_SESSION[$tipo."_id"]." AND prop.id = cfhp.property_id AND prop.state = 'active'";
       }
       $execQueryProp = $this->db->runQuery($queryProp);
       while ($arrayProp = $execQueryProp->fetch_assoc())
       {
           $un = $this->obtemUnidades($arrayProp["unit_type_id"]);
           
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
?>
                    <br><br>
<?php
                    break;
                case "ent_ref":
 ?>
                    <select name="<?php echo $arrayProp['form_field_name'];?>">
<?php
                    //vai buscar todos as referencias a entidades que tem como chave estrangeira uma referenca a outra entidade
                    $selecionaFK = $this->db->runQuery("SELECT `fk_ent_type_id` FROM `property` WHERE ".$_SESSION[$tipo."_id"]." = ent_type_id AND value_type = 'en_ref'");

                    while($FK = $selecionaFK->fetch_assoc())
                    {
                        // vai buscar o id e o nome da instancia do componente que tem uma referencia de outro compoenente
                        $selecionainstancia = $this->db->runQuery("SELECT `id`, `entity_name` FROM `entity` WHERE ent_type_id = ".$FK['fk_ent_type_id']."");

                        //array associativo que guarda o resultado que vem da query 
                        while($nomeinstancia = $selecionainstancia->fetch_assoc())
                        {
                            //criação das opções dinamicas que recebm o nome do componente que vem do array associativo
?>
                        <option value="<?php echo $nomeinstancia['id'];?>"><?php echo $nomeinstancia['entity_name'];?></option>
<?php
                        }
                    }
?>
                    </select></br>
<?php
                    break;
                default :
                    break;
            }
       }
?>
            <label>Nome para instância da entidade</label><br>
            <input type="text" name="nomeInst"><br><br>
            <input hidden="hidden" name="estado" value="validar">
            <input type="submit" value="Submeter">           
        </form>
<?php  
    }
    
    /**
     * This method is responsible to control the flow execution when state is "inserir"
     */
    private function estadoInserir() {
        print_r($_REQUEST);
        $tipo = $_SESSION["tipo"];
?>
        <h3>Inserção de valores - <?php echo $_SESSION[$tipo."_name"] ?> - inserção </h3>
<?php						
        //creation of transaction because we will insert values in more than one tables
        $this->db->getMysqli()->autocommit(false);
        $this->db->getMysqli()->begin_transaction();
        if ($tipo === "form")
        {
            $arrayEntRel = $this->idEntRel($_SESSION[$tipo."_id"]);
            $arrayEnt = $arrayEntRel[0];
            $arrayRel = $arrayEntRel[1];
            foreach ($arrayEnt as $ent) {
                $this->insertEntityValues($ent);
            }
            /*foreach ($arrayRel as $rel) {
                $this->insertRelValues($rel);
            }*/
        }
        else {
            $this->insertEntityValues($_SESSION[$tipo."_id"]);
        }
    }
    
    /*private function insertRelValues($idRel) {
        $queryInsertInst = "INSERT INTO `relation`(`id`, `rel_type_id`) VALUES (NULL,".$idRel.",".$idRel2.")";
        $resInsertInst = $this->db->runQuery($queryInsertInst);
        if(!$resInsertInst) {
            $this->db->getMysqli()->rollback();
?>
            <p>Erro na criação da instância.</p>
<?php				
        }
        else {
            $idEntForm = $this->db->getMysqli()->insert_id;
            $propriedadesEnt = $this->db->runQuery("SELECT * FROM `property` WHERE state = 'active' AND ent_type_id = ".$idEnt);
            if(!$propriedadesEnt) {
                $this->db->getMysqli()->rollback();
?>
                <p>Erro na selação da propriedade.</p>
<?php
            }
            else {

                $sucesso = false;
                while($propriedades = $propriedadesEnt->fetch_assoc())
                {
                    $insertVal = $this->db->runQuery("INSERT INTO `value`(`id`, `entity_id`, `property_id`, `value`, `date`, `time`, `producer`) VALUES (NULL,".$idEntForm.",".$propriedades['id'].",'".$_REQUEST[$propriedades['form_field_name']]."','".date("Y-m-d")."','".date("H:i:s")."','".wp_get_current_user()->user_login."')");

                    if(!$insertVal)
                    {								
                        $this->db->getMysqli()->rollback();
?>
                        <p>Erro na atribuição do valor à propriedade.</p>
<?php
                        $sucesso = false;
                    }
                    else
                    {
                        $this->db->getMysqli()->commit();
                        $sucesso = true;
                    }								
                }
                if($sucesso == true)
                {
?>
                    <p>Inseriu o(s) valor(es) com sucesso.</p></br>
                    <p>Clique em <a href="/insercao-de-valores">Voltar</a> para voltar ao início da inserção de valores e poder escolher outro componente, em <a href="?estado=introducao&<?php echo $tipo;?>=<?php echo $_SESSION[$tipo."_id"];?>">Continuar a inserir valores nesta entidade</a> se quiser continuar a inserir valores ou em <a href="/insercao-de-relacoes?estado=associar&ent=<?php echo $_SESSION[$tipo."_id"];?>">Associar entidades</a>, caso deseje associar a entidade criada, com uma outra já previamente criada.</p>
<?php
                }

                else
                {
?>
                    <p>Lamentamos, mas ocorreu um erro.</p>
<?php
                    goBack();
                }
            }
        }
    
    }
    */
    private function insertEntityValues($idEnt) {
        $queryInsertInst = "INSERT INTO `entity`(`id`, `ent_type_id`, `entity_name`) VALUES (NULL,".$idEnt.", '".$_REQUEST["nomeInst"]."')";
        $resInsertInst = $this->db->runQuery($queryInsertInst);
        if(!$resInsertInst) {
            $this->db->getMysqli()->rollback();
?>
            <p>Erro na criação da instância.</p>
<?php				
        }
        else {
            $idEntForm = $this->db->getMysqli()->insert_id;
            $propriedadesEnt = $this->db->runQuery("SELECT * FROM `property` WHERE state = 'active' AND ent_type_id = ".$idEnt);
            if(!$propriedadesEnt) {
                $this->db->getMysqli()->rollback();
?>
                <p>Erro na selação da propriedade.</p>
<?php
            }
            else {

                $sucesso = false;
                while($propriedades = $propriedadesEnt->fetch_assoc())
                {
                    $insertVal = $this->db->runQuery("INSERT INTO `value`(`id`, `entity_id`, `property_id`, `value`, `date`, `time`, `producer`, `entity_name`) VALUES (NULL,".$idEntForm.",".$propriedades['id'].",'".$_REQUEST[$propriedades['form_field_name']]."','".date("Y-m-d")."','".date("H:i:s")."','".wp_get_current_user()->user_login.",'".$_REQUEST["nomeInst"]."')");

                    if(!$insertVal)
                    {								
                        $this->db->getMysqli()->rollback();
?>
                        <p>Erro na atribuição do valor à propriedade <?php echo $propriedades["name"];?>.</p>
<?php
                        $sucesso = false;
                    }
                    else
                    {
                        $this->db->getMysqli()->commit();
                        $sucesso = true;
                    }								
                }
                if($sucesso == true)
                {
?>
                    <p>Inseriu o(s) valor(es) com sucesso.</p></br>
                    <p>Clique em <a href="/insercao-de-valores">Voltar</a> para voltar ao início da inserção de valores e poder escolher outro componente, em <a href="?estado=introducao&<?php echo $tipo;?>=<?php echo $_SESSION[$tipo."_id"];?>">Continuar a inserir valores nesta entidade</a> se quiser continuar a inserir valores ou em <a href="/insercao-de-relacoes?estado=associar&ent=<?php echo $_SESSION[$tipo."_id"];?>">Associar entidades</a>, caso deseje associar a entidade criada, com uma outra já previamente criada.</p>
<?php
                }

                else
                {
?>
                    <p>Lamentamos, mas ocorreu um erro.</p>
<?php
                    goBack();
                }
            }
        }
    }
    
    /**
     * This method is responsible to control the flow execution when state is "validar"
     */
    private function estadoValidar() {
        $tipo = $_SESSION["tipo"];
?>
        <h3>Inserção de valores - <?php echo $_SESSION[$tipo."_name"];?> - validar</h3>
<?php
        if ($tipo === "ent"){
           $queryProp = "SELECT * FROM property WHERE ent_type_id = ".$_SESSION[$tipo."_id"]." AND state = 'active' ORDER BY form_field_order ASC";
       }
       else {
           $queryProp = "SELECT * FROM property AS prop, custom_form_has_prop AS cfhp "
                   . "WHERE cfhp.custom_form_id = ".$_SESSION[$tipo."_id"]." AND prop.id = cfhp.property_id AND prop.state = 'active' ORDER BY cfhp.field_order ASC";
       }
       $execQueryProp = $this->db->runQuery($queryProp);
       $goBack = false;
       while ($arrayProp = $execQueryProp->fetch_assoc()) {
           if ($arrayProp["mandatory"] == 1  && empty($_REQUEST[$arrayProp["form_field_name"]])){
?>
                <p>O campo <?php echo $arrayProp["name"];?></p> é de preenchimento obrigatório!;
<?php
                goBack();
                $goBack = true;
                break;
           }
           else {
               if (empty ($_REQUEST[$arrayProp["form_field_name"]])) {
                   $propVal = NULL;
               }
               else {
                   $propVal = $this->db->getMysqli()->real_escape_string($_REQUEST[$arrayProp["form_field_name"]]);
               }
               switch ($arrayProp["value_type"]) {
                   case "int":
                       if(ctype_digit($propVal))
                        {
                            $propVal = (int)$propVal;
                            //quando o request tem um int e trata o int,actualiza esse valor com esse valor tratado
                            $_REQUEST[$arrayProp["form_field_name"]] = $propVal;
                        }
                        else
                        {
?>
                            <p>Certifique-se que introduziu um número inteiro no campo <?php echo $arrayProp['name'];?>.</p>
<?php
                            goBack();
                            $goBack = true;
                        }
                       break;
                   case "double":
                       if(is_numeric($propVal))
                        {
                            $propVal = floatval($propVal);
                            //quando o request tem um double e trata o double,actualiza esse valor com esse valor tratado
                            $_REQUEST[$arrayProp["form_field_name"]] = $propVal;
                        }
                        else
                        {
?>
                            <p>Certifique-se que introduziu um valor numérico no campo <?php echo $arrayProp['name'];?>.</p>
<?php
                            goBack();
                            $goBack = true;
                        }
                       break;
                   default:
                        $_REQUEST[$arrayProp["form_field_name"]] = $propVal;
                       break;
               }
               if ($goBack) {
                   break;
               }
           }
           
           $_REQUEST["nomeInst"] = $this->db->getMysqli()->real_escape_string($_REQUEST["nomeInst"]);
           
       }
       
       if (!$goBack) {
?>
            <form method="POST" action="?estado=inserir&<?php echo $tipo;?>=<?php echo $_SESSION[$tipo."_id"]?>">
                <p>Estamos prestes a inserir os dados abaixo na base de dados. Confirma que os dados estão correctos e pretende submeter os mesmos?</p>
                <ul>
                    <li><?php echo $_SESSION[$tipo."_name"];?>
                    <ul>
<?php
            $execQueryProp = $this->db->runQuery($queryProp);
            while ($arrayProp = $execQueryProp->fetch_assoc()) {
                if (is_null($_REQUEST[$arrayProp['form_field_name']])){
                    $valor = "Não introduziu nenhum valor";
                }
                else{
                    $valor = $_REQUEST[$arrayProp['form_field_name']];
                }
                
                $un = $this->obtemUnidades($arrayProp['unit_type_id'])
?>
                        <li>
<?php
                        //imprime o valor que o utilizador introduzio no formulario anterior para cada propriedade
                            echo $arrayProp['name'].": ".$valor." ".$un["name"];
?> 
                            <input type='hidden' name="<?php echo $arrayProp['form_field_name'];?>" value="<?php echo $_REQUEST[$arrayProp['form_field_name']];?>">
                        </li>
<?php
            }
?>
                        <li>Nome para instância da entidade: <?php echo $_REQUEST["nomeInst"];?></li>
                        <input type='hidden' name="nomeInst" value="<?php echo $_REQUEST['nomeInst'];?>">
                    </ul>
                    </li>
                </ul>
               <input type="hidden" name="estado" value="inserir">
               <input type="submit" value="Submeter">
            </form>
<?php
        }  
    }
    
    private function obtemUnidades ($idUnit) {
        if(!is_null($idUnit))
        {
             $queryUn = "SELECT put.name FROM prop_unit_type AS put WHERE put.id = ".$idUnit;
             $resUn = $this->db->runQuery($queryUn);
             $un = $resUn->fetch_assoc();
        }
        else
        {
            $un["name"] = "";
        }
        return $un;
    }
    
    /**
     * Identifies all the entities/relations that are involved in a given form
     * @return an array of arrays with all the enities and all the relations
     */
    private function idEntRel($formId) {
        $guardaEnt = array();
        $guardaRel = array();
        $querySelProp = "SELECT * FROM property AS prop, custom_form_has_prop AS cfhp "
                   . "WHERE cfhp.custom_form_id = ".$formId." AND prop.state = 'active'";
        $resQuerySelProp = $this->db->runQuery($querySelProp);
        while ($prop = $resQuerySelProp->fetch_assoc()) {
            if (empty($prop[rel_type_id])){
                $querySelEnt = "SELECT * FROM ent_type WHERE id = ".$prop["ent_type_id"];
                $resQuerySelEnt = $this->db->runQuery($querySelEnt);
                while ($ent = $resQuerySelEnt->fetch_assoc()) {
                    array_push($guardaEnt, $ent["id"]);
                }    
            }
            else {
                $querySelRel = "SELECT * FROM rel_type WHERE id = ".$prop["rel_type_id"];
                $resQuerySelRel = $this->db->runQuery($querySelRel);
                while ($rel = $resQuerySelRel->fetch_assoc()) {
                    array_push($guardaRel, $rel["id"]);
                }
            }
        }
        return [$guardaEnt,$guardaRel];
    }
    
}
// instantiation of an object from the class PropertyManage. This instantiation is responsable to get the script work as expected.
new InsertValues();