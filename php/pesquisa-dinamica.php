<?php
require_once("custom/php/common.php");

$pesquisa = new Search();

class Search{
 
    private $bd;
    private $operators;
    public function __construct()
    {
        $this->bd = new Db_Op();
        $this->operators = operadores();
        $this->checkUser();
    }
    
    public function checkUser(){
        $capability = 'dynamic_search';
	if(is_user_logged_in()){
            if(current_user_can($capability)){ 
                if(empty($_REQUEST['estado'])){
                 
                   $this->tableEmpStt();
                }
                else if($_REQUEST['estado'] == 'escolha'){
                   $this->tableChsStt();
                }
                else if($_REQUEST['estado'] == 'execucao'){
                    $this->estadoExecucao();
                }                
                
                
                
                
            }
            else {
?>
            <html>
                <p>Não tem autorização para a aceder a esta página.</p>
            </html>
<?php                
            }
        }
        else{
?>
            <html>
                <p>O utilizador não tem sessão iniciada.</p>   
                <p>Clique <a href="/login">aqui</a> para iniciar sessão.</p>
            </html>
<?php
        }
    }
    
    /**
     * 
     * 
     */
    private function tableChsStt(){
        if (isset($_REQUEST["ent"])) {
            $_SESSION["tipo"] = "ent";
            $_SESSION["id"] = $_REQUEST["ent"];
        }
        else {
            $_SESSION["tipo"] = "rel";
            $_SESSION["id"] = $_REQUEST["rel"];
        }
        
?>
            <html>
                <form>
<?php
                    $this->showPropEnt();
                    $this->showPropValueType();
                    $this->showRelation();
?>
					<input type="hidden" name="estado" value="execucao">
					<input type="submit" value="Pesquisar">
                </form>
            </html>
<?php
    }
    
    /**
     * This method will print table showing all the relation types and their atributes/properties where there is 
     * one entity equal the one we have choosed
     */
    private function showRelation(){
        $count = $_SESSION['vtPropCount'];
        $res_GetRelType = $this->bd->runQuery("SELECT * FROM rel_type WHERE ent_type1_id =".$this->bd->userInputVal($_REQUEST['ent'])." OR ent_type2_id=".$this->bd->userInputVal($_REQUEST['ent'])."");
        if($res_GetRelType->num_rows == 0)
        {
?>
            <html>
                <h3>Propriedades de relações em que a entidade selecionada está presente.</h3>
                <p>Não existem relações cuja entidade selecionada se encontra presente.</p>
            </html>
<?php
            
        }
        else
        {
?>
            <h3>Propriedades de relações em que a entidade selecionada está presente.</h3>
            <html>
                <table class="table">
                    <thead>
                        <th>Tipo Relação</th>
                        <th>Propriedade da Relação</th>
                        <th>Seleção</th>
                        <th>Valor</th>
                    </thead>
                    <tbody>
<?php
            while($read_GetRelType = $res_GetRelType->fetch_assoc())
            {
                $res_GetRelProps = $this->bd->runQuery("SELECT * FROM property WHERE rel_type_id=".$read_GetRelType['id']);
?>
                <tr>
                    <td rowspan="<?php echo $res_GetRelProps->num_rows?>"><?php echo $this->bd->runQuery("SELECT name FROM ent_type WHERE id =".$read_GetRelType['ent_type1_id'])->fetch_assoc()['name'];?> - <?php echo $this->bd->runQuery("SELECT name FROM ent_type WHERE id =".$read_GetRelType['ent_type2_id'])->fetch_assoc()['name']?></td>
<?php
                   
                    while($read_GetRelProps = $res_GetRelProps->fetch_assoc()){
?>
                        <td><?php echo $read_GetRelProps['name']?></td>                              <!--Id da propriedade da relação-->
                        <td><input type="checkbox" name="checkRL<?php echo $count?>" value="<?php echo $read_GetRelProps['id'] ?>"></td>
                        <td>
<?php                       
                            switch ($read_GetRelProps['value_type']) {
                                case 'enum':
                                    //get enum values if the component valu_type is enum
                                    $res_AlldVal = $this->bd->runQuery("SELECT * FROM prop_allowed_value WHERE prop_allowed_value.property_id = ".$read_GetRelProps['id']." AND prop_allowed_value.state = 'active'");
 ?>
                                    <select name="selectRL<?php echo $count ?>">
<?php
                                        while($read_AlldVal = $res_AlldVal->fetch_assoc()){
?>                                            
                                            <option><?php echo $read_AlldVal['value']; ?></option>
<?php
                                        }
?>                                  </select>
<?php
                                    break;
                                case 'bool':
?>
                                    <input type="radio" name="radioRL<?php echo $count?>" value="true">True
                                    <input type="radio" name="radioRL<?php echo $count?>" value="false">False
<?php
                                            break;
				case 'double':
?>
                                    <select name="operators<?php echo $count?>">
                                        <option> </option> <!--This solves the problem that operatores always where sent in set state-->
<?php
                                        foreach($this->operators as$key=>$value)
                                        {
?>
                                            <option><?php echo $value;?></option>
<?php                                   }
?>
                                    </select>
                                    <input type="text" name="doubleRL<?php echo $count;?>">
<?php                                                
                                    break;
				case 'text':
?>
                                    <input type="text" name="textRL<?php echo $count; ?>">
<?php
                                    break;
				case 'int':
?>
                                    <select name="operators<?php echo $count?>">
					<option> </option> <!--This solves the problem that operatores always where sent in set state-->
<?php					foreach($this->operators as$key=>$value)
                                        {
?>
                                            <option><?php echo $value;?></option>
<?php                                   }
?>
                                    </select>
                                    <input type="text" name="intRL<?php echo $count ?>">
<?php
                                    break;
				case 'ent_ref':
?>  
                                    <input type="hidden" name="ent_refRL" value="<?php echo $read_GetRelProps['id'] ?>">
<?php
                                    break;
                            }
?>
                        </td>
                        
                </tr>

<?php
                        $count++;
                    }
                    $_SESSION['relPropCount'] = $count;
            }
?>
                          
                    </tbody>
                </table>
            </html>
<?php
        }
    }
    
