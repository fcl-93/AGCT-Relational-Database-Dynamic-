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
    private function tableChsStt()
    {
?>
            <html>
                <form>
<?php
                    $this->showPropEnt();
                    $this->showPropValueType();
?>
                </form>
            </html>
<?php
    }
    
    
    
    
    /**
     * Show a table of entities, where at least the value_type of one o the properties of the selected entity is ent_ref, and fk_ent_type_id 		
     * references the select entity 
     */
    private function showPropValueType(){
        $res_EntRef = $this->bd->runQuery("SELECT ent_type.id, ent_type.name FROM ent_type, property WHERE ent_type.id = property.ent_type_id AND property.value_type = 'ent_ref' AND property.fk_ent_type_id = ".$this->bd->userInputVal($_REQUEST['ent'])."");
    
        if($res_EntRef->num_rows == 0)
	{
?>
            <html>
                <p>Não existem propriedades de entidades que referenciem o tipo de entidade selecionada.</p>
            </html>
<?php                                       
        }
        else
        {
?>
            <h3>Propriedades de entidades que contenham pelo menos uma propriedade que referêncie a entidade selecionada.</h3>
<?php
        }
    }
    
    
    
    
    /**
     * Show the properties for the selected entities
     * the properties will be presented in a table
     */
    private function showPropEnt(){
        $res_GetProp = $this->bd->runQuery("SELECT * FROM property WHERE id=".$this->bd->userInputVal($_REQUEST['ent']));
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
                        $count =0;
                        while($read_GetProp = $res_GetProp->fetch_assoc()){
?>
                            <tr>
                                <td><?php echo $read_GetProp['id'] ?></td>
                                <td><?php echo $read_GetProp['name']?></td>
                                <td><input type="checkbox" name="check<?php echo $count;?>" value="<?php echo $read_GetProp['id']; ?>"></td>
                                <td>
<?php
                                    switch ($read_GetProp['value_type']) {
                                        case 'enum':
                                            //get enum values if the component valu_type is enum
                                            $res_AlldVal = $this->bd->runQuery("SELECT * FROM prop_allowed_value WHERE prop_allowed_value.property_id = ".$read_GetProp['id']." AND prop_allowed_value.state = 'active");
 ?>
                                            <select name="select<?php echo $count ?>">
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
                                             <input type="radio" name="radio<?php echo $count?>" value="true">True
                                             <input type="radio" name="radio<?php echo $count?>" value="false">False
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
						<input type="text" name="double<?php echo $count;?>">
<?php                                                
						break;
					case 'text':
?>
                                            <input type="text" name="textbox<?php echo $count; ?>">
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
                                                    <input type="text" name="int<?php echo $count ?>">
<?php
                                                    break;
					case 'ent_ref':
?>
                                                    <input type="hidden" name="comp_ref" value="<?php echo $read_GetProp['id'] ?>">
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
?>
