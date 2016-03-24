<?php
require_once("custom/php/common.php");
require_once 'PHPExcel/Classes/PHPExcel.php';
require_once 'PHPExcel/Classes/PHPExcel/Writer/Excel2007.php';
require_once 'PHPExcel/Classes/PHPExcel/IOFactory.php';
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
     * Method that creates the name of the type of relation
     */
    private function getRelName($idEnt1, $idEnt2) {
        $queryEntity1 = "SELECT * FROM `ent_type` WHERE id = ".$idEnt1;
        $queryEntity2 = "SELECT * FROM `ent_type` WHERE id = ".$idEnt2;
        $executaEntity1 = $this->db->runQuery($queryEntity1);
        $executaEntity2 = $this->db->runQuery($queryEntity2);
        $nome1 = $executaEntity1->fetch_assoc()["name"];
        $nome2 = $executaEntity2->fetch_assoc()["name"];
        return $nome1."-".$nome2;
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
        <h3>Importação de valores - escolher entidade/relação/formulário customizado</h3>
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
                    <ul>
<?php            
            
            // get all the entities to list                    
            $queryEntity = "SELECT * FROM `ent_type`";

            $executaEntity = $this->db->runQuery($queryEntity);
            // guarda um array associativo que recebe a informação da query, 
            while($arrayEntity = $executaEntity->fetch_assoc())
            {
?>
                        <li><a href="?estado=introducao&ent=<?php echo $arrayEntity['id'];?>">[<?php echo $arrayEntity['name'];?>]</a>
<?php
            }
?>
            
                    </ul>
             <!--create a list with all the relations-->
                <li>Relação:</li>
                    <ul>
<?php            
            
            // get all the relations to list                    
            $queryRelation = "SELECT * FROM `rel_type`";

            $executaRelation = $this->db->runQuery($queryRelation);
            // guarda um array associativo que recebe a informação da query, 
            while($arraRelm= $executaRelation->fetch_assoc())
            {
?>
                        <li><a href="?estado=introducao&rel=<?php echo $arraRelm['id'];?>">[<?php echo $this->getRelName($arraRelm["ent_type1_id"], $arraRelm["ent_type2_id"]);?>]</a>
<?php
            }
?>   
                    </ul>
                <li>Formulários customizados:</li>
                    <ul>
<?php
            // get all the entities to list                    
            $queryCustForm = "SELECT * FROM `custom_form`";

            $executaCustForm = $this->db->runQuery($queryCustForm);
            // guarda um array associativo que recebe a informação da query, 
            while($arrayCustForm= $executaCustForm->fetch_assoc())
            {
?>
                        <li><a href="?estado=introducao&form=<?php echo $arrayCustForm['id'];?>">[<?php echo $arrayCustForm['name'];?>]</a>
<?php
            }
?>   
                    </ul>
            </ul>
            
<?php
        }
    }
    
    private function estadoIntroducao() {
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("")
                ->setLastModifiedBy("")
                ->setTitle("")
                ->setSubject("")
                ->setDescription("")
                ->setKeywords("")
                ->setCategory("");
    
?>
	<table class = "table">
            <thead>
            <tr>
                <td id="acertaCabecalho"></td>
                
<?php
                $linha = 1;
                $coluna = 'A';
                $valor = "";
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($coluna.$linha, $valor);
                $coluna++;
                if (isset($_REQUEST["ent"])) {
                    $getEntidade = "SELECT * FROM ent_type WHERE id = ".$_REQUEST["ent"];
                    $entidade = $this->db->runQuery($getEntidade)->fetch_assoc();
                    $arrayEntidadesRel[$entidade["id"]] = $entidade["name"];
                    $contaEntRel = 0;
                    $numCol = 0;
                }
                else if (isset($_REQUEST["rel"])) {
                    $getRelacao = "SELECT * FROM rel_type WHERE id = ".$_REQUEST["rel"];
                    $relacao = $this->db->runQuery($getRelacao)->fetch_assoc();
                    $arrayEntidadesRel[$relacao["id"]] = $this->getRelName($relacao["ent_type1_id"],$relacao["ent_type2_id"]);
                    $valor = "Entidade 1";
?>
                    <th><?php echo $valor; ?></th>
<?php
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($coluna.$linha, $valor);
                    $objPHPExcel->getActiveSheet()->getColumnDimension($coluna)->setAutoSize(true);
                    $coluna++;
                    $valor = "Entidade 2";
?>
                    <th><?php echo $valor; ?></th>
<?php
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($coluna.$linha, $valor);
                    $objPHPExcel->getActiveSheet()->getColumnDimension($coluna)->setAutoSize(true);
                    $coluna++;
                    $contaEntRel = 2;
                    $numCol = 2;
                }
                else {
                    $arrayEntidadesRel = $this->idEntRel($_REQUEST["form"])[0];
                    $contaEntRel = 0;
                    $numCol = 0;
                }
                
                foreach ($arrayEntidadesRel as $nome) {
                    $contaEntRel++;
                    $numCol++;
                    if (empty($_REQUEST["rel"])) {
                        $valor = "Nome para instância da entidade ".$nome;
?>
                    <th>Nome para instância da entidade <?php echo $nome; ?></th>
<?php
                    }
                    else {
                        $valor = "Nome para instância da relação ".$nome;
?>
                    <th>Nome para instância da relação <?php echo $nome; ?></th>
<?php
                    }
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($coluna.$linha, $valor);
                    $objPHPExcel->getActiveSheet()->getColumnDimension($coluna)->setAutoSize(true);
                    $coluna++;
                }
		if (isset($_REQUEST['form']))
		{
                    $selPropQuery = "SELECT p.id, p.ent_type_id FROM property AS p, custom_form AS cf, custom_form_has_prop AS cfhp 
                                    WHERE cf.id=".$_REQUEST['form']." AND cf.id = cfhp.custom_form_id AND cfhp.property_id = p.id";
		}
		else if (isset($_REQUEST['ent']))
		{
                    $selPropQuery = "SELECT p.id, p.ent_type_id FROM property AS p, ent_type AS e 
                                    WHERE e.id=".$_REQUEST['ent']." AND p.ent_type_id = e.id";
		}
                else {
                    $selPropQuery = "SELECT p.id, p.rel_type_id FROM property AS p, rel_type AS r 
                                    WHERE r.id=".$_REQUEST['rel']." AND p.rel_type_id = r.id";
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
                            for($num = $selfAllowed->num_rows; $num > 0; $num--)
                            {
                                $valor = $formfieldnames['form_field_name'];
                                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($coluna.$linha, $valor);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($coluna)->setAutoSize(true);
                                $coluna++;
?>
                                <th><?php echo $formfieldnames['form_field_name'];?></th>
<?php
                               $numCol++;
                            }
                        }
                        else
                        {
                            $valor = $formfieldnames['form_field_name'];
                            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($coluna.$linha, $valor);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($coluna)->setAutoSize(true);
                            $coluna++;
?>
                            <th><?php echo $formfieldnames['form_field_name'];?></th>
<?php
                            $numCol++;
                        }
                    }
		}