    /**
     * Show a table of entities, where at least the value_type of one o the properties of the selected entity is ent_ref, and fk_ent_type_id 		
     * references the select entity 
     */
    private function showPropValueType(){
        $count = $_SESSION['countPrintedProps'];
        $res_EntRef = $this->bd->runQuery("SELECT ent_type.id, ent_type.name FROM ent_type, property WHERE ent_type.id = property.ent_type_id AND property.value_type = 'ent_ref' AND property.fk_ent_type_id = ".$this->bd->userInputVal($_REQUEST['ent'])."");
    
        if($res_EntRef->num_rows == 0)
	{
?>
            <html>
                <h3>Propriedades de entidades que contenham pelo menos uma propriedade que referêncie a entidade selecionada.</h3>
                <p>Não existem propriedades de entidades que referenciem o tipo de entidade selecionada.</p>
            </html>
<?php                                       
        }
        else
        {
?>
            <h3>Propriedades de entidades que contenham pelo menos uma propriedade que referêncie a entidade selecionada.</h3>
<?php
            while($read_EntRef = $res_EntRef->fetch_assoc())
            {
                
?>              
                <h5>Tipo de Entidade: <?php echo $read_EntRef['name']; ?></h5>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Nome da propriedade</th>
                            <th>Seleção</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
<?php
                        $res_PropRelEnt = $this->bd->runQuery("SELECT * FROM property as p WHERE p.ent_type_id=".$read_EntRef['id']);
                        while($read_PropRelEnt = $res_PropRelEnt->fetch_assoc()){
                            if($read_PropRelEnt['value_type'] != 'ent_ref')
                            {
?>
                        <tr>
                            <td><?php echo  $read_PropRelEnt['id'] ?></td>
                            <td><?php echo $read_PropRelEnt['name']?></td>
                            <td><input type="checkbox" name="checkVT<?php echo $count?>" value="<?php echo $read_PropRelEnt['id'] ?>"></td>
                            <td>
<?php
                                switch ($read_PropRelEnt['value_type']) {
                                    case 'enum':
                                        //get enum values if the component valu_type is enum
                                        $res_AlldVal = $this->bd->runQuery("SELECT * FROM prop_allowed_value WHERE prop_allowed_value.property_id = ".$read_PropRelEnt['id']." AND prop_allowed_value.state = 'active'");
 ?>
                                        <select name="selectVT<?php echo $count ?>">
<?php
                                            while($read_AlldVal = $res_AlldVal->fetch_assoc()){
?>                                            
                                                <option><?php echo $read_AlldVal['value']; ?></option>
<?php
                                            }
?>                                      </select>
<?php
                                        break;
                                    case 'bool':
?>
                                        <input type="radio" name="radioVT<?php echo $count?>" value="true">True
                                        <input type="radio" name="radioVT<?php echo $count?>" value="false">False
<?php
                                            break;
					case 'double':
?>
                                                <select name="operators<?php echo $count?>">
                                                    <option> </option> <!--This solves the problem that operatores always where sent in set state-->
<?php
                                                    foreach($this->operators as$key=>$value)
                                                    {
?>
                                                        <option><?php echo $value;?></option>
<?php                                               }
?>
                                                    </select>
						<input type="text" name="doubleVT<?php echo $count;?>">
<?php                                                
						break;
					case 'text':
?>
                                            <input type="text" name="textVT<?php echo $count; ?>">
<?php
                                            break;
					case 'int':
?>
                                            <select name="operators<?php echo $count?>">
						<option> </option> <!--This solves the problem that operatores always where sent in set state-->
<?php						 foreach($this->operators as$key=>$value)
                                                    {
?>
                                                        <option><?php echo $value;?></option>
<?php                                               }
?>
                                            </select>
                                                    <input type="text" name="intVT<?php echo $count ?>">
<?php
                                                    break;
					case 'ent_ref':
?>
                                                    <input type="hidden" name="ent_refVT" value="<?php echo $read_PropRelEnt['id'] ?>">
<?php
                                            break;
                                    }
                                    $count++;
?>
                                    </td>
                        </tr>
<?php                       
                        }
                        $_SESSION['vtPropCount'] = $count;
                    }
?>
                    </tbody>
                </table>
<?php
            }
?>
<?php
        }
    }
    
    
    
    
    /**
     * Show the properties for the selected entities
     * the properties will be presented in a table
     */
    private function showPropEnt(){
        $count =0;
        $res_GetProp = $this->bd->runQuery("SELECT * FROM property WHERE ent_type_id=".$this->bd->userInputVal($_REQUEST['ent']));
        if($res_GetProp->num_rows == 0)
        {
?>
            <html>
                <p>O tipo de entidade selecionada não tem propriedades.</p>
            </html>

<?php            
        }
        else
        {
?>
                <h3>Lista de propriedades do tipo de entidade selecionada</h3>
                <table class="table">
                    <thead >
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Nome da propriedade</th>
                                <th>Seleção</th>
                                <th>Valor</th>
                            </tr>
                  
                        </thead>
                    <tbody>
<?php
                        while($read_GetProp = $res_GetProp->fetch_assoc()){
?>
                            <tr>
                                <td><?php echo $read_GetProp['id'] ?></td>
                                <td><?php echo $read_GetProp['name']?></td>
                                <td><input type="checkbox" name="checkET<?php echo $count;?>" value="<?php echo $read_GetProp['id']; ?>"></td>
                                <td>
<?php
                                    switch ($read_GetProp['value_type']) {
                                        case 'enum':
                                            //get enum values if the component valu_type is enum
                                            $res_AlldVal = $this->bd->runQuery("SELECT * FROM prop_allowed_value WHERE prop_allowed_value.property_id = ".$read_GetProp['id']." AND prop_allowed_value.state = 'active");
 ?>
                                            <select name="selectET<?php echo $count ?>">
<?php
                                                while($read_AlldVal = $res_AlldVal->fetch_assoc()){
?>                                            
                                                    <option><?php echo $read_AlldVal['value']; ?></option>
<?php
                                                }
?>                                          </select>
<?php
                                            break;
					case 'bool':
?>
                                             <input type="radio" name="radioET<?php echo $count?>" value="true">True
                                             <input type="radio" name="radioET<?php echo $count?>" value="false">False
<?php
                                            break;
					case 'double':
?>
                                                <select name="operators<?php echo $count?>">
                                                    <option> </option> <!--This solves the problem that operatores always where sent in set state-->
<?php
                                                    foreach($this->operators as$key=>$value)
                                                    {
?>
                                                        <option><?php echo $value;?></option>
<?php                                               }
?>
                                                    </select>
						<input type="text" name="doubleET<?php echo $count;?>">
<?php                                                
						break;
					case 'text':
?>
                                            <input type="text" name="textET<?php echo $count; ?>">
<?php
                                            break;
					case 'int':
?>
                                            <select name="operators<?php echo $count?>">
						<option> </option> <!--This solves the problem that operatores always where sent in set state-->
<?php						 foreach($this->operators as$key=>$value)
                                                    {
?>
                                                        <option><?php echo $value;?></option>
<?php                                               }
?>
                                            </select>
                                                    <input type="text" name="intET<?php echo $count ?>">
<?php
                                                    break;
					case 'ent_ref':
?>
                                                    <input type="hidden" name="ent_refET" value="<?php echo $read_GetProp['id'] ?>">
<?php
                                            break;
                                    }
?>
                                    </td>
                            </tr>
<?php
                            $count++;
                        }
                        $_SESSION['countPrintedProps']= $count;
?>
                    </tbody>
                </table>
<?php
        }
    }
    
