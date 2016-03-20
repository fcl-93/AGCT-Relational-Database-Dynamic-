<?php
require_once("custom/php/common.php");
include 'PHPExcel/Classes/PHPExcel/IOFactory.php';
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
        elseif($_REQUEST['estado'] =='insercao')
        {
            $this->estadoInsercao();
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
                    echo'<li><a href="?estado=introducao&ent='.$arrayEntity['id'].'">['.$arrayEntity['name'].']</a>';
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
                    echo'<li><a href="?estado=introducao&form='.$arrayCustForm['id'].'">['.$arrayCustForm['name'].']</a>';
            }
?>   
            </ul>
            
<?php
        }
    }
    
    private function estadoIntroducao() {
?>
	<table class = "table">
            <thead>
            <tr>
                
<?php
                if (isset($_REQUEST["ent"])) {
                    $getEntidade = "SELECT * FROM ent_type WHERE id = ".$_REQUEST["ent"];
                    $entidade = $this->db->runQuery($getEntidade)->fetch_assoc();
                    $arrayEntidades[$entidade["id"]]=$entidade["name"];
                }
                else {
                    $arrayEntidades = $this->idEntRel($_REQUEST["form"])[0];
                }
                $contaEntidades = 0;
                foreach ($arrayEntidades as $nome) {
                    $contaEntidades++;
?>
                    <th>Nome para instância da entidade <?php echo $nome; ?></th>
<?php
                }
		if(isset($_REQUEST['form']))
		{
                    $selPropQuery = "SELECT p.id, p.ent_type_id FROM property AS p, custom_form AS cf, custom_form_has_prop AS cfhp 
                                    WHERE cf.id=".$_REQUEST['form']." AND cf.id = cfhp.custom_form_id AND cfhp.property_id = p.id";
		}
		else
		{
                    $selPropQuery = "SELECT p.id, p.ent_type_id FROM property AS p, ent_type AS e 
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
                                <th><?php echo $formfieldnames['form_field_name'];?></th>
<?php
                            }
                        }
                        else
                        {
?>
                            <th><?php echo $formfieldnames['form_field_name'];?></th>
<?php
                        }
                    }
		}
?>
            </tr>
            </thead>
            <tbody>
            <tr>