?>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td id="primCol">Tipo de valor/Valores permitidos</td>
<?php
                $linha++;
                $coluna = 'A';
                $valor = "Tipo de valor/Valores permitidos";
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($coluna.$linha, $valor);
                $objPHPExcel->getActiveSheet()->getColumnDimension($coluna)->setAutoSize(true);
                $coluna++;
                for (;$contaEntRel > 0; $contaEntRel--) {
                    $valor = "";
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($coluna.$linha, $valor);
                    $objPHPExcel->getActiveSheet()->getColumnDimension($coluna)->setAutoSize(true);
                    $coluna++;
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
                            while($valPerm = $selfAllowed->fetch_assoc())
                            {
                                $valor = $valPerm['value'];
                                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($coluna.$linha, $valor);
                                $objPHPExcel->getActiveSheet()->getColumnDimension($coluna)->setAutoSize(true);
                                $coluna++;
?>
                                <td><?php echo $valPerm['value'];?></td>	
<?php
                            }
                        }
                        else
                        {
                            $valor = $formfieldnames['value_type'];
                            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($coluna.$linha, $valor);
                            $objPHPExcel->getActiveSheet()->getColumnDimension($coluna)->setAutoSize(true);
                            $coluna++;
?>
                            <td><?php echo $formfieldnames['value_type'];?></td>
<?php
                        }
                    }
		}