    /**
     * Prints the table when the state is empty this table will have
     * all the entities type that you can select to make searches
     */
    public function tableEmpStt()
    {
        $res_entTypeLst = $this->bd->runQuery("SELECT * FROM ent_type ORDER BY name ASC");
        if($res_entTypeLst->num_rows == 0){
?>
            <html>
                <h3>Pesquisa Dinâmica - escolher componente</h3>
                <p>Não pode efetuar pesquisas uma vez que ainda não foram introduzidos tipos de entidades.</p>
            </html>
<?php
        }
        else
        {
?>
            <h3>Pesquisa Dinâmica - escolher componente</h3>
<?php
            $res_getEnt = $this->bd->runQuery("SELECT id, name FROM  ent_type"); //get all entities from ent type 
            if($res_getEnt->num_rows == 0)
            {
?>
            <html>
                <p>Não existem tipos de entidades.</p>
            </html>

<?php
            }
            else 
            {
?>
            <ul>
                <li>Entidade:
<?php
                while($read_getEnt = $res_getEnt->fetch_assoc())
                {
                    //need to filter the entities previously selected.
                    $res_FilterEntities = $this->bd->runQuery("SELECT ent_type.name, ent_type.id FROM ent_type INNER JOIN property ON property.fk_ent_type_id = ent_type.id AND ent_type.id = '".$read_getEnt['id']."'");
?>
<?php               
                            while($read_Filter = $res_FilterEntities->fetch_assoc())
                            {
?>          
                                <ul>
                                    <li><a href="pesquisa-dinamica?estado=escolha&ent=<?php echo $read_Filter['id']; ?>">[<?php echo $read_Filter['name']; ?>]</a></li>
                                </ul>
<?php
                            }
                }
?>              </li>
            </ul>
<?php
            }
        }
    }
    
