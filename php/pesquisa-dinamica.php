<?php
require_once("custom/php/common.php");
require_once("custom/php/exportacao-de-valores.php");

$pesquisa = new Search();

class Search{
 
    private $bd;                         // object from the Db_Op class
    private $operators;                  // array of operators used in dynmic search
    private $guardaidDosSelecionados;   // array that saves the id of the properties selected for the search
    private $guardanomePropSelec;       // array that saves the names of the properties selected for the search
    private $guardaValorDaProp;         // array that value for the properties selected for the search
    private $saveNames;                 // array that value for the properties selected for the search
    private $frase;                     // string with the setence that describes the search made
    
    /**
     * Constructor method
     */
    public function __construct()
    {
        $this->bd = new Db_Op();
        $this->operators = operadores();
        $this->gereInsts = new entityHist();
        $this->guardaidDosSelecionados = array();
        $this->guardaValorDaProp = array();
        $this->guardanomePropSelec = array();
        $this->saveNames = array();
        $this->checkUser();
    }
    
    /**
     * Main method that controls the capability of the current user to acces this component
     * and controls his execution flow 
     */
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
                else if($_REQUEST['estado'] == 'apresentacao'){
                    $this->estadoApresentacao();
                } 
                else if ($_REQUEST['estado'] == 'inactive')
                {
                    $this->changeState();
                }
                else if ($_REQUEST['estado'] == 'active')
                {
                    $this->changeState();
                }
                else if ($_REQUEST['estado'] == 'updateValoresEnt')
                {
                    $this->updatEntVal();
                }
                else if($_REQUEST['estado']=='historico')
                {
                    $this->gereInsts->tableHist($this->bd);
                }
                else if($_REQUEST['estado']=='versionBack')
                {
                    $this->gereInsts->changeVersion($this->bd->userInputVal($_REQUEST['histId']),$this->bd);
                }
                else if($_REQUEST['estado'] == 'novasPropriedadesAdd')
                {
                    $this->addAttrEnt();
                }
                else if($_REQUEST['estado'] == 'desativarVal')
                {
                    $this->desativarVal();
                }
                else if($_REQUEST['estado'] == 'ativarVal')
                {
                    $this->ativarVal();
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
     * This is the form that will allow the user to  make his searchs
     * 
     */
    private function tableChsStt(){
        if (isset($_REQUEST["ent"])) {
            $_SESSION["tipo"] = "ent";
            $_SESSION["id"] = $_REQUEST["ent"];
        }
        else {
            $_SES0SION["tipo"] = "rel";
            $_SESSION["id"] = $_REQUEST["rel"];
        }
        
?>
            <html>
                <form method="POST">
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
     * This method will show all the properties form the entity that is related to the one we have chosed
     * @param type $idDaRel
     */
    private function showPropRelQSRel($idDaRel){
         //print_r($idDaRel);
        if(count($idDaRel) == 0)
        {
?>
            <h3>Entidades que se relacionam com <?php  echo $this->bd->runQuery("SELECT name FROM ent_type WHERE id=".$this->bd->userInputVal($_REQUEST['ent']))->fetch_assoc()['name'];;?></h3>
            <p>Não existem entidades que se relacionem com a entidade selecionada</p>
<?php            
        }
        else
        {
?>
            <html>
                <h3>Entidades que se relacionam com 
<?php
                echo $this->bd->runQuery("SELECT name FROM ent_type WHERE id=".$this->bd->userInputVal($_REQUEST['ent']))->fetch_assoc()['name'];
?>
                </h3>
                <table class="table">
                    <thead>
                        <th>Entidade</th>
                        <th>Propriedade</th>
                        <th>Seleção</th>
                        <th>Valor</th>
                    </thead>
                    <tbody>
<?php
            $count = $_SESSION['relPropCount'];
            $run = 0;
            while($run < count($idDaRel)){
?>
                <tr>
<?php
                    $_resGetIdEnt = $this->bd->runQuery("SELECT * FROM rel_type WHERE id=".$idDaRel[$run]);
                    $_GetIdEnt = $_resGetIdEnt->fetch_assoc();

                    if($_GetIdEnt['ent_type1_id'] == $this->bd->userInputVal($_REQUEST['ent']))
                    {
                        $res_GetRelProps = $this->bd->runQuery("SELECT * FROM property WHERE ent_type_id=".$_GetIdEnt['ent_type2_id']);
                    }
                    else
                    {
                        $res_GetRelProps = $this->bd->runQuery("SELECT * FROM property WHERE ent_type_id=".$_GetIdEnt['ent_type1_id']);
                    }
                    $numProps = $res_GetRelProps->num_rows;
                    $x = $numProps;
                    while($read_GetRelProps = $res_GetRelProps->fetch_assoc()){
                        if($x == $numProps)
                        {
                            $_readName = $this->bd->runQuery("SELECT * FROM ent_type WHERE id = ".$read_GetRelProps['ent_type_id']);
?>
                            <td rowspan="<?php echo $numProps;?>"><?php echo $_readName->fetch_assoc()['name'];?></td>
<?php
                        }
?>
                            <td><?php echo $read_GetRelProps['name']?></td>
                            <td><input type="checkbox" name="checkER<?php echo $count?>" value="<?php echo $read_GetRelProps['id'] ?>"></td>
                            <td>
<?php
                                switch ($read_GetRelProps['value_type']) {
                                    case 'enum':
                                    //get enum values if the component valu_type is enum
                                    $res_AlldVal = $this->bd->runQuery("SELECT * FROM prop_allowed_value WHERE prop_allowed_value.property_id = ".$read_GetRelProps['id']." AND prop_allowed_value.state = 'active'");
 ?>
                                    <select name="selectER<?php echo $count ?>">
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
                                        <input type="radio" name="radioER<?php echo $count?>" value="true">True
                                        <input type="radio" name="radioER<?php echo $count?>" value="false">False
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
<?php                                   
                                            }
?>
                                        </select>
                                        <input type="text" name="doubleER<?php echo $count;?>">
<?php                                                
                                        break;
                                    case 'text':
?>
                                        <input type="text" name="textER<?php echo $count; ?>">
<?php
                                        break;
                                    case 'int':
?>
                                        <select name="operators<?php echo $count?>">
                                            <option> </option> <!--This solves the problem that operatores always where sent in set state-->
<?php                                       foreach($this->operators as$key=>$value){
?>
                                                <option><?php echo $value;?></option>
<?php                                   
                                            }
?>
                                        </select>
                                            <input type="text" name="intER<?php echo $count ?>">
<?php
                                            break;
                                    case 'ent_ref':
 ?>
                                        <select name="ent_refER<?php echo $count ?>">
                                            <option></option>
<?php
                                               
                                                    //vai buscar todos as referencias a entidades que tem como chave estrangeira uma referenca a outra entidade
                                                    $selecionaFK = $this->bd->runQuery("SELECT `fk_ent_type_id` FROM `property` WHERE ".$_REQUEST['ent']." = ent_type_id AND value_type = 'ent_ref' AND ".$read_GetRelProps["id"]." = id");
                                                    while($FK = $selecionaFK->fetch_assoc())
                                                    {

                                                        $nomeEntRef = $this->bd->runQuery("SELECT name FROM ent_type WHERE ".$FK['fk_ent_type_id']." = id")->fetch_assoc()["name"];
                                                        // vai buscar o id e o nome da instancia do componente que tem uma referencia de outro compoenente
                                                        $selecionainstancia = $this->bd->runQuery("SELECT `id`, `entity_name` FROM `entity` WHERE ent_type_id = ".$FK['fk_ent_type_id']."");
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
                            }
?>
                            </td>                              
<?php
                $x--; 
                
            $count++;
?>
                </tr>
<?php
                }
            $run++;    
?>
               
<?php
        }
    }
        $_SESSION['ER'] = $count;
?>
                </tbody>
            </table>
        </html>
<?php
    }
    
    /**
     * This method will print table showing all the relation types and their atributes/properties where there is 
     * one entity equal the one we have choosed
     */
    private function showRelation(){
        $guardaRelTpId =  array();
        $count = $_SESSION['vtPropCount'];
        $res_GetRelType = $this->bd->runQuery("SELECT * FROM rel_type WHERE ent_type1_id =".$this->bd->userInputVal($_REQUEST['ent'])." OR ent_type2_id=".$this->bd->userInputVal($_REQUEST['ent'])."");
        if($res_GetRelType->num_rows == 0)
        {
?>
            <html>
                <h3>Propriedades de relações em que a entidade <?php echo $this->bd->runQuery("SELECT name FROM ent_type WHERE id=".$this->bd->userInputVal($_REQUEST['ent']))->fetch_assoc()['name']; ?> está presente.</h3>
                <p>Não existem relações em que a entidade <?php echo $this->bd->runQuery("SELECT name FROM ent_type WHERE id=".$this->bd->userInputVal($_REQUEST['ent']))->fetch_assoc()['name']; ?> está presente.</p>
            </html>
<?php
            
        }
        else
        {
?>
            <h3>Propriedades de relações em que a entidade <?php echo $this->bd->runQuery("SELECT name FROM ent_type WHERE id=".$this->bd->userInputVal($_REQUEST['ent']))->fetch_assoc()['name'];?> está presente.</h3>
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
                array_push($guardaRelTpId, $read_GetRelType['id']);
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
                                        <select name="ent_refER<?php echo $count ?>">
                                            <option></option>
<?php
                                               
                                                    //vai buscar todos as referencias a entidades que tem como chave estrangeira uma referenca a outra entidade
                                                    $selecionaFK = $this->bd->runQuery("SELECT `fk_ent_type_id` FROM `property` WHERE ".$_REQUEST['ent']." = ent_type_id AND value_type = 'ent_ref' AND ".$read_GetRelProps["id"]." = id");
                                                    while($FK = $selecionaFK->fetch_assoc())
                                                    {

                                                        $nomeEntRef = $this->bd->runQuery("SELECT name FROM ent_type WHERE ".$FK['fk_ent_type_id']." = id")->fetch_assoc()["name"];
                                                        // vai buscar o id e o nome da instancia do componente que tem uma referencia de outro compoenente
                                                        $selecionainstancia = $this->bd->runQuery("SELECT `id`, `entity_name` FROM `entity` WHERE ent_type_id = ".$FK['fk_ent_type_id']."");
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
        $this->showPropRelQSRel($guardaRelTpId);
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
                <h3>Propriedades de entidades que contenham pelo menos uma propriedade que referêncie a entidade <?php echo $this->bd->runQuery("SELECT name FROM ent_type WHERE id=".$this->bd->userInputVal($_REQUEST['ent']))->fetch_assoc()['name']; ?></h3>
                <p>Não existem propriedades de entidades que referenciem a entidade <?php echo $this->bd->runQuery("SELECT name FROM ent_type WHERE id=".$this->bd->userInputVal($_REQUEST['ent']))->fetch_assoc()['name']; ?>.</p>
            </html>
<?php                                       
        }
        else
        {
?>
            <h3>Propriedades de entidades que contenham pelo menos uma propriedade que referêncie a entidade <?php echo $this->bd->runQuery("SELECT name FROM ent_type WHERE id=".$this->bd->userInputVal($_REQUEST['ent']))->fetch_assoc()['name']; ?>.</h3>
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
                                            <select name="ent_refER<?php echo $count ?>">
                                            <option></option>
<?php
                                               
                                                    //vai buscar todos as referencias a entidades que tem como chave estrangeira uma referenca a outra entidade
                                                    $selecionaFK = $this->bd->runQuery("SELECT `fk_ent_type_id` FROM `property` WHERE ".$_REQUEST['ent']." = ent_type_id AND value_type = 'ent_ref' AND ".$read_PropRelEnt["id"]." = id");
                                                    while($FK = $selecionaFK->fetch_assoc())
                                                    {

                                                        $nomeEntRef = $this->bd->runQuery("SELECT name FROM ent_type WHERE ".$FK['fk_ent_type_id']." = id")->fetch_assoc()["name"];
                                                        // vai buscar o id e o nome da instancia do componente que tem uma referencia de outro compoenente
                                                        $selecionainstancia = $this->bd->runQuery("SELECT `id`, `entity_name` FROM `entity` WHERE ent_type_id = ".$FK['fk_ent_type_id']."");
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
                <p>A entidade <?php echo $this->bd->runQuery("SELECT name FROM ent_type WHERE id=".$this->bd->userInputVal($_REQUEST['ent']))->fetch_assoc()['name'];?> não tem propriedades.</p>
            </html>

<?php            
        }
        else
        {
?>
                <h3>Lista de propriedades da entidade <?php echo $this->bd->runQuery("SELECT name FROM ent_type WHERE id=".$this->bd->userInputVal($_REQUEST['ent']))->fetch_assoc()['name']; ?></h3>
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
                                            $res_AlldVal = $this->bd->runQuery("SELECT * FROM prop_allowed_value WHERE prop_allowed_value.property_id = ".$read_GetProp['id']." AND prop_allowed_value.state = 'active'");
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
                                            <select name="ent_refER<?php echo $count ?>">
                                            <option></option>
<?php
                                               
                                                    //vai buscar todos as referencias a entidades que tem como chave estrangeira uma referenca a outra entidade
                                                    $selecionaFK = $this->bd->runQuery("SELECT `fk_ent_type_id` FROM `property` WHERE ".$_REQUEST['ent']." = ent_type_id AND value_type = 'ent_ref' AND ".$read_GetProp["id"]." = id");
                                                    while($FK = $selecionaFK->fetch_assoc())
                                                    {

                                                        $nomeEntRef = $this->bd->runQuery("SELECT name FROM ent_type WHERE ".$FK['fk_ent_type_id']." = id")->fetch_assoc()["name"];
                                                        // vai buscar o id e o nome da instancia do componente que tem uma referencia de outro compoenente
                                                        $selecionainstancia = $this->bd->runQuery("SELECT `id`, `entity_name` FROM `entity` WHERE ent_type_id = ".$FK['fk_ent_type_id']."");
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
            <h3>Pesquisa Dinâmica - escolher tipo de entidade</h3>
<?php
            $res_getEnt = $this->bd->runQuery("SELECT  id, name FROM  ent_type"); //get all entities from ent type 
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
                    //$res_FilterEntities = $this->bd->runQuery("SELECT ent_type.name, ent_type.id FROM ent_type INNER JOIN property ON property.fk_ent_type_id = ent_type.id AND ent_type.id = '".$read_getEnt['id']."'");
?>
<?php               
                            //while($read_Filter = $res_FilterEntities->fetch_assoc())
                            //{
?>          
                                <ul>
                                    <li><a href="pesquisa-dinamica?estado=escolha&ent=<?php echo $read_getEnt['id']; ?>">[<?php echo $read_getEnt['name']; ?>]</a></li>
                                </ul>
<?php
                            //}
                }
?>              </li>
            </ul>
<?php
            }
        }
    }
    
    /**
     * This method controls the state execution.
     * It is on this method that the dynamic query used for the is 
     * created with help of several other methods above.
     * In a simplified way the dynamic query that is generated in this method 
     * is composed by other subqueries that are joined together using the IN clause
     */
    private function estadoExecucao() {
        $tipo = $_SESSION["tipo"];
        $idEnt = $_SESSION['id']; 
        // the number of printed checks equals the $_SESSION['ER'] because the ER retains the number 
        // of printed checks inthe ER table types plus all the previous table types
        $numeroDechecksImpressos = $_SESSION['ER'];
        // control variables count the number of checkboxes selected by type
        $checkSelected = 0;
        $checkSelectedET = 0;
        $checkSelectedVT = 0;
        $checkSelectedRL = 0;
        $checkSelectedER = 0;
        // arrays that retains the id of the refered entities, relations and related 
        // entities wich properties where selected fo the search
        $arrayVT = array();
        $arrayRL = array();
        $arrayER = array();
        //control variables that indicates if any property of the entity or relation was already used
        $vtExiste = false;
        $relExiste = false;
        $i = 0;
        
        // cycles to count the number of checks selected by each category
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
            else if(isset($_REQUEST['checkER'.$i])){
                //significa que foi selecionada
                $checkSelectedER++;
                $checkSelected++;
            }
            $i++;
        }
        $queryEntType = "SELECT name FROM ent_type WHERE id = ".$idEnt;
        $tipoEntidade = $this->bd->runQuery($queryEntType)->fetch_assoc()["name"];
        $this->frase = "Pesquisa de todas as entidades do tipo ".$tipoEntidade;
        
        // headers of all the queries that will be used for do the dynamic search
        $querydinamica = "SELECT DISTINCT e.id FROM entity AS e, value AS v WHERE e.id IN (";
        $cabecalhoQuery = "SELECT DISTINCT e.id FROM entity AS e, value AS v WHERE ";
        $query1Ref = $query1Ent = $query1ER = "SELECT DISTINCT e.id FROM entity AS e, value AS v WHERE ";
        $query1Rel = "SELECT DISTINCT r.id FROM relation AS r WHERE ";
        $primeiraVezET = $primeiraVezVT = $primeiraVezRL = $primeiraVezER = true;
        
        //cycle that grants that all the checks are analysed to see if it was 
        //selected and if true add it to the dynamic search
        for($count = 0 ;$count < $numeroDechecksImpressos; $count++ ) {
            //CheckBoxes não foram selecionadas
            if(empty($_REQUEST['checkET'.$count]) && empty($_REQUEST['checkVT'.$count]) && empty($_REQUEST['checkRL'.$count]) && empty($_REQUEST['checkER'.$count])) {
                // checkbox not selected
            }
            //checkbox selected.
            else {
                //check what type of property it is
                if (isset($_REQUEST['checkET'.$count])) {
                    $idDaPropriedade = $_REQUEST['checkET'.$count];
                    $tipo = "ET";
                }
                else if (isset($_REQUEST['checkVT'.$count])) {
                    $idDaPropriedade = $_REQUEST['checkVT'.$count];
                    $tipo = "VT";
                }
                else if (isset($_REQUEST['checkER'.$count])) {
                    $idDaPropriedade = $_REQUEST['checkER'.$count];
                    $tipo = "ER";
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
                
                // each type of property will have a different treatment
                
                if ($tipo == "ET") {
                    if ($primeiraVezET) {
                        $this->frase .= " cuja propriedade ".$nomeProp." é ";
                    }
                    else {
                        $this->frase .= ", cuja propriedade ".$nomeProp." é ";
                    }
                    $query1Ent = $this->filtro1Tabela($query1Ent, $primeiraVezET, $count,$idDaPropriedade,$nomeProp, $tipoValor, $tipo);
                    $primeiraVezET = false;
                    if ($query1Ent === true) {
                        break;
                    }
                }
                else if ($tipo == "VT") {   
                    $getEntRef = "SELECT e.id, e.name FROM property AS p, ent_type AS e WHERE p.id = ".$idDaPropriedade." AND p.ent_type_id = e.id";
                    $getEntRef = $this->bd->runQuery($getEntRef)->fetch_assoc();
                    
                    // checks if the id of the entity type related to the property in case was already used in the search
                    foreach ($arrayVT as $id) {
                        if ($getEntRef["id"] == $id) {
                            $vtExiste = true;
                            break;
                        }
                        else {
                            $vtExiste = false;
                        }
                    }
                    // if there isn't we add a new kind of substring to the dynamic sentence and add that id to the array
                    if (!$vtExiste) {
                        array_push($arrayVT, $getEntRef["id"]);
                        $this->frase .= " que referencie uma entidade do tipo ".$getEntRef["name"]." cuja propriedade ".$nomeProp." é ";
                    }
                    else {
                        $this->frase .= ", cuja propriedade ".$nomeProp." é ";
                    }
                    $query1Ref = $this->filtros2Tabela($query1Ref, $primeiraVezVT, $count,$idDaPropriedade,$nomeProp, $tipoValor, $tipo);
                    $primeiraVezVT = false;
                    if ($query1Ref === true) {
                        break;
                    }
                }
                else if ($tipo == "RL") {
                    $idRel = "SELECT DISTINCT r.* FROM rel_type AS r, property AS p  WHERE p.id = ".$idDaPropriedade." AND p.rel_type_id = r.id AND r.ent_type1_id = ".$idEnt." OR r.ent_type1_id = ".$idEnt;
                    $rel = $this->bd->runQuery($idRel)->fetch_assoc();
                    $idRel = $rel["id"];
                    
                    // checks if the id of the relation type related to the property in case was already used in the search
                    foreach ($arrayRL as $id) {
                        if ($rel["id"] == $id) {
                            $relExiste = true;
                            break;
                        }
                        else {
                            $relExiste = false;
                        }
                    }
                    // if there isn't we add a new kind of substring to the dynamic sentence and add that id to the array
                    if (!$relExiste) {
                        array_push($arrayRL, $rel["id"]);
                        $ent1 = $rel["ent_type1_id"];
                        $ent2 = $rel["ent_type2_id"];
                        $getEnt1 = "SELECT name FROM ent_type WHERE id = ".$ent1;
                        $getEnt2 = "SELECT name FROM ent_type WHERE id = ".$ent2;
                        $getEnt1 = $this->bd->runQuery($getEnt1)->fetch_assoc()["name"];
                        $getEnt2 = $this->bd->runQuery($getEnt2)->fetch_assoc()["name"];
                        $this->frase .= " que está presente na relação do tipo ".$getEnt1." - ".$getEnt2." cuja propriedade ".$nomeProp." é ";
                    }
                    else {
                        $this->frase .= ", cuja propriedade ".$nomeProp." é ";
                    }
                    $query1Rel = $this->filtros3Tabela($query1Rel, $primeiraVezRL, $count,$idDaPropriedade,$nomeProp,$tipoValor, $tipo);
                    $primeiraVezRL = false;
                    if ($query1Rel === true) {
                        break;
                    }
                }
                else if($tipo == "ER") 
                {
                    $idRel = "SELECT DISTINCT r.* FROM rel_type AS r, property AS p  WHERE p.id = ".$idDaPropriedade." AND p.rel_type_id = r.id AND r.ent_type1_id = ".$idEnt." OR r.ent_type1_id = ".$idEnt;
                    $rel = $this->bd->runQuery($idRel)->fetch_assoc();
                    $idRel = $rel["id"];
                    
                    // checks if the id of the entity type related to the property in case was already used in the search
                    foreach ($arrayER as $id) {
                        if ($rel["id"] == $id) {
                            $relExiste = true;
                            break;
                        }
                        else {
                            $relExiste = false;
                        }
                    }
                    // if there isn't we add a new kind of substring to the dynamic sentence and add that id to the array
                    if (!$relExiste) {
                        array_push($arrayER, $rel["id"]);
                        $ent1 = $rel["ent_type1_id"];
                        $ent2 = $rel["ent_type2_id"];
                        $getEnt1 = "SELECT name FROM ent_type WHERE id = ".$ent1;
                        $getEnt2 = "SELECT name FROM ent_type WHERE id = ".$ent2;
                        $getEnt1 = $this->bd->runQuery($getEnt1)->fetch_assoc()["name"];
                        $getEnt2 = $this->bd->runQuery($getEnt2)->fetch_assoc()["name"];
                        $this->frase .= " que têm uma relação com a entidade do tipo ".$getEnt2." cuja propriedade ".$nomeProp." é ";
                    }
                    else {
                        $this->frase .= ", cuja propriedade ".$nomeProp." é ";
                    }
                    
                    // in this case we don't use a different method becaus in his 
                    // essence what we need to do in first step is the same we did for the second type of filters
                    $query1ER = $this->filtros2Tabela($query1ER, $primeiraVezER, $count,$idDaPropriedade,$nomeProp,$tipoValor, $tipo);
                    $primeiraVezER = false;
                    if ($query1ER === true) {
                        break;
                    }
                
                }
            }
        }
        // if anything was selected all we need to do is to present all the entities of the selected type
        if($checkSelected == 0)
        {
            $querydinamica = "SELECT * FROM entity WHERE ent_type_id = ".$idEnt;
        }
        else {
            $primeiraVez = true;
            // the strlen presented above checks if anything was added to the 
            // 1st subquery during the previous cycle.
            // if it was it means that we need to go to another methods to
            // generated the others subqueries
            if (strlen($query1Ent) > 56 && !$erro) { //56 é o tamanho da query qd esta não é alterada pelos métodos antecessores
                if ($primeiraVez) {
                    $querydinamica .= $query1Ent.")";
                    $primeiraVez = false;
                }
            }
            if (strlen($query1Ref) > 56 && !$erro) { //56 é o tamanho da query qd esta não é alterada pelos métodos antecessores
                if ($primeiraVez) {
                    if ($this->geraQueryTabela2($query1Ref,$idEnt,$cabecalhoQuery) === false) {
                        $erro = true;
                    }
                    else {
                        $querydinamica .= $this->geraQueryTabela2($query1Ref,$idEnt,$cabecalhoQuery).")";
                        $primeiraVez = false;   
                    }
                }
                else {
                    if ($this->geraQueryTabela2($query1Ref,$idEnt,$cabecalhoQuery) === false) {
                        $erro = true;
                    }
                    else {
                        $querydinamica .= " AND e.id IN (".$this->geraQueryTabela2($query1Ref,$idEnt,$cabecalhoQuery).")";
                    }
                }
            }
            if (strlen($query1Rel) > 46 && !$erro) { //46 é o tamanho da query qd esta não é alterada pelos métodos antecessores
                if ($primeiraVez) {
                    if ($this->geraQueryTabela3($query1Ref,$idEnt,$cabecalhoQuery) === false) {
                        $erro = true;
                    }
                    else {
                        $querydinamica .= $this->geraQueryTabela3($query1Rel, $idEnt, $cabecalhoQuery).")";
                        $primeiraVez = false;
                    }
                }
                else {
                    if ($this->geraQueryTabela3($query1Ref,$idEnt,$cabecalhoQuery) === false) {
                        $erro = true;
                    }
                    else {
                        $querydinamica .= " AND e.id IN (".$this->geraQueryTabela3($query1Rel, $idEnt, $cabecalhoQuery).")";
                    }
                }
            }
            if (strlen($query1ER) > 56 && !$erro) { //46 é o tamanho da query qd esta não é alterada pelos métodos antecessores
                if ($primeiraVez) {
                    if ($this->geraQueryTabela4($query1Ref,$idEnt,$cabecalhoQuery) === false) {
                        $erro = true;
                    }
                    else {
                        $querydinamica .= $this->geraQueryTabela4($query1ER, $idEnt, $cabecalhoQuery).")";
                        $primeiraVez = false;
                    }
                }
                else {
                    if ($this->geraQueryTabela4($query1Ref,$idEnt,$cabecalhoQuery) === false) {
                        $erro = true;
                    }
                    else {
                        $querydinamica .= " AND e.id IN (".$this->geraQueryTabela4($query1ER, $idEnt, $cabecalhoQuery).")";
                    }
                }
            }
        }
        if($erro)
        {
?>
            <p><?php echo $this->frase;?></p>
            <p>Não existem entidades que respeitem a pesquisa efetuada.</p>
<?php
            goBack();
        }
        else {
            $this->apresentaResultado ($querydinamica, $checkSelected);
        }
    }
    
    /**
     * 
     * This method creates a query when the user selected on or more properties
     * presented in the table type where we found properties about entities that
     * has references to the selected entity.
     * Before we selected all the refered entities that have the properties selected.
     * In this method we will check if anyone of the results of that query is 
     * refered by one of the entities wich type is the selected
     *
     * @param type $query1 (query that selects all the entities that satisfy the properties selected)
     * @param type $idEnt (the id of the entity type we want to search)
     * @param type $querydinamica (the dynamic query that is alread built at the moment)
     * @return boolean|string (false if there are any errors or non results, string otherwise with an updated version od the dynamic query)
     */
    private function geraQueryTabela2($query1,$idEnt,$querydinamica) {
        $conta = 0;
        $guardaEntRef = array();
        $query1 = $this->bd->runQuery($query1);
        if (!$query1 || $query1->num_rows === 0) {
            return false;
        }
        while ($entRef = $query1->fetch_assoc()) {
            //obtem o id de todas a propriedades ent_ref do tipo de entidade que tem uma referência ao tipo de entidade pretendido
            $query2 = "SELECT id FROM property WHERE fk_ent_type_id = ".$idEnt." AND value_type = 'ent_ref' AND ent_type_id IN (SELECT ent_type_id FROM entity WHERE id = '".$entRef["id"]."')";
            $propEntRef = $this->bd->runQuery($query2);
            if (!$propEntRef || $propEntRef->num_rows === 0) {
                return false;
            }
            else {
                $idPropEntRef = $propEntRef->fetch_assoc()["id"];  
            }
            //obtem o id das entidades que satisfazem a pesquisa
            $query3 = "SELECT v.value FROM property AS p, entity AS e, value AS v WHERE v.property_id = ".$idPropEntRef." AND v.entity_id = ".$entRef["id"]." AND v.property_id = p.id AND e.id = v.entity_id";
            $entidadesComCorrespondencia = $this->bd->runQuery($query3)->fetch_assoc()["value"];
            array_push($guardaEntRef, $entidadesComCorrespondencia);
        }
        foreach ($guardaEntRef as $entidades) {
            if ($conta == 0) {
                $querydinamica .= "e.id IN (";
            }
            else {
                $querydinamica .= " OR e.id IN (";
            }
            $querydinamica .= "SELECT id FROM entity WHERE id = ".$entidades.")";
            $conta++;
        }
        return $querydinamica;
    }
    
    /**
     *
     * This method creates a query when the user selected one or more properties
     * presented in the table type where we found properties about relations that
     * where the ent type selected is envolved
     * Before we selected all the relations that have the properties selected.
     * In this method we will check if anyone of the results of that query has 
     * an entity wich type is the selected
     *
     * @param type $query1REL (query that selects all the relations that satisfy the properties selected)
     * @param type $idEnt (the id of the entity type we want to search)
     * @param type $querydinamica (the dynamic query that is alread built at the moment)
     * @return boolean|string (false if there are any errors or non results, string otherwise with an updated version od the dynamic query)
     */
    private function geraQueryTabela3($query1REL, $idEnt, $querydinamica) {
        $conta = 0;
        $guardaEnt = array();
        $query1REL = $this->bd->runQuery($query1REL);
        if (!$query1REL || $query1REL->num_rows === 0) {
            return false;
        }
        while ($rel = $query1REL->fetch_assoc()) {
            //obtem o id de todas a propriedades ent_ref do tipo de entidade que tem uma referência ao tipo de entidade pretendido
            $query2 = "SELECT entity1_id, entity2_id FROM relation WHERE id =".$rel["id"];
            $idEmtRel = $this->bd->runQuery($query2);
            if (!$idEmtRel || $idEmtRel->num_rows === 0) {
                return false;
            }
            else {
               $idEmtRel = $idEmtRel->fetch_assoc();
            }
            if ($idEmtRel["entity1_id"] == $idEnt) {
                array_push($guardaEnt, $idEmtRel["entity1_id"]);
            }
            else {
                array_push($guardaEnt, $idEmtRel["entity2_id"]);
            }
        }
        foreach ($guardaEnt as $entidades) {
            if ($conta == 0) {
                $querydinamica .= "e.id IN (";
            }
            else {
                $querydinamica .= " OR e.id IN (";
            }
            $querydinamica .= "SELECT id FROM entity WHERE id = ".$entidades.")";
            $conta++;
        }
        return $querydinamica;
        
    }
    
    /**
     * 
     * This method creates a query when the user selected one or more properties
     * presented in the table type where we found properties about entities that
     * where involved in a relation between the ent type selected
     * Before we selected all the entities that have the properties selected.
     * In this method we will check if there is any relation with the entities
     * presented in that results and if so what is the entity of the type we
     * want to search that is involved in the relation

     * @param type $query1ER (query that selects all the entities that satisfy the properties selected)
     * @param type $idEnt (the id of the entity type we want to search)
     * @param type $querydinamica (the dynamic query that is alread built at the moment)
     * @return boolean|string (false if there are any errors or non results, string otherwise with an updated version od the dynamic query)
     */
    private function geraQueryTabela4($query1ER, $idEnt, $querydinamica){
        $conta = 0;
        $guardaEnt = array();
        $query1ER = $this->bd->runQuery($query1ER);
        if (!$query1ER || $query1ER->num_rows === 0) {
            return false;
        }
        while ($er = $query1ER->fetch_assoc()) {
            //obtem o id de todas a propriedades ent_ref do tipo de entidade que tem uma referência ao tipo de entidade pretendido
            $query2 = "SELECT entity1_id, entity2_id FROM relation WHERE entity1_id =".$er['id']." OR entity2_id=".$er['id']."";
            $runQuery2 = $this->bd->runQuery($query2);
            if (!$runQuery2 || $runQuery2->num_rows === 0) {
                return false;
            }
           while($idER = $runQuery2 ->fetch_assoc() ){
                $tpEnt1 = $this->bd->runQuery("SELECT ent_type_id FROM entity WHERE id=".$idER['entity1_id']);
                while($read_TpEnt1 = $tpEnt1->fetch_assoc())
                {
                    if ($read_TpEnt1['ent_type_id']== $idEnt) {
                        array_push($guardaEnt, $idER["entity1_id"]);
                    }
                }
                $tpEnt2 = $this->bd->runQuery("SELECT ent_type_id FROM entity WHERE id=".$idER['entity2_id']);
                while($read_TpEnt2 = $tpEnt2->fetch_assoc())
                {
                     if ($read_TpEnt2['ent_type_id']== $idEnt) {
                        array_push($guardaEnt, $idER["entity2_id"]);
                    }
                }
           }
        }
        foreach ($guardaEnt as $entidades) {
            if ($conta == 0) {
                $querydinamica .= "e.id IN (";
            }
            else {
                $querydinamica .= " OR e.id IN (";
            }
            $querydinamica .= "SELECT id FROM entity WHERE id = ".$entidades.")";
            $conta++;
        }
        return $querydinamica;    
    }
    
    /**
     * This method will create the query that returns all the entities of 
     * the selected ent type that satisfy the selected properties
     * 
     * @param type $query1Ent (header of the query that will return all the entities of the selected ent type that satisfy the selected properties)
     * @param type $controlo (variable that checks if the this is the first time that this method is invoked)
     * @param type $count (variable that indicates wich of the checkboxes is being analysed)
     * @param type $idDaPropriedade (id of the selected property)
     * @param type $nomeProp (name of the selected property)
     * @param type $tipoValor (value type of the selected property)
     * @param type $tipo (indicates which type of property is involved)
     * @return boolean|string (true if there are any error with validations, the query if not)
     */
    private function filtro1Tabela($query1Ent,$controlo, $count,$idDaPropriedade,$nomeProp, $tipoValor, $tipo) {
        if ($controlo) {
            $query1Ent .= "e.id IN (";
        }
        else {
            $query1Ent .= " AND e.id IN (";
        }
        if ($tipoValor == "int") {
            if ($this->validaInt($count, $tipo) === false) {
                return true;
            }
            else {
                $valor = $this->validaInt($count, $tipo);
                $query1Ent .= "SELECT e.id FROM entity AS e, value AS v WHERE v.value".$_REQUEST['operators'.$count]." ".$valor." AND  v.property_id = ".$idDaPropriedade." AND v.entity_id = e.id)";
                $this->frase .= $_REQUEST['operators'.$count]." ";
            }
        }
        else if ($tipoValor == "double") {
            if ($this->validaDouble($count, $tipo) === false) {
                return true;
            }
            else {
                $valor = $this->validaDouble($count, $tipo);
                $query1Ent .= "SELECT e.id FROM entity AS e, value AS v WHERE v.value".$_REQUEST['operators'.$count]." ".$valor." AND  v.property_id = ".$idDaPropriedade." AND v.entity_id = e.id)";
                $this->frase .= $_REQUEST['operators'.$count]." ";
            }
        }
        else  if ($tipoValor == "text"){
            $valor = $this->bd->userInputVal($_REQUEST['text'.$tipo.$count]);
            $query1Ent .= "SELECT e.id FROM entity AS e, value AS v WHERE v.value = '".$valor."' AND  v.property_id = ".$idDaPropriedade." AND v.entity_id = e.id)";
        }
        else  if ($tipoValor == "enum"){
            $valor = $this->bd->userInputVal($_REQUEST['select'.$tipo.$count]);
            $query1Ent .= "SELECT e.id FROM entity AS e, value AS v WHERE v.value = '".$valor."' AND  v.property_id = ".$idDaPropriedade." AND v.entity_id = e.id)";
        }
        else  if ($tipoValor == "bool"){
            $valor = $this->bd->userInputVal($_REQUEST['radio'.$tipo.$count]);
            $query1Ent .= "SELECT e.id FROM entity AS e, value AS v WHERE v.value = '".$valor."' AND  v.property_id = ".$idDaPropriedade." AND v.entity_id = e.id";
        }
        $this->frase .= $valor;
        $this->preencheArrays ($idDaPropriedade,$nomeProp,$valor);
        return $query1Ent;
    }
   
    /**
     * 
     * This method will create the query that returns all the entities of 
     * the ent type correpondent to the table type
     * that satisfy the selected properties
     * 
     * @param type $query1 (header of the query that will return all the entities of the ent type correspondent to the table type that satisfy the selected properties)
     * @param type $controlo (variable that checks if the this is the first time that this method is invoked)
     * @param type $count (variable that indicates wich of the checkboxes is being analysed)
     * @param type $idDaPropriedade (id of the selected property)
     * @param type $nomeProp (name of the selected property)
     * @param type $tipoValor (value type of the selected property)
     * @param type $tipo (indicates which type of property is involved)
     * @return boolean|string (true if there are any error with validations, the query if not)
     */
    private function filtros2Tabela($query1,$controlo, $count,$idDaPropriedade,$nomeProp,$tipoValor, $tipo) {
        $res_GetEntId = $this->bd->runQuery("SELECT ent_type_id FROM property WHERE id=".$idDaPropriedade);
        $read_GetEntId = $res_GetEntId->fetch_assoc();
        //echo "Id da propriedade".$idDaPropriedade."<br>";
        if ($controlo) {
            array_push($this->saveNames, $read_GetEntId['ent_type_id']);
            $query1 .= "e.id IN (";
        }
        else {
           
            //echo in_array($read_GetEntId['ent_type_id'],$this->saveNames);
            //echo "O valor da entidade é ".$read_GetEntId['ent_type_id'];
            if(in_array($read_GetEntId['ent_type_id'],$this->saveNames))
            {
                $query1 .= " AND e.id IN (";
                //echo "entrei no AND";
            }
            else
            {
                array_push($this->saveNames, $read_GetEntId['ent_type_id']);
                $query1 .= " OR e.id IN (";        
                        
            }
            //print_r($this->saveNames);
        }
        if ($tipoValor == "int") {
            if ($this->validaInt($count, $tipo) === false) {
                return true;
            }
            else {
                $valor = $this->validaInt($count, $tipo);
                $query1 .= "SELECT e.id FROM entity AS e, value AS v WHERE v.value".$_REQUEST['operators'.$count]." ".$valor." AND  v.property_id = ".$idDaPropriedade." AND v.entity_id = e.id)";
                $this->frase .= $_REQUEST['operators'.$count]." ";
            }
        }
        else if ($tipoValor == "double") {
            if ($this->validaDouble($count, $tipo) === false) {
                return true;
            }
            else {
                $valor = $this->validaDouble($count, $tipo);
                $query1 .= "SELECT e.id FROM entity AS e, value AS v WHERE v.value".$_REQUEST['operators'.$count]." ".$valor." AND  v.property_id = ".$idDaPropriedade." AND v.entity_id = e.id)";
                $this->frase .= $_REQUEST['operators'.$count]." ";
            }
        }
        else  if ($tipoValor == "text"){
            $valor = $this->bd->userInputVal($_REQUEST['text'.$tipo.$count]);
            $query1 .= "SELECT e.id FROM entity AS e, value AS v WHERE v.value = '".$valor."' AND  v.property_id = ".$idDaPropriedade." AND v.entity_id = e.id)";
        }
        else  if ($tipoValor == "enum"){
            $valor = $this->bd->userInputVal($_REQUEST['select'.$tipo.$count]);
            $query1 .= "SELECT e.id FROM entity AS e, value AS v WHERE v.value = '".$valor."' AND  v.property_id = ".$idDaPropriedade." AND v.entity_id = e.id)";
        }
        else  if ($tipoValor == "bool"){
            $valor = $this->bd->userInputVal($_REQUEST['radio'.$tipo.$count]);
            $query1 .= "SELECT e.id FROM entity AS e, value AS v WHERE v.value = '".$valor."' AND  v.property_id = ".$idDaPropriedade." AND v.entity_id = e.id";
        }
        $this->frase .= $valor;
        $this->preencheArrays ($idDaPropriedade,$nomeProp,$valor);
        return $query1;
    }

    /**
     * 
     * This method will create the query that returns all the entities of 
     * the relation that satisfy the selected properties
     * 
     * @param type $query1 (header of the query that will return all the relations that satisfy the selected properties)
     * @param type $controlo (variable that checks if the this is the first time that this method is invoked)
     * @param type $count (variable that indicates wich of the checkboxes is being analysed)
     * @param type $idDaPropriedade (id of the selected property)
     * @param type $nomeProp (name of the selected property)
     * @param type $tipoValor (value type of the selected property)
     * @param type $tipo (indicates which type of property is involved)
     * @return boolean|string (true if there are any error with validations, the query if not)
     */
    private function filtros3Tabela($query1,$controlo ,$count,$idDaPropriedade,$nomeProp,$tipoValor, $tipo) {
        $res_GetEntId = $this->bd->runQuery("SELECT ent_type_id FROM property WHERE id=".$idDaPropriedade);
        $read_GetEntId = $res_GetEntId->fetch_assoc();
        if ($controlo) {
            array_push($this->saveNames, $read_GetEntId['ent_type_id']);
            $query1 .= "r.id IN (";
        }
        else {
           
            //echo in_array($read_GetEntId['ent_type_id'],$this->saveNames);
            //echo "O valor da entidade é ".$read_GetEntId['ent_type_id'];
            if(in_array($read_GetEntId['ent_type_id'],$this->saveNames))
            {
                $query1 .= " AND r.id IN (";
                //echo "entrei no AND";
            }
            else
            {
                array_push($this->saveNames, $read_GetEntId['ent_type_id']);
                $query1 .= " OR r.id IN (";        
                        
            }
            //print_r($this->saveNames);
        }
        if ($tipoValor == "int") {
            if ($this->validaInt($count, $tipo) === false) {
                return true;
            }
            else {
                $valor = $this->validaInt($count, $tipo);
                $query1 .= "SELECT r.id FROM relation AS r, value AS v WHERE v.value".$_REQUEST['operators'.$count]." ".$valor." AND  v.property_id = ".$idDaPropriedade." AND v.relation_id = r.id)";
                $this->frase .= $_REQUEST['operators'.$count]." ";
            }
        }
        else if ($tipoValor == "double") {
            if ($this->validaDouble($count, $tipo) === false) {
                return true;
            }
            else {
                $valor = $this->validaDouble($count, $tipo);
                $query1 .= "SELECT r.id FROM relation AS r, value AS v WHERE v.value".$_REQUEST['operators'.$count]." ".$valor." AND  v.property_id = ".$idDaPropriedade." AND v.relation_id = r.id)";
                $this->frase .= $_REQUEST['operators'.$count]." ";
            }
        }
        else  if ($tipoValor == "text"){
            $valor = $this->bd->userInputVal($_REQUEST['text'.$tipo.$count]);
            $query1 .= "SELECT r.id FROM relation AS r, value AS v WHERE v.value = '".$valor."' AND  v.property_id = ".$idDaPropriedade." AND v.relation_id = r.id)";
        }
        else  if ($tipoValor == "enum"){
            $valor = $this->bd->userInputVal($_REQUEST['select'.$tipo.$count]);
            $query1 .= "SELECT r.id FROM relation AS r, value AS v WHERE v.value = '".$valor."' AND  v.property_id = ".$idDaPropriedade." AND v.relation_id = r.id)";
        }
        else  if ($tipoValor == "bool"){
            $valor = $this->bd->userInputVal($_REQUEST['radio'.$tipo.$count]);
            $query1 .= "SELECT r.id FROM relation AS r, value AS v WHERE v.value = '".$valor."' AND  v.property_id = ".$idDaPropriedade." AND v.relation_id = r.id";
        }
        $this->frase .= $valor;
        $this->preencheArrays ($idDaPropriedade,$nomeProp,$valor);
        return $query1;
    }
    
    /**
     * Method that validates the user input when it's type is suposed to be of an int
     * @param type $count (the number of the input)
     * @param type $tipo (the type of table where is the input)
     * @return boolean
     */
    private function validaInt ($count, $tipo) {
        if ($this->verificaOperadores($count)) {
            $int_escaped = $this->bd->userInputVal($_REQUEST['int'.$tipo.$count.'']);
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
    
    /**
     * Method that validates the user input when it's type is suposed to be of an double
     * @param type $count (the number of the input)
     * @param type $tipo (the type of table where is the input)
     * @return boolean
     */
    private function validaDouble ($count, $tipo) {
        if ($this->verificaOperadores($count)) {
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
    
    /**
     * Method that check if the user selected one operator
     * @param type $count (the number of the input)
     * @return boolean (true if user selected one operator, false otherwise)
     */
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
    
    /**
     * This method populates the arrays that are used to save the properties and its values.
     * @param type $idDaPropriedade (id of the property)
     * @param type $nomeProp (name of the property)
     * @param type $valor (value for the property)
     */
    private function preencheArrays ($idDaPropriedade,$nomeProp,$valor) {
        array_push($this->guardaidDosSelecionados,$idDaPropriedade);
        array_push($this->guardanomePropSelec, $nomeProp);
        array_push($this->guardaValorDaProp,$valor);
    }
    
    /**
     * This method presents the results of tha dynamic search.
     * First it writes a descritive sentence about the search
     * Then it presents a table with all the results, where the user can edit, 
     * view the history, or activate/desactivate
     * @param type $querydinamica
     * @param type $selected (number of the selected filters)
     */
    private function apresentaResultado ($querydinamica, $selected) {
?>
        <p><?php echo $this->frase;?></p>
<?php
        $instEnt = $this->bd->runquery($querydinamica);		
        //imprime a lista de instancias do componente selecionado de acordo com os filtros
        if ($instEnt->num_rows === 0) {
?>
            <p>Não existem entidades que respeitem a pesquisa efetuada.</p>
<?php
            goBack();
        }
        else {
            if ($selected == 0) {
?>
                <form method="GET">
                    Verificar propriedades existentes no dia : 
                    <input type="text" class="datepicker" id="datepicker" name="data" placeholder="Introduza uma data"> 
                    <input type="hidden" name="estado" value="historico">
                    <input type="hidden" name="ent" value="<?php echo $this->bd->userInputVal($_REQUEST["ent"])?>">
                    <input type="hidden" name="histAll" value="true">
                    <input type="submit" value="Apresentar propriedades">
                </form>
<?php
            }          
?>
        <table class="table">
            <thead>
                <tr>
                    <th>Instância</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
<?php
            $arrayInstId = array();
            $arrayInstComp = array();
            while($instancias =$instEnt->fetch_assoc()) {
?>
            <tr>
                <td>
<?php
                    $getEntName = "SELECT * FROM entity WHERE id = ".$instancias['id'];
                    if ($this->bd->runQuery($getEntName)->num_rows == 0) {
                        echo $instancias['id'];
                    }
                    else {
                        $entity = $this->bd->runQuery($getEntName)->fetch_assoc();
                        $entity_name = $entity['entity_name'];
                        $entity_id = $entity['id'];
                        if (!empty ($entity_name)) {
?>
                            <?php echo $entity_name;?>
<?php
                        }
                        else {
?>
                           <?php echo $entity_id;?>
<?php
                        }
                    }
?>
                </td>
                <td>
                     <a href="?estado=apresentacao&id=<?php echo $entity_id;?>">[Inserir/Editar Propriedades da Entidade]</a>
                        
<?php

                    $readState = $this->bd->runQuery("SELECT state FROM entity WHERE id=".$entity_id)->fetch_assoc();
                    if($readState['state'] == "active")
                    {
?>
                        <a href="?estado=inactive&id=<?php echo $entity_id;?>">[Desativar]</a>
<?php                    
                    }
                    else
                    {
?>
                        <a href="?estado=active&id=<?php echo $entity_id;?>">[Ativar]</a>
<?php
                    }
                    
?>
                        
                        <a href="?estado=historico&ent_id=<?php echo $entity_id;?>">[Histórico]</a>  
                </td>
            </tr>	
<?php
                array_push($arrayInstId,$instancias['id']);
                array_push($arrayInstComp,$entity_name); 
            }
?>
            </tbody>
        </table>
<?php
            $excelGen = new ExportValues();
            $excelGen->geraExcel($querydinamica,$this->frase,$this->guardaidDosSelecionados,$this->guardanomePropSelec,$this->guardaValorDaProp,$arrayInstId,$arrayInstComp);
        }
    }
    
    /**
     * Method that creates the tables where the user can view and edit the values
     * of properties for the entity selected in the previous state
     */
    public function estadoApresentacao() {
        $idEnt = $this->bd->userInputVal($_REQUEST["id"]);
        $queryEnt = "SELECT * FROM entity WHERE id = ".$idEnt;
        $ent = $this->bd->runQuery($queryEnt)->fetch_assoc();
        if (!empty ($ent["entity_name"])) {
?>
            <h3>Entidade <?php echo $ent["entity_name"];?> - Inserção de Propriedades</h3>
<?php
            $this->printEntAttrAdder($idEnt);
?>
            <h3>Entidade <?php echo $ent["entity_name"];?> - Alteração de Propriedades</h3>
<?php
        }
        else {
?>
            <h3>Entidade <?php echo $idEnt;?> - Inserção de Propriedades</h3>
<?php
            $this->printEntAttrAdder($idEnt);
?>
            <h3>Entidade <?php echo $idEnt;?> - Alteração de Propriedades</h3>
<?php            
        }
        $ent_type = $ent["ent_type_id"];

        $queryProp = "SELECT * FROM property WHERE ent_type_id = ".$ent_type;
        $queryProp = $this->bd->runQuery($queryProp);
?>
    <form>
        <html>
        <table class="table">
            <thead>
                <th>Propriedade</th>
                <th>Valor Atual</th>
                <th>Estado</th>
                <th>Selecionar</th>
                <th>Novo Valor</th> 
                <th>Ação</th>
            </thead>
            <tbody>
<?php
        $x = 0;
        while ($prop = $queryProp->fetch_assoc()) {
?>
            <tr>
<?php
            $queryValue = "SELECT * FROM value WHERE property_id = ".$prop["id"]." AND entity_id = ".$idEnt;
            $queryValue = $this->bd->runQuery($queryValue);
            while ($value = $queryValue->fetch_assoc()) {
                if ($prop["value_type"] != "ent_ref") {
                    $valor = $value["value"];
                }
                else {
                    $queryEnt = "SELECT * FROM entity WHERE id = ".$value["value"];
                    $ent = $this->bd->runQuery($queryEnt)->fetch_assoc();
                    if (!empty ($ent["entity_name"])) {
                        $valor = $ent["entity_name"];
                    }
                    else {
                        $valor = $value["value"];
                    }
                    
                }
?>
                <td><?php echo $prop["name"];?></td>
                <td><?php 
                if($valor == "")
                {
                   echo "Sem Valor Atribuido";
                }
                else
                {
                    echo $valor;
                }
                ?>
                </td>
                <td>
                    <?php if($value["state"] == 'active')
                    {
                        echo "Ativo";
                    }
                    else
                    {
                        echo "Inativo";
                    }
                    ?>
                </td>
                  
               
<?php
                    $getValType = $this->bd->runQuery("SELECT * FROM property WHERE id = ".$value['property_id'])->fetch_assoc();
                    if($value["state"] == 'active')
                    {
?>
                        <td>
                            <input type="checkbox" name="check<?php echo $x?>" value="<?php echo $value["id"] ?>">
                        </td>  
                        <td>
<?php
                        if($getValType['value_type'] == 'bool')
                        {

?>
                            <input type="radio" name="<?php echo 'radio'.$x?>" value="true">True
                            <input type="radio" name="<?php echo 'radio'.$x?>" value="false">False
<?php
                        }
                        else if($getValType['value_type'] == 'enum')
                        {   
                        $res_EnumValue = $this->bd->runQuery("SELECT * FROM prop_allowed_value WHERE property_id=".$value['property_id']);
?>
                        <select name="<?php echo 'select'.$x ?>">
<?php
                            while($read_EnumValue = $res_EnumValue->fetch_assoc())
                            {
?>
                                <option  value="<?php echo $read_EnumValue['value']; ?>"><?php echo $read_EnumValue['value']; ?></option>
<?php
                            }
?>
                        </select>
<?php                   }
                        else if($getValType['value_type'] == 'ent_ref')
                        {
                            $getEntToRef = $this->bd->runQuery("SELECT * FROM entity WHERE ent_type_id=".$getValType['fk_ent_type_id']);
?>                      
                            <select name="<?php echo 'select'.$x ?>">
<?php
                            while($readEntToRef = $getEntToRef->fetch_assoc()){
                               if($readEntToRef['entity_name'] == "")
                               {
?>
                                   <option  value="<?php echo $readEntToRef['id']; ?>"><?php echo $readEntToRef['id']; ?></option>
<?php
                               }
                               else
                               {
?>
                                   <option  value="<?php echo $readEntToRef['id']; ?>"><?php echo $readEntToRef['entity_name']; ?></option>
<?php
                                }
                            }
?>
                        </select>
<?php
                        }
                        else
                        {
?>
                            <input type="text" name="<?php echo 'textbox'.$x ?>">
<?php  
                        }
?>
                </td>
              
                
<?php
                    }
                    else
                    {
?>
                      <td> - </td>
                      <td> - </td>
<?php
                    }
?>
                    <td>
<?php
                    if($value['state'] == 'active')
                    {
?>
                        <a href="pesquisa-dinamica?estado=desativarVal&valOfEnt=<?php echo $value['id'];?>">[Desativar]</a>
<?php
                    }
                    else
                    {
?>
                        <a href="pesquisa-dinamica?estado=ativarVal&valOfEnt=<?php echo $value['id'];?>">[Ativar]</a>
                        
<?php
                    }
?>
                   <input type="hidden" name="state<?php echo $x?>" value="<?php echo $value["state"] ?>">
                </td>
              
            </tr>
<?php
            }
?>

<?php
            $x++;
        }
?>
           </tbody>
        </table>
<?php
            $_SESSION['updateValue'] = $x;
?>
            <input type="hidden" name="id" value="<?php echo $_REQUEST['id']?>"><br>
            <input type="hidden" name="estado" value="updateValoresEnt"><br>
           <input type="submit" value="Atualizar">
    </form>        
<?php
    
    }
    
    /**
     * Prints a table with all the attributes that you cana dd to a entity.
     * @param type $id -> id from the entity in which we want to add values
     */
    private function printEntAttrAdder($id){
        
?>
        
<?php
                    //echo "SELECT p.* FROM property AS p, entity AS e WHERE p.ent_type_id = e.ent_type_id AND e.id=".$id." AND  p.id NOT IN (SELECT property_id FROM value AS v WHERE v.entity_id=".$id.")";
                    $getAvaiablePropsToAdd = $this->bd->runQuery("SELECT p.* FROM property AS p, entity AS e WHERE p.ent_type_id = e.ent_type_id AND e.id=".$id." AND p.id NOT IN (SELECT property_id FROM value AS v WHERE v.entity_id=".$id.")");
                    if($getAvaiablePropsToAdd->num_rows == 0)
                    {
?>
                          <html>
                            <p>Não existem propriedades que possam ser adicionadas.</p>
                        </html>
<?php
                    }
                    else
                    {
?>
                    <form>
                        <html>
                            <table class='table'>
                                <thead>
                                    <th>Id</td>
                                    <th>Nome propriedade</td>
                                    <th>Tipo</th>
                                    <th>Seleção</th>
                                    <th>Novo valor</th>
                                </thead>
                                <tbody>
<?php
                        $conta = 0;
                        while($printProps = $getAvaiablePropsToAdd->fetch_assoc()){
?>                        
                            <tr>
                                <td><?php echo $printProps['id']?></td>
                                <td><?php echo $printProps['name']?></td>
                                <td><?php echo $printProps['value_type']?></td>
                                <td><input type="checkbox" name="check<?php echo $conta; ?>" value="<?php echo $printProps['id']?>"></td>
                                <td>
<?php
                                        if($printProps['value_type'] == 'bool')
                                        {
?>
                                            <input type="radio" name="<?php echo 'radio'.$conta ?>" value="true">True
                                            <input type="radio" name="<?php echo 'radio'.$conta ?>" value="false">False
<?php
                                        }
                                        else if($printProps['value_type'] == 'enum')
                                        {   
                                                $res_EnumValue = $this->bd->runQuery("SELECT * FROM prop_allowed_value WHERE property_id=".$printProps['id']);
?>
                                                <select name="<?php echo 'select'.$conta ?>">
<?php
                                                while($read_EnumValue = $res_EnumValue->fetch_assoc())
                                                {
?>
                                                    <option  value="<?php echo $read_EnumValue['value']; ?>"><?php echo $read_EnumValue['value']; ?></option>
<?php
                                                }
?>
                                                </select>
<?php
                                        }
                                        else
                                        {
?>
                                                <input type="text" name="<?php echo 'textbox'.$conta ?>">
                                            
<?php
                                       }
?>
                                </td>
                            </tr>
<?php
                            $conta++;
                        }
                        $_SESSION['entPropPrinted'] = $conta;
?>
                </tbody>
            </table>
        </html> 
                        <input type="hidden" name="estado" value="novasPropriedadesAdd" >
                        <input type="hidden" name="iddaEnt" value="<?php echo $id ?>" >
                        <input type="submit" value="Adicionar Novas Propriedades">
                    </form>
<?php                        
                    }
    }
    
    /**
     *This method will insert new properties in the entity choosed by the user
     */
    private function addAttrEnt(){ 
            
        $this->bd->getMysqli()->autocommit(false);
	$this->bd->getMysqli()->begin_transaction();
        if($this->ssValidationUp($_SESSION['entPropPrinted'],2)){
            $updated_on = date("Y-m-d H:i:s",time());
            for($i= 0; $i <= $_SESSION['entPropPrinted']; $i++ )
            {
                
                if(isset($_REQUEST['check'.$i]))
                {
                    if(isset($_REQUEST['radio'.$i]))
                    {
                        $newValue = $_REQUEST['radio'.$i];
                    }
                    else if(isset($_REQUEST['select'.$i]))
                    {
                        $newValue = $_REQUEST['select'.$i];
                    }
                    else if(isset($_REQUEST['textbox'.$i]))
                    {
                        $newValue =$_REQUEST['textbox'.$i];
                    }
                    $id = $this->bd->userInputVal($_REQUEST['iddaEnt']);
                    
                    if($this->gereInsts->addEntToHist($id,$this->bd,$updated_on))
                    {
                        $getCurrentVals = $this->bd->runQuery("SELECT * FROM value WHERE entity_id=".$id);
                        $erro = false;
                        while($readVal = $getCurrentVals->fetch_assoc()){
                            if(!$this->gereInsts->addHistValues($readVal['id'],$this->bd,$updated_on)){
                                $erro = true;
                            }
                        }
                        
                        if(!$erro){
                            if($this->bd->runQuery("INSERT INTO `value`(`id`, `entity_id`, `property_id`, `value`, `producer`, `relation_id`, `state`, `updated_on`) VALUES (NULL,".$id.",".$_REQUEST['check'.$i].",'".$newValue."','".wp_get_current_user()->user_login."',NULL,'active','".$updated_on."')"))
                            {
?>
                                <html>
                                    <p>As propriedades foram adicionadas à entidade.</p>
                                    <p>Clique em <a href="/pesquisa-dinamica"/>Continuar</a> para avançar</p>
                                </html>
<?php
                            $this->bd->getMysqli()->commit();
                            }
                            else
                            {
?>
                                <html>
                                    <p>Erro ao adicionar as propriedades selecionadas à entidade.</p>
                                    <p>Clique em <a href="/pesquisa-dinamica"/>Continuar</a> para avançar</p>
                                </html>
<?php
                            $this->bd->getMysqli()->rollback();
                            }
                        }
                        else{
?>
                            <html>
                                <p>Erro ao criar uma cópia dos valores autuais da entidade na tabela hist_value.</p>
                                <p><?php goBack(); ?></p>
                            </html>
<?php
                             $this->bd->getMysqli()->rollback();
                        }
                    }
                    else
                    {
?>
                            <html>
                                <p>Erro ao criar heckuma cópia da entidade na tabela hist_entity.</p>
                                <p><?php goBack(); ?></p>
                            </html>
<?php  
                        $this->bd->getMysqli()->rollback();
                    }
                    

                    
                }
            }
        }
        else
        {
          $this->bd->getMysqli()->rollback();  
        }
        }
    
    /**
     * This method will handle the activation and the the desativation of the
     * entities.
     */
    public function changeState(){
        $id = $this->bd->userInputVal($_REQUEST['id']);
        $estado = $this->bd->userInputVal($_REQUEST['estado']);
        $readVal = $this->bd->runQuery("SELECT * FROM entity WHERE id=".$id)->fetch_assoc();
        $updated_on = date("Y-m-d H:i:s",time());
        //echo $estado;
        $getRel = $this->bd->runQuery("SELECT * FROM relation WHERE entity1_id=".$id." OR entity2_id=".$id." AND state='active'");
        if($getRel->num_rows == 0){
            if($this->gereInsts->addHist($id,$this->bd)){
                if($estado == 'active')
                {
                    $this->bd->runQuery("UPDATE `entity` SET `state`='active',`updated_on`='".$updated_on."' WHERE id=".$id);
                    $getUnadedVals = $this->bd->runQuery("SELECT * FROM value WHERE entity_id=".$id); 
                    while($readVals = $getUnadedVals->fetch_assoc())
                    {
                        if($readVals['relation_id'] == "")
                        {
                            if(!$this->bd->runQuery("INSERT INTO `hist_value`(`id`, `entity_id`, `property_id`, `value`, `producer`, `relation_id`, `value_id`, `active_on`, `inactive_on`, `state`) VALUES (NULL,".$readVals['entity_id'].",".$readVals['property_id'].",'".$readVals['value']."','".$readVals['producer']."',NULL,".$readVals['id'].",'".$readVals['updated_on']."','".$updated_on."','".$readVals['state']."')"))
                            {
                               $this->bd->getMysqli()->rollback();
                            }
                        }
                        else
                        {
                            if(!$this->bd->runQuery("INSERT INTO `hist_value`(`id`, `entity_id`, `property_id`, `value`, `producer`, `relation_id`, `value_id`, `active_on`, `inactive_on`, `state`) VALUES (NULL,".$readVals['entity_id'].",".$readVals['property_id'].",'".$readVals['value']."','".$readVals['producer']."',".$readVals['relation_id'].",".$readVals['id'].",'".$readVals['updated_on']."','".$updated_on."','".$readVals['state']."')"))
                            {
                               $this->bd->getMysqli()->rollback();
                            }
                        }
                    }
?>
                    <p>A instância <?php $readVal['entity_name'] == "" ?  $readVal['id']: $readVal['entity_name'] ?> foi ativado</p>
                    <p>Clique em <a href="/pesquisa-dinamica/">Pesquisa dinâmica </a> para continuar</p>
<?php
                    $this->bd->getMysqli()->commit();   
                }
                else if ($estado == 'inactive')
                {
                    $this->bd->runQuery("UPDATE `entity` SET `state`='inactive',`updated_on`='".$updated_on."' WHERE id=".$id);
                    $getUnadedVals = $this->bd->runQuery("SELECT * FROM value WHERE entity_id=".$id); 
                    while($readVals = $getUnadedVals->fetch_assoc())
                    {
                        if($readVals['relation_id'] == "")
                        {
                            if(!$this->bd->runQuery("INSERT INTO `hist_value`(`id`, `entity_id`, `property_id`, `value`, `producer`, `relation_id`, `value_id`, `active_on`, `inactive_on`, `state`) VALUES (NULL,".$readVals['entity_id'].",".$readVals['property_id'].",'".$readVals['value']."','".$readVals['producer']."',NULL,".$readVals['id'].",'".$readVals['updated_on']."','".$updated_on."','".$readVals['state']."')"))
                            {
                               $this->bd->getMysqli()->rollback();
                            }
                        }
                        else
                        {
                            if(!$this->bd->runQuery("INSERT INTO `hist_value`(`id`, `entity_id`, `property_id`, `value`, `producer`, `relation_id`, `value_id`, `active_on`, `inactive_on`, `state`) VALUES (NULL,".$readVals['entity_id'].",".$readVals['property_id'].",'".$readVals['value']."','".$readVals['producer']."',".$readVals['relation_id'].",".$readVals['id'].",'".$readVals['updated_on']."','".$updated_on."','".$readVals['state']."')"))
                            {
                                $this->bd->getMysqli()->rollback();
                            }
                        }
                    }
?>
                    <p>A instância <?php $readVal['entity_name'] == "" ?  $readVal['id']: $readVal['entity_name'] ?> foi desativada</p>
                    <p>Clique em <a href="/pesquisa-dinamica/">Pesquisa dinâmica </a> para continuar</p>
<?php 
                    $this->bd->getMysqli()->commit();   
                }
                
            }
            else
            {
?>
                <p>Clique em <a href="/insercao-de-relacoes/">Inserção de relações</a></p>
                <p>ou Clique em <?php goBack() ?> para voltar à página anterior.</p>
<?php
                $this->bd->getMysqli()->rollback();
            }
        }
        else
        {
?>
                <p>Necessita de desativar relações a que a instância <?php $readVal['entity_name'] == "" ?  $readVal['id']: $readVal['entity_name'] ?> pertence,</p>
                <p>para poder proceder à sua desativação </p>
                <p>Clique em <a href="/insercao-de-relacoes/">Inserção de relações</a></p>
                <p>ou Clique em <?php goBack() ?> para voltar à página anterior.</p>
<?php
                $this->bd->getMysqli()->rollback();
        }
    }
    
    /**
     * Updates the values for the selected entity
     */
    public function updatEntVal(){
        if($this->ssValidationUp($_SESSION['updateValue'],1)){
            
            $this->bd->getMysqli()->autocommit(false);
            $this->bd->getMysqli()->begin_transaction();
            
            $updated_on = date("Y-m-d H:i:s",time());
            $error = false;
            $added =false;
            $id = 0;
            for($x = 0; $x <= $_SESSION['updateValue']; $x++)
            { 
                if(isset($_REQUEST['check'.$x]))
                {
                     if(isset($_REQUEST['select'.$x]))
                    {
                        if($this->gereInsts->addHistValues($this->bd->userInputVal($_REQUEST['check'.$x]),$this->bd,$updated_on)){
                            if(!$this->bd->runQuery("UPDATE `value` SET `value`='".$this->bd->userInputVal($_REQUEST['select'.$x])."',`producer`='".wp_get_current_user()->user_login."',`updated_on`='".$updated_on."',`state`='".$_REQUEST['state'.$x]."' WHERE id=".$this->bd->userInputVal($_REQUEST['check'.$x]).""))
                            {
                                $error = true;
                                break;
                            }
                        
                        }
                        else
                        {
                            $error = true;
                            break;
                        }
                    }
                    else if(isset($_REQUEST['radio'.$x]))
                    {
                        if($this->gereInsts->addHistValues($this->bd->userInputVal($_REQUEST['check'.$x]),$this->bd,$updated_on)){
                            if(!$this->bd->runQuery("UPDATE `value` SET `value`='".$this->bd->userInputVal($_REQUEST['radio'.$x])."',`producer`='".wp_get_current_user()->user_login."',`updated_on`='".$updated_on."',`state`='".$_REQUEST['state'.$x]."' WHERE id=".$this->bd->userInputVal($_REQUEST['check'.$x]).""))
                            {
                                $error = true;
                                break;
                            }
                        }
                        else 
                        {
                            $error = true;
                            break;
                        }
                    }
                    else if(isset($_REQUEST['textbox'.$x]))
                    {
                        if($this->gereInsts->addHistValues($this->bd->userInputVal($_REQUEST['check'.$x]),$this->bd,$updated_on)){
                            if(!$this->bd->runQuery("UPDATE `value` SET `value`='".$this->bd->userInputVal($_REQUEST['textbox'.$x])."',`producer`='".wp_get_current_user()->user_login."',`updated_on`='".$updated_on."',`state`='".$_REQUEST['state'.$x]."' WHERE id=".$this->bd->userInputVal($_REQUEST['check'.$x]).""))
                            {
                                $error = true;
                                break;
                            }
                        }
                        else
                        {
                            $error = true;
                            break;
                        }
                    }
                    //Backups the entity in the first iteration
                    if($added == false)
                    {
                        $getEntId = $this->bd->runQuery("SELECT entity_id FROM value WHERE id=".$this->bd->userInputVal($_REQUEST['check'.$x]));
                        $readId = $getEntId->fetch_assoc();
                        $id = $readId['entity_id'];
                        if(!$this->gereInsts->addEntToHist($readId['entity_id'],$this->bd,$updated_on)){
                            $error = true;
                            break;
                        }
                        if($error == false){
                            if(!$this->bd->runQuery("UPDATE `entity` SET`updated_on`='".$updated_on."' WHERE id=".$id)){
                                $error = true;
                                break;
                            }
                        }
                        $added = true;   
                    }
                    
                }
            }   
            //Backups the value that haven't been changed
            $saveRemainValue = $this->bd->runQuery("SELECT * FROM value WHERE value. id NOT IN (SELECT value_id FROM hist_value WHERE inactive_on = '".$updated_on."'AND entity_id = ".$id.") AND entity_id = ".$id);
            while($readVals = $saveRemainValue->fetch_assoc())
            {
                 if($readVals['relation_id'] == "")
                {
                    if(!$this->bd->runQuery("INSERT INTO `hist_value`(`id`, `entity_id`, `property_id`, `value`, `producer`, `relation_id`, `value_id`, `active_on`, `inactive_on`, `state`) VALUES (NULL,".$readVals['entity_id'].",".$readVals['property_id'].",'".$readVals['value']."','".$readVals['producer']."',NULL,".$readVals['id'].",'".$readVals['updated_on']."','".$updated_on."','".$readVals['state']."')"))
                    {
                       $error = true;
                       break;
                    }
                }
                else
                {
                    if(!$this->bd->runQuery("INSERT INTO `hist_value`(`id`, `entity_id`, `property_id`, `value`, `producer`, `relation_id`, `value_id`, `active_on`, `inactive_on`, `state`) VALUES (NULL,".$readVals['entity_id'].",".$readVals['property_id'].",'".$readVals['value']."','".$readVals['producer']."',".$readVals['relation_id'].",".$readVals['id'].",'".$readVals['updated_on']."','".$updated_on."','".$readVals['state']."')"))
                    {
                        $error = true;
                        break;
                    }
                }
            }
            //$getUnadedVals = $this->bd->runQuery("SELECT * FROM value WHERE ");
            
            /*while($readVals = $getUnadedVals->fetch_assoc())
            {
                if($readVals['relation_id'] == "")
                {
                    if(!$this->bd->runQuery("INSERT INTO `hist_value`(`id`, `entity_id`, `property_id`, `value`, `producer`, `relation_id`, `value_id`, `active_on`, `inactive_on`, `state`) VALUES (NULL,".$readVals['entity_id'].",".$readVals['property_id'].",'".$readVals['value']."','".$readVals['producer']."',NULL,".$readVals['id'].",'".$readVals['updated_on']."','".$updated_on."','".$readVals['state']."')"))
                    {
                       $error = true;
                       break;
                    }
                }
                else
                {
                    if(!$this->bd->runQuery("INSERT INTO `hist_value`(`id`, `entity_id`, `property_id`, `value`, `producer`, `relation_id`, `value_id`, `active_on`, `inactive_on`, `state`) VALUES (NULL,".$readVals['entity_id'].",".$readVals['property_id'].",'".$readVals['value']."','".$readVals['producer']."',".$readVals['relation_id'].",".$readVals['id'].",'".$readVals['updated_on']."','".$updated_on."','".$readVals['state']."')"))
                    {
                        $error = true;
                        break;
                    }
                }
            }*/
            
            if($error == false)
            {
                $this->bd->getMysqli()->commit();
?>
                <p>Os valores das propriedades da entidade selecionada foram alterados.</p>
                <p>Clique em <a href="/pesquisa-dinamica"/>Continuar</a> para avançar</p>
                
<?php
            }
            else
            {
                $this->bd->getMysqli()->rollback();
?>
                <p>Ocorreu um erro na alteração valores das propriedades da entidade selecionada.</p>
                <p>Clique em <?php goBack()?> para voltar a página anterior</p>
<?php
            }
                    
        }
    }
    
    /**
     * Ensures that all data that will be inserted in the database is what was supossed.
     * @return boolean -> true = all ok, false = something wrong happened
     * mode 1 = check the values that the value to update atributes oof one entity
     * mode 2 = check the values that come from the inserts
     */
    public function ssValidationUp($max, $mode)
    {
        $count = 0;
        for($x = 0; $x <= $max; $x++)
        {           
            if(isset($_REQUEST['check'.$x]))
            {
                if(empty($_REQUEST['select'.$x]) && empty($_REQUEST['radio'.$x]) && empty($_REQUEST['textbox'.$x]))
                {
?>
                        <html>
                            <p>Verifique se para todas as checkBoxes selecionadas introduziu valores.</p>
                            <p>Clique em <?php goBack()?> para voltar a página anterior</p>
                        </html>
<?php   
                    return false;
                }
                else
                {
                    if(isset($_REQUEST['select'.$x]))
                    {}
                    else if(isset($_REQUEST['radio'.$x]))
                    {}
                    else if(isset($_REQUEST['textbox'.$x]))
                    {
                        if($mode == 1){
                            $res_getPropId = $this->bd->runQuery("SELECT property_id FROM value WHERE id=".$this->bd->userInputVal($_REQUEST['check'.$x]));
                            $getPropId = $res_getPropId->fetch_assoc();

                            $res_getValue_Type = $this->bd->runQuery("SELECT value_type FROM property WHERE id=".$getPropId['property_id']);
                            $getValue_Type = $res_getValue_Type->fetch_assoc();
                        }
                        else
                        {
                            //echo "SELECT value_type FROM property WHERE id=".$this->bd->userInputVal($_REQUEST['check'.$x]);
                            $res_getValue_Type = $this->bd->runQuery("SELECT value_type FROM property WHERE id=".$this->bd->userInputVal($_REQUEST['check'.$x]));
                            $getValue_Type = $res_getValue_Type->fetch_assoc();
                        }
                        if($this->typeValidation($getValue_Type['value_type'], $this->bd->userInputVal($_REQUEST['textbox'.$x]))== false)
                        {
?>
                            <html>
                                <p>Verifique se o tipo introduzido num dos campos é compativel com o valor aceite na base de dados.</p>
                                 <p>Clique em <?php goBack()?> para voltar a página anterior</p>
                            </html>
                        
<?php
                                        return false;
                        }
                    }
                }
                            $count++;
            }
        }
                    
                if($count == 0)
                {
?>
                    <html>
                        <p>Deve selecionar pelo menos uma propriedade para atualizar</p>
                        <p>Clique em <?php goBack()?> para voltar a página anterior</p>
                    </html>
<?php
                    return false;
                }
                    return true;
           
        }
    
    
    /**
     * Check if a value is an integer or a bool or a double.
     * @param type $value_type ->type of that value
     * @param type $valores -> value to checl
     * @return boolean
     */
    private function typeValidation($value_type,$valores){
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
                    <p>O valor introduzido não está correto. Certifique-se que introduziu um valor numérico</p>
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
                    <p>O valor introduzido não está correto. Certifique-se que introduziu um valor numérico</p>
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
                break;
            default:
                $tipoCorreto = true;
                break;
        }
        return $tipoCorreto;
    }    
    
    
    /**
     * Disables the values from on entity
     */
    public function desativarVal(){
        
        $this->bd->getMysqli()->autocommit(false);
	$this->bd->getMysqli()->begin_transaction();
        
        $valToDisable = $this->bd->userInputVal($_REQUEST['valOfEnt']);
        $updated_on = date("Y-m-d H:i:s",time());
        $error = false;
        
        //makes the backup
        $valueDis = $this->bd->runQuery("SELECT * FROM value WHERE id=".$valToDisable)->fetch_assoc();
        if($this->gereInsts->addEntToHist($valueDis['entity_id'],$this->bd,$updated_on)){ //-----> backups the entity
            
            $this->bd->runQuery("UPDATE `entity` SET `updated_on`='".$updated_on."' WHERE id=".$valueDis['entity_id']);
            
            $getvals = $this->bd->runQuery("SELECT * FROM value WHERE entity_id = ".$valueDis['entity_id']);
            while($valsToHist = $getvals->fetch_assoc())
            {
                if(!$this->gereInsts->addHistValues($valsToHist['id'],$this->bd,$updated_on))
                {
                  $error = true;
                  break; 
                }
            }
        }
        
        //changes the version
        if(!$this->bd->runQuery("UPDATE `value` SET `state`='inactive',`updated_on`='".$updated_on."' WHERE id=".$valToDisable))
        {
            $error = true;
        }
        
        if($error == false)
        {
?>
            
                <p>O valor selecionada foi desativado.</p>
                <p>Clique em <a href="pesquisa-dinamica/?estado=apresentacao&id=<?php echo$valueDis['entity_id'] ?>"/>Continuar</a> para avançar</p>
<?php            
            $this->bd->getMysqli()->commit();
        }
        else
        {
?>
                <p>O valor selecionado não pôde ser desativado.</p>
                <p>Clique em <?php goBack()?> para voltar à página anterior</p>
<?php            
            $this->bd->getMysqli()->rollback();
        }
        
    }
    /**
     * Activates the values from one entity
     */
    public function ativarVal(){
        $valToEnable = $this->bd->userInputVal($_REQUEST['valOfEnt']);
        $updated_on = date("Y-m-d H:i:s",time());
                
        $this->bd->getMysqli()->autocommit(false);
	$this->bd->getMysqli()->begin_transaction();
        
        $valToDisable = $this->bd->userInputVal($_REQUEST['valOfEnt']);
        $updated_on = date("Y-m-d H:i:s",time());
        $error = false;
        
        //makes the backup
        $valueDis = $this->bd->runQuery("SELECT * FROM value WHERE id=".$valToDisable)->fetch_assoc();
        if($this->gereInsts->addEntToHist($valueDis['entity_id'],$this->bd,$updated_on)){ //-----> backups the entity
            
            $this->bd->runQuery("UPDATE `entity` SET `updated_on`='".$updated_on."' WHERE id=".$valueDis['entity_id']);
            
            $getvals = $this->bd->runQuery("SELECT * FROM value WHERE entity_id = ".$valueDis['entity_id']);
            while($valsToHist = $getvals->fetch_assoc())
            {
                if(!$this->gereInsts->addHistValues($valsToHist['id'],$this->bd,$updated_on))
                {
                  $error = true;
                  break; 
                }
            }
        }
        
        //changes the version
        if(!$this->bd->runQuery("UPDATE `value` SET `state`='active',`updated_on`='".$updated_on."' WHERE id=".$valToDisable))
        {
            $error = true;
        }
        
        if($error == false)
        {
?>
            
                <p>O valor selecionada foi ativado.</p>
                <p>Clique em <a href="pesquisa-dinamica/?estado=apresentacao&id=<?php echo$valueDis['entity_id'] ?>"/>Continuar</a> para avançar</p>
<?php            
            $this->bd->getMysqli()->commit();
        }
        else
        {
?>
                <p>O valor selecionado não pôde ser ativada.</p>
                <p>Clique em <?php goBack()?> para voltar à página anterior</p>
<?php            
            $this->bd->getMysqli()->rollback();
        }
    }
}
    

/**
 * This class will manage the history that is created for the entity table
 */
class entityHist{
                                        
    public function __construct() {
       
    }
    
    /**
     * Adds the previous entity version to the table hist_ent and gives permision to create a new entity
     * @param type $id -> from the entity that we want to change
     * @param type $bd -> object to allow us to make changes in the database
     * @return boolean
     */
    public function addHist($id,$bd){
        $bd->getMysqli()->autocommit(false);
	$bd->getMysqli()->begin_transaction();
        
        $readEnt = $bd->runQuery("SELECT * FROM entity WHERE id=".$id)->fetch_assoc();
        
        $inactive = date("Y-m-d H:i:s",time());
        if(!$bd->runQuery("INSERT INTO `hist_entity`(`id`, `ent_type_id`, `entity_id`, `entity_name`, `state`, `active_on`, `inactive_on`) VALUES (NULL,".$readEnt['ent_type_id'].",".$readEnt['id'].",'".$readEnt['entity_name']."','".$readEnt['state']."','".$readEnt['updated_on']."','".$inactive."')")){
                return false;
        }
        return true;
    }
    
    /**
     * 
     * @param type $id ->Receives the id of the entity that i want to add to the history
     * @param type $bd
     * @param type $inactiveTime -> time of add
     * @return boolean
     */
    public function addEntToHist($id,$bd,$inactiveTime){
        $readEnt = $bd->runQuery("SELECT * FROM entity WHERE id=".$id)->fetch_assoc();
        
        //$inactive = date("Y-m-d H:i:s",time());
        if(!$bd->runQuery("INSERT INTO `hist_entity`(`id`, `ent_type_id`, `entity_id`, `entity_name`, `state`, `active_on`, `inactive_on`) VALUES (NULL,".$readEnt['ent_type_id'].",".$readEnt['id'].",'".$readEnt['entity_name']."','".$readEnt['state']."','".$readEnt['updated_on']."','".$inactiveTime."')")){
                return false;
        }
        return true;
    }
    
    /**
     * Adds the previous values t the table hist_values and gives permission to create a new value
     * @param type $id -> of the value we will change
     * @param type $bd -> database object to alllow 
     * @param type $inactiveTime -> 
     * @return boolean
     */
    public function addHistValues($id,$bd,$inactiveTime){
        $getOldVal = $bd->runQuery("SELECT * FROM value WHERE id=".$id)->fetch_assoc();
        //echo $id;
        if($getOldVal['relation_id'] == "")
        {
            if(!$bd->runQuery("INSERT INTO `hist_value`(`id`, `entity_id`, `property_id`, `value`, `producer`, `relation_id`, `value_id`, `active_on`, `inactive_on`, `state`) VALUES (NULL,".$getOldVal['entity_id'].",".$getOldVal['property_id'].",'".$getOldVal['value']."','".$getOldVal['producer']."',NULL,".$getOldVal['id'].",'".$getOldVal['updated_on']."','".$inactiveTime."','".$getOldVal['state']."')"))
            {
                return false;
            }
        }
        else
        {
            if(!$bd->runQuery("INSERT INTO `hist_value`(`id`, `entity_id`, `property_id`, `value`, `producer`, `relation_id`, `value_id`, `active_on`, `inactive_on`, `state`) VALUES (NULL,".$getOldVal['entity_id'].",".$getOldVal['property_id'].",'".$getOldVal['value']."','".$getOldVal['producer']."',".$getOldVal['relation_id'].",".$getOldVal['id'].",'".$getOldVal['updated_on']."','".$inactiveTime."','".$getOldVal['state']."')"))
            {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Presents the table history to the selected entity
     * @param type $id -> id from the selected entity
     * @param Db_Op $bd
     */
     public function tableHist($bd)
     {
         if (isset($_REQUEST["histAll"])) {
            $this->apresentaHistTodas($bd);
        }
        else {
?>
                <form method="GET">
                                Verificar histórico:<br>
                                <input type="radio" name="controlDia" value="ate">até ao dia<br>
                                <input type="radio" name="controlDia" value="aPartir">a partir do dia<br>
                                <input type="radio" name="controlDia" value="dia">no dia<br>
                                <input type="text" id="datepicker" name="data" placeholder="Introduza uma data">
                                <input type="hidden" name="estado" value="historico">
                                <input type="hidden" name="ent_id" value="<?php echo $id; ?>">
                                <input type="submit" value="Apresentar histórico">
                        </form>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Data de Ativação</th>
                                <th>Data de Desativação</th>
                                <th>Nome</th>
                                <th>Propriedade</th>
                                <th>Valor</th>
                                <th>Estado</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
<?php
                        $id = $bd->userInputVal($_REQUEST['ent_id']); 
                        $data = $bd->userInputVal($_REQUEST['data']);
                        if (isset($_REQUEST["controlDia"]) && $_REQUEST["controlDia"] == "ate") {
                            $presetOld = $bd->runQuery("SELECT * FROM hist_entity WHERE entity_id=".$id." AND inactive_on<='".$data."' ORDER BY inactive_on DESC");   
                        }
                        else if (isset($_REQUEST["controlDia"]) && $_REQUEST["controlDia"] == "aPartir") {
                            $presetOld = $bd->runQuery("SELECT * FROM hist_entity WHERE entity_id=".$id." AND inactive_on>='".$data."' ORDER BY inactive_on DESC");
                        }
                        else if (isset($_REQUEST["controlDia"]) && $_REQUEST["controlDia"] == "dia"){
                            $presetOld = $bd->runQuery("SELECT * FROM hist_entity WHERE entity_id=".$id." AND inactive_on < '".date("Y-m-d",(strtotime($data) + 86400))."' AND inactive_on >= '".$data."' ORDER BY inactive_on DESC");

                        }
                        else {
                            $presetOld = $bd->runQuery("SELECT * FROM hist_entity WHERE entity_id=".$id);
                        }
                        if($presetOld->num_rows == 0)
                        {
?>
                            <tr>
                                    <td colspan="6">Não existe registo referente à entidade selecionada no histórico</td>
                                    <td><?php goBack(); ?></td>
                            </tr>
<?php
                        }
                        else
                        {
                            $oneTimePrint = false;
                            while($readHistory = $presetOld->fetch_assoc()){
                                //echo "SELECT * FROM hist_value WHERE inactive_on = '".$readHistory['inactive_on']."' ORDER BY inactive_on DESC, property_id ASC";
                                $readHistValues = $bd->runQuery("SELECT * FROM hist_value WHERE inactive_on = '".$readHistory['inactive_on']."' ORDER BY inactive_on DESC, property_id ASC");
?>
                                <tr>
                                    <td rowspan="<?php echo $readHistValues->num_rows?>"><?php echo $readHistory['active_on']?></td>
                                    <td rowspan="<?php echo $readHistValues->num_rows?>"><?php echo $readHistory['inactive_on']?></td>
                                    
                                    
<?php
                                    
                                    if($oneTimePrint == false){
                                        if($readHistory['entity_name'] == '')
                                        {
?>
                                            <td rowspan="<?php echo $readHistValues->num_rows?>"><?php echo $readHistory['id']?></td>
<?php
                                        }
                                        else
                                        {
?>
                                        <td rowspan="<?php echo $readHistValues->num_rows?>"><?php echo $readHistory['entity_name']?></td>
<?php
                                        }
?>
<?php
                                    $oneTimePrint = true;
                                    }
?>
                                    
                                    
<?php                               //echo "SELECT * FROM hist_value WHERE entity_id = ".$readHistory['id']." AND inactive_on = '".$readHistory['inactive_on']."'";
                                    //$readHistValues = $bd->runQuery("SELECT * FROM hist_value WHERE inactive_on = '".$readHistory['inactive_on']."'");
                                    $oneTimePrint2 = false;
                                    while($readHV = $readHistValues->fetch_assoc())
                                    {
                                        //echo "SELECT name FROM property WHERE id=".$readHV['property_id'];
                                        $propName = $bd->runQuery("SELECT name FROM property WHERE id=".$readHV['property_id'])->fetch_assoc();
?>
                                            <td><?php echo $propName['name']?></td>

                                        <td><?php echo $readHV['value']?></td>
<?php
                                   
                                   if($oneTimePrint2 == false){
                                       if($readHistory['state'] == 'inactive')
                                       {
?> 
                                        <td rowspan="<?php echo $readHistValues->num_rows?>"><?php echo "Inativo"?></td>
<?php
                                       }
                                       else 
                                       {
?>
                                         <td rowspan="<?php echo $readHistValues->num_rows?>"><?php echo "Ativo"?></td>
<?php
                                       }
?>
                                          <td rowspan="<?php echo $readHistValues->num_rows?>"><a href="?estado=versionBack&histId=<?php echo $readHistory['id']?>">Voltar para esta versão</a></td>
                                
                                     <?php
                                     $oneTimePrint2 = true;
                                   }
                                     $oneTimePrint = false;
                                     ?></tr><?php
                                    }
                                     ?>   
                                
<?php                                
                                    
                            }
                        }
                        
                        
?>
                        </tbody>
                    </table>
<?php
        }
     }
     
     /**
      * Procedes to the version change move a group of values from 
      * hist_entity and hist_value to the value and entity tables.
      * @param type $id -> id from the entity we want to reactivate
      * @param type $bd
      */
     public function changeVersion($id,$bd){
         //Get the entity we want to reactivate
        $getHisEnt = $bd->runQuery("SELECT * FROM hist_entity WHERE id =".$id);
        $readHistEnt = $getHisEnt->fetch_assoc();
        //Get values from the hist_value table 
        $getOldAttr = $bd->runQuery("SELECT * FROM hist_value WHERE inactive_on='".$readHistEnt['inactive_on']."'"); 
         
        //get the actual entity
        $getActEnt = $bd->runQuery("SELECT * FROM entity WHERE id=".$readHistEnt['entity_id']);
        $readActENt = $getActEnt->fetch_assoc();
        //get the actual entity values 
        $getActVal = $bd->runQuery("SELECT * FROM value WHERE entity_id=".$readActENt['id']);
       
        
        //backup the current values
        $bd->getMysqli()->autocommit(false);
	$bd->getMysqli()->begin_transaction();
        
        $errorFound = false;
        
        $updated_on = date("Y-m-d H:i:s",time());
        if(!$bd->runQuery("INSERT INTO `hist_entity`(`id`, `ent_type_id`, `entity_id`, `entity_name`, `state`, `active_on`, `inactive_on`) VALUES (NULL,".$readActENt['ent_type_id'].",".$readActENt['id'].",'".$readActENt['entity_name']."','".$readActENt['state']."','".$readActENt['updated_on']."','".$updated_on."')"))
        {
                echo "#NO BACKUP DA ENTITY";
                $errorFound = true;
        }
        else
        {
            while( $readActVal = $getActVal->fetch_assoc())
            {
            
                if(!$bd->runQuery("INSERT INTO `hist_value`(`id`, `entity_id`, `property_id`, `value`, `producer`, `relation_id`, `value_id`, `active_on`, `inactive_on`, `state`) VALUES (NULL,".$readActVal['entity_id'].",".$readActVal['property_id'].",'".$readActVal['value']."','".$readActVal['producer']."',NULL,".$readActVal['id'].",'".$readActVal['updated_on']."','".$updated_on."','".$readActVal['state']."')")){
                     echo "#NO BACKUP DOS VALUES";
                    $errorFound = true;
                    break;
                }
            }
        }
        
        
        //changes the current valuees and entities to the ones that come from the history
        if(!$bd->runQuery("UPDATE `entity` SET `entity_name`='".$readHistEnt['entity_name']."',`state`='".$readHistEnt['state']."',`updated_on`='".$updated_on."' WHERE id=".$readActENt['id'].""))
        {
             echo "#NO UPDATE DA ENTITY";
            $errorFound = true;
        }
        else 
        {
            //print_r($getOldAttr);
            while($moveToMain = $getOldAttr->fetch_assoc())
            {
                //echo $moveToMain['id'];
                if(!$bd->runQuery("UPDATE `value` SET `entity_id`=".$moveToMain['entity_id'].",`property_id`=".$moveToMain['property_id'].",`value`='".$moveToMain['value']."',`producer`='".$moveToMain['producer']."',`relation_id`=NULL,`state`='".$moveToMain['state']."',`updated_on`='".$updated_on."' WHERE id = ".$moveToMain['value_id']))
                {
                    echo "#NO UPDATE DOS VALUES";
                    $errorFound = true;
                    break;
                }
            }
        }
        
        //check if there is more properties on the history table or more properties on the normal table
        echo "SELECT * FROM value WHERE entity_id=".$readActENt['id']." AND updated_on != '".$updated_on."'";
        $getActVal = $bd->runQuery("SELECT * FROM value WHERE entity_id=".$readActENt['id']." AND updated_on != '".$updated_on."'");
        /*echo "SELECT * FROM hist_value WHERE entity_id=".$readActENt['id']."AND updated_on != '".$updated_on."'";
        $getOldVal = $bd->runQuery("SELECT * FROM hist_value WHERE entity_id=".$readActENt['id']."AND updated_on != '".$updated_on."'");
        */
        //echo $getActVal->num_rows;
        if($getActVal->num_rows > 0)
        {
            while($disableVal = $getActVal->fetch_assoc()){
                //echo $disableVal['id'];
                //echo entrou;
                if(!$bd->runQuery("UPDATE `value` SET `state`='inactive',`updated_on`='".$updated_on."' WHERE id=".$disableVal['id']))
                {
                    $errorFound = true;
                    break;
                }
            }
        }
        
        //Updates if there is no error
        if($errorFound)
        {
?>                    
                <p>Ocorreu um erro. Não atualizou a propriedade para uma versão anterior.</p>
                <p>Clique em <?php goBack() ?> para voltar a página anterior</p>           
<?php
            $bd->getMysqli()->rollback();
        }
        else
        {
?>
                <p>Atualizou a propriedade com sucesso para uma versão anterior.</p>
                <p>Clique em <a href="/pesquisa-dinamica"/>Continuar</a> para avançar</p>                
<?php
            $bd->getMysqli()->commit();
        }
        
        
     }
     
     /**
     * This method creates a table with a view of all the entities in the selected day
     * @param Db_Op $db (object form the class Db_Op)
     */
    private function apresentaHistTodas ($db) {
?>
        <table class="table">
            <thead>
                <tr>
                    <th>Instância</th>
                </tr>
            </thead>
            <tbody>
<?php
                $data = $db->userInputVal($_REQUEST['data']);
                // Queries that select the verion present in the history or in the main table in the given date
                $selecionaHist = "SELECT * FROM hist_entity WHERE (('".$data."' > active_on AND '".$data."' < inactive_on) OR ((active_on LIKE '".$data."%' AND inactive_on < '".$data."') OR inactive_on LIKE '".$data."%')) AND ent_type_id = ".$db->userInputVal($_REQUEST["ent"])." GROUP BY entity_id ORDER BY inactive_on DESC";
                $selecionaEntity = "SELECT * FROM entity WHERE (updated_on < '".$data."' OR updated_on LIKE '".$data."%') AND ent_type_id = ".$db->userInputVal($_REQUEST["ent"]);
                echo $selecionaEntity.$selecionaHist;
                
                $resultSelecionaEntity = $db->runQuery($selecionaEntity);
                $resultSelecionaHist = $db->runQuery($selecionaHist);
?>
                <tr>
<?php
                    // Creates a temporary table with the results of the previous queries, this will be the table that should be printed.
                    $creatTempTable = "CREATE TEMPORARY TABLE temp_table (
                        `id` INT NOT NULL,
                        `ent_type_id` INT NOT NULL,
                        `entity_name` VARCHAR(255) NULL,
                        `state` ENUM('active', 'inactive') NOT NULL)";
                    $creatTempTable = $db->runQuery($creatTempTable);
                    
                    while ($ent = $resultSelecionaEntity->fetch_assoc()) {
                        $db->runQuery("INSERT INTO temp_table VALUES (".$ent['id'].",'".$ent['ent_type_id']."','".$ent['entity_name']."','".$ent['state']."')");
                    }
                    while ($hist = $resultSelecionaHist->fetch_assoc()) {
                       $db->runQuery("INSERT INTO temp_table VALUES (".$hist['entity_id'].",'".$hist['name']."','".$hist['entity_name']."','".$hist['state']."')");
                    }
                    $resultSeleciona = $db->runQuery("SELECT * FROM temp_table GROUP BY id ORDER BY id ASC");
                    
                    while($arraySelec = $resultSeleciona->fetch_assoc())
                    {
?>
                        <tr>
                            <td>
<?php
                            $getEntName = "SELECT * FROM entity WHERE id = ".$arraySelec['id'];
                            if ($db->runQuery($getEntName)->num_rows == 0) {
                                echo $instancias['id'];
                            }
                            else {
                                $entity = $db->runQuery($getEntName)->fetch_assoc();
                                $entity_name = $entity['entity_name'];
                                $entity_id = $entity['id'];
                                if (!empty ($entity_name)) {
?>
                                <?php echo $entity_name;?>
<?php
                                }
                                else {
?>
                                   <?php echo $entity_id;?>
<?php
                                }
                            }
?>
                            </td>
                        </tr>
<?php
                    }
                    $db->runQuery("DROP TEMPORARY TABLE temp_table");
                
?>
            </tbody>
        </table>
<?php
    }
     
}

?>