<?php
                for (;$contaEntidades > 0; $contaEntidades--) {
?>
                    <td></td>
<?php                    
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
            </tbody>
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
    
    private function estadoInsercao() {
        $target_file = $_FILES["file"]["name"];
	$uploadOk = 1;
	$fileType = pathinfo($target_file,PATHINFO_EXTENSION);
	$sucesso = false;

	// Check if file already exists
	if (file_exists($target_file)) {
?>
	    <p>Pedimos desculpa, mas o seu ficheiro não foi carregado!</p>
<?php
	    $uploadOk = 0;
	}
	// Allow certain file formats
	if($fileType != "xls" && $fileType != "xlsx") {
?>
            <p>Apenas são permitidos ficheiros Excel.</p>
	    $uploadOk = 0;
<?php
        }
	// Check if $uploadOk is set to 0 by an error
	if ($uploadOk == 0) {
?>
        <p>Pedimos desculpa, mas o seu ficheiro não foi carregado!</p>
<?php
        // if everything is ok, try to upload file
	} 
	else 
	{
            $inputFileName = $_FILES["file"]["tmp_name"];
            $objPHPExcel = PHPExcel_IOFactory::load($inputFileName);

            $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
            $propriedadesExcel = array();
            $valoresPermitidosEnum = array();
            foreach($sheetData["1"] as $valores )
            {
                array_push($propriedadesExcel, $valores);
            }
            foreach($sheetData["2"] as $valores )
            {
                array_push($valoresPermitidosEnum, $valores);
            }
            $contaLinhas = 3;
            $this->db->getMysqli()->autocommit(false);

            while($contaLinhas <= count($sheetData))
            {
                $i = 0;
                print_r($sheetData[strval($contaLinhas)]);
                foreach($sheetData[strval($contaLinhas)] as $valores)
                {
                    echo "iteracao: ".$i." val: ".$valores."<br>";
                    if(isset($_REQUEST["ent"]))
                    {
                        $numEnt = 1;
                        $idEnt[0] = $_REQUEST["ent"];
                    }
                    else {
                        $entId = $this->idEntRel($_REQUEST["form"])[0];
                        $numEnt = count($idEnt);
                        $k = 0;
                        foreach ($entId as $key => $value) {
                            $idEnt[$k] = $key;
                            $k++;
                        }
                    }
                    if ($i < $numEnt)
                    {
                        $valores = $this->db->getMysqli()->real_escape_string($valores);
                        $this->db->getMysqli()->begin_transaction();
                        if (empty($valores)) {
                            $queryInsertInst = "INSERT INTO `entity`(`id`, `ent_type_id`) VALUES (NULL,".$idEnt[$i].")";
                        }
                        else {
                            $queryInsertInst = "INSERT INTO `entity`(`id`, `ent_type_id`, `entity_name`) VALUES (NULL,".$idEnt[$i].",'".$valores."')";
                        }
                        $queryInsertInst = $this->db->runQuery($queryInsertInst);
                        $idEnt = $this->db->getMysqli()->insert_id;
                        if(!$queryInsertInst )
                        {
                            $this->db->getMysqli()->rollback();
                            $sucesso = false;
                            break;
                        }
                        $i++;
                    }
                    else {
                        $querySelectProp = "SELECT id, value_type, fk_ent_type_id, ent_type_id FROM property WHERE form_field_name = '".$propriedadesExcel[$i]."'";
                        $querySelectProp = $this->db->runQuery($querySelectProp);
                        if(!$querySelectProp )
                        {
                                $this->db->getMysqli()->rollback();
                                $sucesso = false;
                                break;
                        }
                        while($atrProp = $querySelectProp->fetch_assoc())
                        {
                                $idProp = $atrProp['id'];
                                $value_type = $atrProp['value_type'];
                                $ent_fk_id = $atrProp['fk_ent_type_id'];
                                $ent_type_id = $atrProp["ent_type_id"];
                        }
                        if(empty($valoresPermitidosEnum[$i]))
                        {
                                $valores = $this->db->getMysqli()->real_escape_string($valores);
                                $tipoCorreto = false;
                                switch($value_type)
                                {
                                    case 'int':
                                        if(ctype_digit($valores))
                                        {
                                            $valores = (int)$valores;
                                            $tipoCorreto = true;
                                        }
                                        else
                                        {
?>
                                            <p>O valor introduzido para o campo <?php echo $propriedadesExcel[$i];?> não está correto. Certifique-se que introduziu um valor numérico</p>
<?php
                                            $tipoCorreto = false;
                                        }
                                        break;
                                    case 'double':
                                        if(is_numeric($valores))
                                        {

                                            $valores = floatval($valores);
                                            $tipoCorreto = true;
                                        }
                                        else
                                        {
?>
                                            <p>O valor introduzido para o campo <?php echo $propriedadesExcel[$i]; ?> não está correto. Certifique-se que introduziu um valor numérico</p>
<?php
                                            $tipoCorreto = false;
                                        }
                                        break;
                                    case 'bool':
                                        if($valores == 'true' || $valores == 'false')
                                        {
                                            $valores = boolval($valores);
                                            $tipoCorreto = true;
                                        }
                                        else
                                        {
?>
                                            <p>O valor introduzido para o campo <?php echo $propriedadesExcel[$i];?> não está correto. Certifique-se que introduziu um valor true ou false</p>
<?php
                                        $tipoCorreto = false;
                                        }
                                    case 'ent_ref':
                                        if(is_numeric($valores))
                                        {
                                        // vai buscar o id da instancia da entidade que tem uma referencia de outra entidade
                                            $selecionainstancia = $this->db->runQuery("SELECT `id` FROM `entity` WHERE ent_type_id = ".$ent_fk_id."");

                                            $verificaInst = false;
                                            while($instancia = $selecionainstancia->fetch_assoc())
                                            {
                                                if($instancia['id'] == $valores)
                                                {
                                                    $valores = (int)$valores;
                                                    $tipoCorreto = true;
                                                    $verificaInst = true;
                                                    break;
                                                }									
                                            }
                                            if($verificaInst == false)
                                            {
?>
                                                <p>Não existe nenhuma instância com o id que introduziu no campo <?php echo $propriedadesExcel[$i];?></p>
<?php
                                                $tipoCorreto = false;
                                            }
                                        }
                                        else
                                        {
?>
                                                <p>O valor introduzido para o campo <?php echo $propriedadesExcel[$i];?> não está correto. Certifique-se que introduziu um valor numérico</p>
<?php                                            
                                        $tipoCorreto = false;
                                        }
                                        break;
                                    default: 
                                        $tipoCorreto = true;
                                        break;
                                }
                                if($tipoCorreto)
                                {
                                    if (empty($_REQUEST["ent"])) {                                            
                                        $querySelectEnt = "SELECT * FROM ent_type WHERE id = ".$ent_type_id;
                                        $idEntType = $this->db->runQuery($querySelectEnt)->fetch_assoc()["id"];
                                        $querySelUlt = "SELECT * FROM entity WHERE ent_type_id = ".$idEntType."ORDER BY id DESC LIMIT 1";
                                        $idEnt = $this->db->runQuery($querySelUlt)->fetch_assoc()["id"];
                                    }
                                    $queryInsertValue = "INSERT INTO `value`(`id`, `entity_id`, `property_id`, `value`, `date`, `time`, `producer`) VALUES (NULL,".$idEnt.", ".$idProp.",'".$valores."','".date("Y-m-d")."','".date("H:i:s")."','".wp_get_current_user()->user_login."')";

                                    $queryInsertValue = $this->db->runQuery($queryInsertValue);
                                    if(!$queryInsertValue)
                                    {
                                        $this->db->getMysqli()->rollback();
                                        $sucesso = false;
                                        break;
                                    }
                                    else
                                    {
                                        $sucesso = true;
                                    }
                                }
                                else
                                {
                                    $sucesso = false;
                                    break;
                                }

                        }
                        else
                        {
                            if($valores == 1)
                            {
                                if (empty($_REQUEST["ent"])) {                                            
                                    $querySelectEnt = "SELECT * FROM ent_type WHERE id = ".$ent_type_id;
                                    $idEntType = $this->db->runQuery($querySelectEnt)->fetch_assoc()["id"];
                                    $querySelUlt = "SELECT * FROM entity WHERE ent_type_id = ".$idEntType."ORDER BY id DESC LIMIT 1";
                                    $idEnt = $this->db->runQuery($querySelUlt)->fetch_assoc()["id"];
                                }
                                $queryInsertValue = "INSERT INTO `value`(`id`, `entity_id`, `property_id`, `value`, `date`, `time`, `producer`) VALUES (NULL,".$idEnt.", ".$idProp.",'".$valoresPermitidosEnum[$i]."','".date("Y-m-d")."','".date("H:i:s")."','".wp_get_current_user()->user_login."')";
                                $queryInsertValue = $this->db->runQuery($queryInsertValue);
                                if(!$queryInsertValue)
                                {
                                    $this->db->getMysqli()->rollback();
                                    $sucesso = false;
                                    break;
                                }
                                else
                                {
                                    $sucesso = true;
                                }
                            }
                        }
                        $i++;
                    }
                }
                if($sucesso)
                {
                    $this->db->getMysqli()->commit();
?>
                    <p>Os dados foram inseridos com sucesso!</p>
<?php
                }
                $contaLinhas++;
            }
	}
    }
    
    /**
     * Identifies all the entities/relations that are involved in a given form
     * @return an array of arrays with all the enities and all the relations
     */
    private function idEntRel($formId) {
        $guardaEnt = array();
        $guardaRel = array();
        $querySelProp = "SELECT * FROM property AS prop, custom_form_has_prop AS cfhp "
                   . "WHERE cfhp.custom_form_id = ".$formId." AND prop.state = 'active' AND cfhp.property_id = prop.id "
                . "ORDER BY prop.fk_ent_type_id ASC";
        $resQuerySelProp = $this->db->runQuery($querySelProp);
        while ($prop = $resQuerySelProp->fetch_assoc()) {
            if (empty($prop["rel_type_id"])){
                $querySelEnt = "SELECT * FROM ent_type WHERE id = ".$prop["ent_type_id"];
                $resQuerySelEnt = $this->db->runQuery($querySelEnt);
                while ($ent = $resQuerySelEnt->fetch_assoc()) {
                    $guardaEnt[$ent["id"]] = $ent["name"];
                }    
            }
            else {
                $querySelRel = "SELECT * FROM rel_type WHERE id = ".$prop["rel_type_id"];
                $resQuerySelRel = $this->db->runQuery($querySelRel);
                while ($rel = $resQuerySelRel->fetch_assoc()) {
                   $guardaRel[$rel["id"]] = $rel["id"];
                }
            }
        }
        return [$guardaEnt,$guardaRel];
    }
}
// instantiation of an object from the class ImportValues. This instantiation is responsable to get the script work as expected.
new ImportValues();