    private function estadoExecucao() {
        $tipo = $_SESSION["tipo"];
        $idEnt = $_SESSION['id']; // vem pelo session é o id da entidade selecionada.
        echo $_SESSION['countPrintedProps']." ". $_SESSION['relPropCount']." ".$_SESSION['vtPropCount'];
        $numeroDechecksImpressos = $_SESSION['relPropCount'];	//numero de checkboxes impressas na pagina anterior == ao numero de propriedades.
        //percorre o request 
        $checkSelected = 0;
        $checkSelectedET = 0;
        $checkSelectedVT = 0;
        $checkSelectedRL = 0;
        $i = 0;
        $guardanomePropSelec = array();
        $guardaValorDaProp = array();
        $guardaidDosSelecionados = array();
        $erro = false;
        while( $i <=  $numeroDechecksImpressos) {
            if(isset($_REQUEST['checkET'.$i])) {
                //significa que foi selecionada
                $checkSelectedET++;
                $checkSelected++;
            }
            else if(isset($_REQUEST['checkVT'.$i])) {
                //significa que foi selecionada
                $checkSelectedVT++;
                $checkSelected++;
            }
            else if(isset($_REQUEST['checkRL'.$i])){
                //significa que foi selecionada
                $checkSelectedRL++;
                $checkSelected++;
            }
            $i++;
        }
        $querydinamica = "SELECT DISTINCT e.id, e.entity_name FROM entity AS e, value AS v WHERE ";
        $controla = 0;
        for($count = 0 ;$count < $numeroDechecksImpressos; $count++ ) {
            echo "count ".$count." controla ".$controla." <br>";
            //CheckBoxes não foram selecionadas
            if(empty($_REQUEST['checkET'.$count]) && empty($_REQUEST['checkVT'.$count]) && empty($_REQUEST['checkRL'.$count])) {
                //significa que não foi selecionado
            }
            //checkboxes selecionadas.
            else {
                if (isset($_REQUEST['checkET'.$count])) {
                    $idDaPropriedade = $_REQUEST['checkET'.$count];
                    $tipo = "ET";
                }
                else if (isset($_REQUEST['checkVT'.$count])) {
                    $idDaPropriedade = $_REQUEST['checkVT'.$count];
                    $tipo = "VT";
                }
                else {
                    $idDaPropriedade = $_REQUEST['checkRL'.$count];
                    $tipo = "RL";
                }
                $queryNomeValProp = "SELECT name, value_type FROM property where id = ".$idDaPropriedade;
                $queryNomeValProp = $this->bd->runQuery($queryNomeValProp);
                $queryNomeValProp = $queryNomeValProp->fetch_assoc();
                $nomeProp = $queryNomeValProp["name"];
                $tipoValor = $queryNomeValProp["value_type"];
                
                if ($checkSelectedVT === 0 && $checkSelectedRL === 0) {
                    $querydinamica = $this->filtros1Tabela($controla,$count,$idDaPropriedade,$guardaidDosSelecionados,$guardanomePropSelec,$nomeProp, $guardaValorDaProp, $tipoValor, $tipo);
                    if ($querydinamica === true) {
                        break;
                    }
                }
                else if ($checkSelectedET === 0 && $checkSelectedRL === 0) {
                    $query1 = $this->filtros2Tabela($controla, $count,$idDaPropriedade,$guardaidDosSelecionados,$guardanomePropSelec,$nomeProp, $guardaValorDaProp, $tipoValor, $tipo);
                    if ($query1 === true) {
                        break;
                    }
                }
                else if ($checkSelectedET === 0 && $checkSelectedVT === 0) {
                    $querydinamica = $this->filtros3Tabela($controla, $count,$idDaPropriedade,$guardaidDosSelecionados,$guardanomePropSelec,$nomeProp, $guardaValorDaProp, $tipoValor, $tipo);
                    if ($querydinamica === true) {
                        break;
                    }
                }
              $controla++;  
            }
        }
        if($checkSelected == 0)
        {
            $querydinamica = "SELECT * FROM entity WHERE ent_type_id = ".$idEnt;
        }
        if ($checkSelectedET === 0 && $checkSelectedRL === 0) {
            echo "entrei aqui 3";
            $conta = 0;
            $guardaEntRef = array();
            while ($entRef = $this->bd->runQuery($query1)->fetch_assoc()) {
                //obtem o id de todas a propriedades ent_ref do tipo de entidade que tem uma referência ao tipo de entidade pretendido
                $query2 = "SELECT id FROM property WHERE fk_ent_type_id = ".$idEnt." AND value_type = 'ent_ref' AND ent_type_id IN (SELECT ent_type_id FROM entity WHERE v.entity_id = '".$entRef["id"]."')";
                $idPropEntRef = $this->bd->runQuery($query2)["id"];
                //obtem o id das entidades que satisfazem a pesquisa
                $query3 = "SELECT v.value FROM property AS p, entity AS e, value AS v WHERE v.property_id = ".$idPropEntRef." AND v.entity_id = ".$entRef["id"]." AND v.property_id = p.id AND e.id = v.entity_id";
                $entidadesComCorrespondencia = $this->bd->runQuery($query3)["id"];
                array_push($guardaEntRef, $entidadesComCorrespondencia);
            }
            foreach ($guardaEntRef as $entidades) {
                if ($count == 0) {
                    $querydinamica .= "e.id IN (";
                }
                else {
                    $querydinamica .= " AND e.id IN (";
                }
                $querydinamica .= "SELECT e.id FROM entity WHERE id = ".$entidades.")";
                $conta++;
            }
        }
        if($erro)
        {
            goBack();
        }
        else {
            echo "check selected: ".$checkSelected." checksimpressos: ".$numeroDechecksImpressos;
            $this->apresentaResultado ($querydinamica);
        }
    }
    