?>
            </tr>
            <tr>
                <td id="primCol">Valores a intoduzir</td>
<?php
                $linha++;
                $coluna = 'A';
                $valor = "Valores a intoduzir";
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($coluna.$linha, $valor);
                $coluna++;
                for (;$numCol > 0; $numCol--) {
?>
                    <td></td>
<?php                    
                }
?>
            </tr>
            </tbody>
	</table>
<?php
	$objPHPExcel->getActiveSheet()->setTitle('ImportValues');


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save(getcwd()."/ImportValues.xlsx");
?>
        <p>Caro utilizador,<br>
	Para introduzir os valores, por favor <a href="/ImportValues.xlsx" target="_blank">Clique aqui</a> para descarregar o ficheiro Excel.<br>
        Os valores devem ser sempre introduzidos a partir da 3ª linha da tabela.<br>
        Cada linha corresponderá a uma inserção na base de dados.<br>
        Nos casos em que a propriedade é enum, deverá colocar um 1 na propriedade pretendida e 0 nas restantes.<br>
        De seguida, deve guardar esse ficheiro e submetê-lo a partir do campo abixo.</p>

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
	else {
            $inputFileName = $_FILES["file"]["tmp_name"];
            $objPHPExcel = PHPExcel_IOFactory::load($inputFileName);

            $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
            $propriedadesExcel = array();
            $valoresPermitidosEnum = array();
            $idEntidade = array();
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
            $this->db->getMysqli()->begin_transaction();
            while($contaLinhas <= count($sheetData)) {
                $i = 0;
                foreach($sheetData[strval($contaLinhas)] as $valores) {
                    echo "iteracao: ".$i." val: ".$valores."<br>";
                    if ($i > 0) {
                        if (isset($_REQUEST["rel"])) {
                            $selEntType = "SELECT ent_type_id FROM entity WHERE id = ".$valores;
                            $selRel = "SELECT * FROM rel_type WHERE id = ".$_REQUEST["rel"];
                            $entity = $this->db->runQuery($selEntType)->fetch_assoc();
                            $relation = $this->db->runQuery($selRel)->fetch_assoc();
                            $ent_type = $entity["ent_type_id"];
                            $ent_type1 = $relation["ent_type1_id"];
                            $selNomeEnt1 = "SELECT * FROM ent_type WHERE id = ".$ent_type1;
                            $ent1 = $this->db->runQuery($selNomeEnt1)->fetch_assoc();
                            $nome1 = $ent1["name"];
                            $ent_type2 = $relation["ent_type2_id"];
                            $selNomeEnt2 = "SELECT * FROM ent_type WHERE id = ".$ent_type2;
                            $ent2 = $this->db->runQuery($selNomeEnt2)->fetch_assoc();
                            $nome2 = $ent2["name"];
                            
                            if ($i == 1) {
                                if (empty($valores))
                                {
?>
                                    <p>O valor introduzido para o campo Entidade 1 não está correto. Certifique-se que introduziu um id correspondente a uma entidade do tipo <?php echo $nome1;?> </p>
<?php
                                    break;
                                }
                                else {
                                    
                                    if ($ent_type != $ent_type1)
                                    {
?>
                                        <p>O valor introduzido para o campo Entidade 1 não está correto. Certifique-se que introduziu um id correspondente a uma entidade do tipo <?php echo $nome1;?> </p>
<?php                                        
                                        break;
                                    }
                                    else {
                                        $entRel1 = $valores;
                                    }
                                }
                            }
                            else if ($i == 2) {
                                if (empty($valores))
                                {
?>
                                    <p>O valor introduzido para o campo Entidade 2 não está correto. Certifique-se que introduziu um id correspondente a uma entidade do tipo <?php echo $nome2;?> </p>
<?php                                    
                                    break;
                                }
                                else {
                                    if ($ent_type != $ent_type2)
                                    {
?>
                                        <p>O valor introduzido para o campo Entidade 2 não está correto. Certifique-se que introduziu um id correspondente a uma entidade do tipo <?php echo $nome2;?> </p>
<?php                                        
                                        break;
                                    }
                                    else {
                                        $entRel2 = $valores;
                                    }
                                }
                            }
                            else {
                                $numEntRel = 1;
                                $idEntidadeRel[0] = $_REQUEST["rel"];
                            }
                        }
                        else if (isset($_REQUEST["ent"]))
                        {
                            $numEntRel = 1;
                            $idEntidadeRel[0] = $_REQUEST["ent"];
                        }
                        else {
                            $entRelId = $this->idEntRel($_REQUEST["form"])[0];
                            $numEntRel = count($entRelId);
                            $k = 0;
                            foreach ($entRelId as $key => $value) {
                                $idEntidadeRel[$k] = $key;
                                $k++;
                            }
                        }
                        if (empty($_REQUEST["rel"]) || $i > 2) {
                            if (empty($_REQUEST["rel"])) {
                                $controlaNotProp = $i - 1;
                            }
                            else {
                                $controlaNotProp = $i - 3;
                            }
                            if ($controlaNotProp < $numEntRel) {
                                $valores = $this->db->getMysqli()->real_escape_string($valores);
                                if (empty($valores)) {
                                    if (empty ($_REQUEST["rel"])) {
                                        $queryInsertInst = "INSERT INTO `entity`(`id`, `ent_type_id`) VALUES (NULL,".$idEntidadeRel[$controlaNotProp].")";
                                    }
                                    else {
                                        $queryInsertInst = "INSERT INTO `relation`(`id`, `rel_type_id`,`entity1_id`, `entity2_id`) VALUES (NULL,".$idEntidadeRel[$controlaNotProp].", ".$entRel1.", ".$entRel2.")";
                                    }
                                }
                                else {
                                    if (empty ($_REQUEST["rel"])) {
                                        $queryInsertInst = "INSERT INTO `entity`(`id`, `ent_type_id`, `entity_name`) VALUES (NULL,".$idEntidadeRel[$controlaNotProp].",'".$valores."')";
                                    }
                                    else {
                                        $queryInsertInst = "INSERT INTO `relation`(`id`, `rel_type_id`, `relation_name`, `entity1_id`, `entity2_id`) VALUES (NULL,".$idEntidadeRel[$controlaNotProp].",'".$valores."', ".$entRel1.", ".$entRel2.")";
                                    }
                                }
                                $queryInsertInst = $this->db->runQuery($queryInsertInst);
                                $idEntRel = $this->db->getMysqli()->insert_id;
                                if(!$queryInsertInst ) {
                                    $sucesso = false;
                                    break;
                                }
                                //$i++;
                            }
                            else {
                                $querySelectProp = "SELECT id, value_type, fk_ent_type_id, ent_type_id, rel_type_id FROM property WHERE form_field_name = '".$propriedadesExcel[$i]."'";
                                $querySelectProp = $this->db->runQuery($querySelectProp);
                                if(!$querySelectProp ) {
                                    $sucesso = false;
                                    break;
                                }
                                while($atrProp = $querySelectProp->fetch_assoc())
                                {
                                    $idProp = $atrProp['id'];
                                    $value_type = $atrProp['value_type'];
                                    $ent_fk_id = $atrProp['fk_ent_type_id'];
                                    $ent_type_id = $atrProp["ent_type_id"];
                                    $rel_type_id = $atrProp["rel_type_id"];
                                }
                                if($value_type != "enum")
                                {
                                    echo "passei 1";
                                    $valores = $this->db->getMysqli()->real_escape_string($valores);
                                    $tipoCorreto = false;
                                    switch($value_type) {
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
                                                $tipoCorreto = true;
                                            }
                                            else
                                            {
?>
                                                <p>O valor introduzido para o campo <?php echo $propriedadesExcel[$i];?> não está correto. Certifique-se que introduziu um valor true ou false</p>
<?php
                                                $tipoCorreto = false;
                                            }
                                            break;
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
                                            else if ($valores == "instPorCriar") {
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
                                        default: 
                                            $tipoCorreto = true;
                                            break;
                                    }
                                    if($tipoCorreto)
                                    {
                                        if (isset($_REQUEST["form"])) {                                    
                                            $querySelUlt = "SELECT * FROM entity WHERE ent_type_id = ".$ent_type_id." ORDER BY id DESC LIMIT 1";
                                            $idEntRel = $this->db->runQuery($querySelUlt)->fetch_assoc()["id"];
                                            if ($valores == "instPorCriar") {
                                                $querySelFK = "SELECT `fk_ent_type_id` FROM `property` WHERE ".$ent_type_id." = ent_type_id AND value_type = 'ent_ref'";
                                                $fk = $this->db->runQuery($querySelFK)->fetch_assoc()["fk_ent_type_id"];
                                                $querySelUltRef = "SELECT * FROM entity WHERE ent_type_id = ".$fk." ORDER BY id DESC LIMIT 1";
                                                $selUltRef = $this->db->runQuery($querySelUltRef);
                                                $ultRef = $selUltRef->fetch_assoc();
                                                $valores = $ultRef["id"];
                                            }
                                        }
                                        if (empty($_REQUEST["rel"])) {
                                            $queryInsertValue = "INSERT INTO `value`(`id`, `entity_id`, `property_id`, `value`, `date`, `time`, `producer`) VALUES (NULL,".$idEntRel.", ".$idProp.",'".$valores."','".date("Y-m-d")."','".date("H:i:s")."','".wp_get_current_user()->user_login."')";
                                        }
                                        else {
                                            $queryInsertValue = "INSERT INTO `value`(`id`, `relation_id`, `property_id`, `value`, `date`, `time`, `producer`) VALUES (NULL,".$idEntRel.", ".$idProp.",'".$valores."','".date("Y-m-d")."','".date("H:i:s")."','".wp_get_current_user()->user_login."')";
                                        }
                                        echo $queryInsertValue;
                                        $queryInsertValue = $this->db->runQuery($queryInsertValue);
                                        if(!$queryInsertValue)
                                        {
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
                                        if (isset($_REQUEST["form"])) {                                            
                                            $querySelectEnt = "SELECT * FROM ent_type WHERE id = ".$ent_type_id;
                                            $idEntType = $this->db->runQuery($querySelectEnt)->fetch_assoc()["id"];
                                            $querySelUlt = "SELECT * FROM entity WHERE ent_type_id = ".$idEntType." ORDER BY id DESC LIMIT 1";
                                            $idEntRel = $this->db->runQuery($querySelUlt)->fetch_assoc()["id"];
                                        }
                                        if (empty ($_REQUEST["rel"])) {
                                            $checkValue = "SELECT * FROM value WHERE entity_id = ".$idEntRel." AND property_id = ".$idProp;
                                        }
                                        else {
                                            $checkValue = "SELECT * FROM value WHERE relation_id = ".$idEntRel." AND property_id = ".$idProp;
                                        }
                                        $checkValue = $this->db->runQuery($checkValue);
                                        $checkValue = $checkValue->num_rows;
                                        if ($checkValue > 0) {
    ?>
                                            <p>Só pode atribuir um valor na propriedade enum <?php echo $propriedadesExcel[$i];?> </p>
    <?php                                           
                                            $sucesso = false;
                                            break;
                                        }
                                        if (empty ($_REQUEST["rel"])) {
                                            $queryInsertValue = "INSERT INTO `value`(`id`, `entity_id`, `property_id`, `value`, `date`, `time`, `producer`) VALUES (NULL,".$idEntRel.", ".$idProp.",'".$valoresPermitidosEnum[$i]."','".date("Y-m-d")."','".date("H:i:s")."','".wp_get_current_user()->user_login."')";
                                        }
                                        else {
                                            $queryInsertValue = "INSERT INTO `value`(`id`, `relation_id`, `property_id`, `value`, `date`, `time`, `producer`) VALUES (NULL,".$idEntRel.", ".$idProp.",'".$valoresPermitidosEnum[$i]."','".date("Y-m-d")."','".date("H:i:s")."','".wp_get_current_user()->user_login."')";
                                        }
                                        $queryInsertValue = $this->db->runQuery($queryInsertValue);
                                        if(!$queryInsertValue)
                                        {
                                            $sucesso = false;
                                            break;
                                        }
                                        else
                                        {
                                            $sucesso = true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $i++;
                }
                $contaLinhas++;
            }
            if($sucesso)
            {
                $this->db->getMysqli()->commit();
?>
                <p>Os dados foram inseridos com sucesso!</p>
<?php
            }
            else {
                $this->db->getMysqli()->rollback();
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