    private function filtro1Tabela($controla, $count,$idDaPropriedade,$guardaidDosSelecionados,$guardanomePropSelec,$nomeProp, $guardaValorDaProp, $tipoValor, $tipo) {
        echo "entrei aqui2";
        $querydinamica = "SELECT e.id, e.name FROM entity AS e, value AS v WHERE ";
        if ($controla == 0) {
            $querydinamica .= "e.id IN (";
        }
        else {
            $querydinamica .= " AND e.id IN (";
        }
        if ($tipoValor == "int") {
            if (validaInt($count, $tipo) === false) {
                return true;
            }
            else {
                $valor = validaInt($count, $tipo);
                $querydinamica .= "SELECT e.id FROM entity AS e, value AS v WHERE v.value".$_REQUEST['operators'.$count]." ".$valor." AND  v.property_id = ".$idDaPropriedade." AND v.entity_id = e.id)";
                preencheArrays ($guardaidDosSelecionados,$idDaPropriedade,$guardanomePropSelec,$nomeProp,$guardaValorDaProp,$valor);
            }
        }
        else if ($tipoValor == "double") {
            if (validaDouble($count, $tipo) === false) {
                return true;
            }
            else {
                $valor = validaDouble($count, $tipo);
                $querydinamica .= "SELECT e.id FROM entity AS e, value AS v WHERE v.value".$_REQUEST['operators'.$count]." ".$valor." AND  v.property_id = ".$idDaPropriedade." AND v.entity_id = e.id)";
                $this->preencheArrays ($guardaidDosSelecionados,$idDaPropriedade,$guardanomePropSelec,$nomeProp,$guardaValorDaProp,$valor);
            }
        }
        else  if ($tipoValor == "text"){
            $valor = $this->bd->userInputVal($_REQUEST['text'.$tipo.$count]);
            $querydinamica .= "SELECT e.id FROM entity AS e, value AS v WHERE v.value = '".$valor."' AND  v.property_id = ".$idDaPropriedade." AND v.entity_id = e.id)";
            $this->preencheArrays ($guardaidDosSelecionados,$idDaPropriedade,$guardanomePropSelec,$nomeProp,$guardaValorDaProp,$valor);
        }
        else  if ($tipoValor == "enum"){
            $valor = $this->bd->userInputVal($_REQUEST['select'.$tipo.$count]);
            $querydinamica .= "SELECT e.id FROM entity AS e, value AS v WHERE v.value = '".$valor."' AND  v.property_id = ".$idDaPropriedade." AND v.entity_id = e.id)";
            $this->preencheArrays ($guardaidDosSelecionados,$idDaPropriedade,$guardanomePropSelec,$nomeProp,$guardaValorDaProp,$valor);
        }
        else  if ($tipoValor == "bool"){
            $valor = $this->bd->userInputVal($_REQUEST['radio'.$tipo.$count]);
            $querydinamica .= "SELECT e.id FROM entity AS e, value AS v WHERE v.value = '".$valor."' AND  v.property_id = ".$idDaPropriedade." AND v.entity_id = e.id";
            $this->preencheArrays ($guardaidDosSelecionados,$idDaPropriedade,$guardanomePropSelec,$nomeProp,$guardaValorDaProp,$valor);
        }
        return $querydinamica;
    }
   
    private function filtros2Tabela($controla, $count,$idDaPropriedade,$guardaidDosSelecionados,$guardanomePropSelec,$nomeProp, $guardaValorDaProp,$tipoValor, $tipo) {
        echo "entrei aqui";
        $query1 = "SELECT e.id, e.name FROM entity AS e, value AS v WHERE ";
        if ($controla == 0) {
            $query1 .= "e.id IN (";
        }
        else {
            $query1 .= " AND e.id IN (";
        }
        if ($tipoValor == "int") {
            if (validaInt($count, $tipo) === false) {
                return true;
            }
            else {
                $valor = validaInt($count, $tipo);
                $query1 .= "SELECT e.id FROM entity AS e, value AS v WHERE v.value".$_REQUEST['operators'.$count]." ".$valor." AND  v.property_id = ".$idDaPropriedade." AND v.entity_id = e.id)";
                preencheArrays ($guardaidDosSelecionados,$idDaPropriedade,$guardanomePropSelec,$nomeProp,$guardaValorDaProp,$valor);
            }
        }
        else if ($tipoValor == "double") {
            if (validaDouble($count, $tipo) === false) {
                return true;
            }
            else {
                $valor = validaDouble($count, $tipo);
                $query1 .= "SELECT e.id FROM entity AS e, value AS v WHERE v.value".$_REQUEST['operators'.$count]." ".$valor." AND  v.property_id = ".$idDaPropriedade." AND v.entity_id = e.id)";
                $this->preencheArrays ($guardaidDosSelecionados,$idDaPropriedade,$guardanomePropSelec,$nomeProp,$guardaValorDaProp,$valor);
            }
        }
        else  if ($tipoValor == "text"){
            $valor = $this->bd->userInputVal($_REQUEST['text'.$tipo.$count]);
            $query1 .= "SELECT e.id FROM entity AS e, value AS v WHERE v.value = '".$valor."' AND  v.property_id = ".$idDaPropriedade." AND v.entity_id = e.id)";
            $this->preencheArrays ($guardaidDosSelecionados,$idDaPropriedade,$guardanomePropSelec,$nomeProp,$guardaValorDaProp,$valor);
        }
        else  if ($tipoValor == "enum"){
            $valor = $this->bd->userInputVal($_REQUEST['select'.$tipo.$count]);
            $query1 .= "SELECT e.id FROM entity AS e, value AS v WHERE v.value = '".$valor."' AND  v.property_id = ".$idDaPropriedade." AND v.entity_id = e.id)";
            $this->preencheArrays ($guardaidDosSelecionados,$idDaPropriedade,$guardanomePropSelec,$nomeProp,$guardaValorDaProp,$valor);
        }
        else  if ($tipoValor == "bool"){
            $valor = $this->bd->userInputVal($_REQUEST['radio'.$tipo.$count]);
            $query1 .= "SELECT e.id FROM entity AS e, value AS v WHERE v.value = '".$valor."' AND  v.property_id = ".$idDaPropriedade." AND v.entity_id = e.id";
            $this->preencheArrays ($guardaidDosSelecionados,$idDaPropriedade,$guardanomePropSelec,$nomeProp,$guardaValorDaProp,$valor);
        }
        return $query1;
    }


    private function validaInt ($count, $tipo) {
        if (verificaOperadores($count)) {
            $int_escaped = mysqli_real_escape_string($link,$_REQUEST['int'.$count.'']);
            if(ctype_digit($int_escaped))
            {	
                    //Se todo o input do user são numeros então converter para inteitro
                    $int_escaped = (int)$int_escaped;
                    if(is_int($int_escaped))
                    {			
                            return $int_escaped;
                    }
                else
                {
?>
                    <p>Verifique se introduziu um valor numérico.</p>
<?php
                    return false;
                }
            }
            else
            {
?>
                <p>Verifique se introduziu um valor numérico.</p>
<?php
                return false;	
            }
        }
        else {
            return false;
        }
    }
    
    private function validaDouble ($count, $tipo) {
        if (verificaOperadores($count)) {
            $double_escaped = $this->bd->userInputVal($_REQUEST['double'.$tipo.$count.'']);
            if(is_numeric($double_escaped))
            {
                $double_escaped = floatval($double_escaped);
                if(is_double ($double_escaped))
                {
                    return $double_escaped;
                }
                else
                {
?>
                    <p>Verifique se introduziu um valor numérico.</p>
<?php
                    return false;
                }
            }
            else
            {
?>
                <p>Verifique se introduziu um valor numérico.</p>
<?php
                return false;	
            }
        }
        else {
            return false;
        }
    }
    
    private function verificaOperadores ($count) {
        if(empty($_REQUEST['operators'.$count]))
        {
?>
            <p>Verifique se introduziu os operadores.</p>
<?php
            return false;
        }
        else {
            return true;
        }
    }
    
    private function preencheArrays ($guardaidDosSelecionados,$idDaPropriedade,$guardanomePropSelec,$nomeProp,$guardaValorDaProp,$valor) {
        array_push($guardaidDosSelecionados,$idDaPropriedade);
        array_push($guardanomePropSelec, $nomeProp);
        array_push($guardaValorDaProp,$valor);
    }
    
    
    private function apresentaResultado ($querydinamica) {
        echo $querydinamica;
        $instEnt = $this->bd->runquery($querydinamica);		
        //imprime a lista de instancias do componente selecionado de acordo com os filtros
?>
        <table class="table">
            <thead>
                <tr>
                    <th>Id</td>
                    <th>Instância</td>
                </tr>
            </thead>
            <tbody>
<?php
        $arrayInstId = array();
        $arrayInstComp = array();
        while($instancias =$instEnt->fetch_assoc()) {
?>
            <tr>
                <td><?php echo $instancias['id'];?></td>
                <td><?php echo $instancias['entity_name'];?></td>
            </tr>	
<?php
            array_push($arrayInstId,$instancias['id']);
            array_push($arrayInstComp,$instancias['entity_name']); 
        }
?>
            </tbody>
        </table>
<?php
    }
}
?>
