<?php
require_once("custom/php/common.php");

$novaRelacao = new InsereRelacoes();

/**
 The methods that are present in this class will handle all the operations that we can do in 
 * the page inserção de relações
 *  @author fabio
 * 
 */
class InsereRelacoes
{
	private $bd;
	/**
	 * Constructor
	 */
	public function __construct(){
		$this->bd = new Db_Op();
		$this->numProp = 0;
		$this->checkUser();
	}
	
	/**
	 *  This method will check if the user as the permission to acess this page
	 * and will handle all the Requests states
	 */
	public function checkUser(){
		$capability = 'insert_relation';
	
		if ( is_user_logged_in() )
		{
			if(current_user_can($capability))
			{
				if(empty($_REQUEST["estado"]))
				{
                                    $this->tablePrint();
				}
				else if($_REQUEST['estado'] == 'editar')
				{
                                    $this->editRlationProps();
				}
				else if($_REQUEST['estado'] == 'associar')
				{
                                    $this->associar();
				}
                                else if($_REQUEST['estado'] == 'introducao')
                                {
                                    $this->secondEntSel();
                                }
				else if($_REQUEST['estado'] == 'inserir')
				{
                                    $this->insertState();
				}
				else if($_REQUEST['estado'] == 'desativar')
				{
                                    $this->desactivate();
				}
                                else if($_REQUEST['estado'] == 'ativar')
                                {
                                    $this->activate();
                                }
			}
			else
			{
?>
				<html>
					<p>Não tem autorização para a aceder a esta página.</p>
				</html>
<?php 
			}
		}
		else 
		{
?>
			<html>
				<p>O utilizador não tem sessão iniciada.</p>
                                 <p>Clique <a href="/login">aqui</a> para iniciar sessão.</p>
			</html>
<?php			
		}
	}
	
	/**
	 * This method will print the table that will be showing all the relations their state and the entity types that 
	 * will be associated to wich relation
	 */
	public function tablePrint()
        {                                
                                $res_Rel = $this->bd->runQuery("SELECT * From relation");
                                 if($res_Rel->num_rows == 0)
                                 {
?>
                                    <html>
                                        <p>Não existem relações.</p>
                                    </html>
<?php
                                 }
                                 else
                                 {
?>                                     
                            <html>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Id</th>
                                            <th>Tipo de relação</th>
                                            <th>Entidade 1</th>
                                            <th>Entidade 2</th>
                                            <th>Estado</th>
                                            <th>Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody>
<?php
                                    while($readRelations = $res_Rel->fetch_assoc()){
                                         $res_EntPart = $this->bd->runQuery("SELECT ent_type1_id, ent_type2_id FROM rel_type WHERE id=".$readRelations['rel_type_id']);
                                         $read_EntPart = $res_EntPart->fetch_assoc();
                                        
                                         $res_name1 = $this->bd->runQuery("SELECT * FROM ent_type WHERE id=".$read_EntPart['ent_type1_id']);
                                         $read_name1 = $res_name1->fetch_assoc(); 
                                         $res_name2 = $this->bd->runQuery("SELECT * FROM ent_type WHERE id=".$read_EntPart['ent_type2_id']);
                                         $read_name2 = $res_name2->fetch_assoc();
?>                                         
                                        <tr>
                                             <td><?php echo $readRelations['id'];?></td>
                                             <td><?php echo $read_name1['name'];?> - <?php echo $read_name2['name'] ?></td>
                                             <td><?php echo $readRelations['entity1_id'];?></td>
                                             <td><?php echo $readRelations['entity2_id'];?></td>
<?php
                                                if($readRelations['state'] == 'active')
                                                {
?>       
                                                        <td>Ativo </td>
                                                        <td>
                                                            <a href="insercao-de-relacoes?estado=editar&rel=<?php echo $readRelations['id'];?>">[Editar Propriedades da Relação]</a>  
                                                            <a href="insercao-de-relacoes?estado=desativar&rel=<?php echo $readRelations['id'];?>">[Desativar]</a>
							</td>
<?php
                                                } 
                                                else
                                                {
?>
                                                    <td>Inativo</td>
                                                    <td>
                                                        <a href="insercao-de-relacoes?estado=editar&rel=<?php echo $readRelations['id'];?>">[Editar Propriedades da Relação]</a>  
                                                        <a href="insercao-de-relacoes?estado=ativar&rel=<?php echo $readRelations['id'];?>">[Ativar]</a>
                                                   </td>
<?php   
                                                }
?>
                                         </tr>
<?php
                                     }
                                 }
?>                               
                                </tbody>
                            </table>
                        </html>
<?php
        }
	
        /**
	 * This will make you edit values for the selected relation
	 */
        public function editRlationProps(){
            //get relation tipo from the relation selected
            $res_relTypeId = $this->bd->runQuery("SELECT rel_type_id FROM relation WHERE id=".$_REQUEST['rel']);
            $read_relTypeId = $res_relTypeId->fetch_assoc();
            
            //get the properties that are associated to the rel_type where the previousçy selected relation belongs. 
            $res_GetPropFromRelType = $this->bd->runQuery("SELECT * FROM property WHERE rel_type_id=".$read_relTypeId['rel_type_id']);
            
            
            $res_PropAded = $this->bd->runQuery("SELECT * FROM value WHERE relation_id=".$_REQUEST['rel']);
            
            
            //Show a table with properties who can be added.
            if($res_PropAded->num_rows != $res_GetPropFromRelType->num_rows)//se o numero de instancias de propriedades de uma relação é menor que o numero de propriedades 
                //não é igual ao numero de propriedades da tabela propertyy significa que ainda posso adicionar mais propriedades
            {
?>
                <h3>Inserção de Relações - Propriedades das Relações</h3>
<?php
                $this->possibleValuesToAdd($res_PropAded,$res_GetPropFromRelType,$_REQUEST['rel']);
            }
            else
            {
?>
                        <html>
                            <p>Não existem propriedades que possam ser adicionadas.</p>
                        </html>
<?php
            }
           
            //Mostrar uma tabela com as propriedades já adicionadas e os respetivos valores.
            
           
            
            
            
        } 
       
        /**
         * This method prints a table with all the property values that are no assigned to the selectd relation
         * @param type $res_PropAded
         * @param type $res_GetPropFromRelType
         */
        private function possibleValuesToAdd($res_PropAded,$res_GetPropFromRelType,$idDaRel)
        {
            
?>
                        <html>
                            <form>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <td>Id</td>
                                        <td>Nome propriedade</td>
                                        <td>Tipo</td>
                                        <td>Seleção</td>
                                        <td>Novo valor</td>
                                    </tr>
                                </thead>
                                <tbody>
<?php
                                $conta = 0;
                                while($read_GetPropFromRelType = $res_GetPropFromRelType->fetch_assoc())
                                {
                                    //$res_CanBeAdded = $this->bd->runQuery("SELECT * FROM value as v WHERE v.relation_id=".$idFromRel." AND v.property_id !=".$res_GetPropFromRelType['id']);
?>                                  
                                    <tr>
                                        <td><?php echo $read_GetPropFromRelType['id']; ?></td>
                                        <td><?php echo $read_GetPropFromRelType['name']; ?></td>
                                        <td><?php  echo $read_GetPropFromRelType['value_type'];?></td>
                                        <td><input type="checkbox" name="check<?php echo $conta; ?>" value="<?php echo $read_GetPropFromRelType['id']?>"></td>
                                        <td>
<?php
                                            //verifies the value type
                                            if($read_GetPropFromRelType['value_type'] == 'bool')
                                            {
?>
                                                <input type="radio" name="<?php echo 'radio'.$conta ?>" value="true">True
                                                <input type="radio" name="<?php echo 'radio'.$conta ?>" value="false">False
<?php
                                            }
                                            else if($read_GetPropFromRelType['value_type'] == 'enum')
                                            {   
                                                $res_EnumValue = $this->bd->runQuery("SELECT * FROM prop_allowed_value WHERE property_id=".$read_GetPropFromRelType['id']);
?>
                                                <select>
<?php
                                                while($read_EnumValue = $res_EnumValue->fetch_assoc())
                                                {
?>
                                                    <option name="<?php echo 'select'.$conta ?>" value="<?php echo $read_EnumValue['id']; ?>"><?php echo $read_EnumValue['value']; ?></option>
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
                                            $conta++;
                                            
?>          
                                        </td>
                                    </tr>
<?php
                                $_SESSION['propImpressas'] = $conta;
                                }
?>                             
                                </tbody>
                            </table>
                                    <input type="hidden" name="iddarel" value="<?php echo $idDaRel ?>" >
                                    <input type="hidden" name="flag" value="atributosNovos">
                                    <input type="hidden" name="estado" value="inserir">
                                    <input type="submit" value="Adicionar Novas Propriedades">
                            </form>
                        </html>
<?php
        }
        
	/**
	 * Server side validation when JQuery is disabled
	 */
	public function ssvalidation(){
                /*$control = true;
                 for($i = 0; $i <  $_SESSION['valueNumber']; $i++)
                {
                    if(isset($_REQUEST['valSel'.$i]))
                    {
                        if(isset($REQUEST['valor'.$i]))//check if for tge selected box there is always a value associated if not abort the submission operation
                        {
                            
                        }
                        else
                        {
                            $control = false;
                            break;
                        }
                    }
                }
            $controlaCheck = 0;
                    for($i = 1; $i <= $_SESSION['propSelected']; $i++)
                    {
                            if(empty($_REQUEST["idProp".$i]))
                            {
                                    $controlaCheck++;
                            }
                    }
                    
            if($controlaCheck == $_SESSION['propSelected'])
            {}
                
            
             for($i = 1; $i <= $_SESSION['propSelected']; $i++)
                    {
                            if((!is_numeric($_REQUEST["ordem".$i]) || $_REQUEST["ordem".$i] < 1) && isset($_REQUEST["idProp".$i]))
                            {
                            
                            }
                    }*/
            return true;
        }
	/**
         * Associates the newy creaed value and associates it with another 
         * existing value.
         */
	public function associar(){
            $res_EntType = $this->bd->runQuery("SELECT * FROM entity WHERE id=". $_REQUEST['ent']);
            $read_EntType = $res_EntType->fetch_assoc();
            //print_R($res_EntType);
            $res_RelTypes = $this->bd->runQuery("SELECT * FROM rel_type WHERE ent_type1_id=".$read_EntType['ent_type_id']." OR ent_type2_id=".$read_EntType['ent_type_id']);
            //echo "SELECT * FROM rel_type WHERE ent_type1_id=".$read_EntType['ent_type_id']." OR ent_type2_id=".$read_EntType['ent_type_id'];

 ?>          
            <h3>Inserção de Relações - Lista Tipos de relação</h3>
            <html>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Tipo de Relacao</th>
                        </tr>
                    </thead>
                    <tbody>
<?php
                        while($read_RelTypes = $res_RelTypes->fetch_assoc())
                        {
                            $res_name1 = $this->bd->runQuery("SELECT * FROM ent_type WHERE id=".$read_RelTypes['ent_type1_id']);
                            $read_name1 = $res_name1->fetch_assoc(); 
                            $res_name2 = $this->bd->runQuery("SELECT * FROM ent_type WHERE id=".$read_RelTypes['ent_type2_id']);
                            $read_name2 = $res_name2->fetch_assoc(); 
?>
                        <tr>
                            <td><?php echo $read_RelTypes['id']?></td>
                            <td>
                                <a href="insercao-de-relacoes?estado=introducao&ent=<?php echo $_REQUEST['ent']; ?>&rel_type=<?php echo $read_RelTypes['id'];?>">[<?php echo $read_name1['name'];?> - <?php echo $read_name2['name'];?>]</a>
                            </td>
                        </tr>
<?php
                        }
?>
                    </tbody>
                </table>
            </html>
 <?php         
        }
        
        /**
         * This method will be able to prvide the user a list of entities that are compatible with the 
         * relation_type choosen in the associar state
         */
        public function secondEntSel(){
            $prev_SelEnt = $_REQUEST['ent'];
            $sltd_RelType = $_REQUEST['rel_type'];
            
            $res_CompRel = $this->bd->runQuery("SELECT * FROM rel_type WHERE id=".$sltd_RelType);
            $read_CompRel =$res_CompRel->fetch_assoc();
            
            $res_InsType = $this->bd->runQuery("SELECT * FROM entity WHERE id=".$prev_SelEnt);
            $read_InsType = $res_InsType->fetch_assoc();
            
            if($read_CompRel['ent_type1_id'] == $read_InsType['ent_type_id'])
            {
               $res_SencondEnt =  $this->bd->runQuery("SELECT entity.id, entity.entity_name FROM rel_type, entity WHERE rel_type.ent_type2_id = entity.ent_type_id AND rel_type.ent_type2_id=".$read_CompRel['ent_type2_id']);
?>
                <html>
                    <form>
<?php
                    $control = 0;
                    while($read_SecondEnt = $res_SencondEnt->fetch_assoc())
                    {
                        if(isset($read_SecondEnt['entity_name']))
                        {
?>
                            <input type="checkbox" name="secondEnt<?php echo $control; ?>" value="<?php echo $read_SecondEnt['id'];?>"><?php echo $read_SecondEnt['entity_name']; ?><br>
<?php
                        }
                        else
                        {               //if the user didn't fave any name to the entity e need to search for the attribute of that entity who has a name.
                            
                        }
                        $control++;
                    }
                    $_SESSION['numEnt2Max'] = $control; 
?>
                    <input type="hidden" name="rel_type" value="<?php echo $sltd_RelType;?>">
                    <input type="hidden" name="firstEnt" value="<?php echo  $prev_SelEnt?>">
                    
                    <input type="hidden" name="flag" value="naoeditar">
                    <input type="hidden" name="estado" value="inserir">
                    <input type="submit" value="Associar Segunda Entidade">
                    </form>
                </html>
<?php               
            }
            else if( $read_CompRel['ent_type2_id'] == $read_InsType['ent_type_id'])
            {
                $res_SencondEnt =  $this->bd->runQuery("SELECT entity.id, entity.entity_name FROM rel_type, entity WHERE rel_type.ent_type1_id = entity.ent_type_id  AND rel_type.ent_type1_id=".$read_CompRel['ent_type1_id']);
?>
                <html>
                    <form>
<?php
                    $control = 0;
                    while($read_SecondEnt = $res_SencondEnt->fetch_assoc())
                    {
                        if(isset($read_SecondEnt['entity_name']))
                        {
?>
                            <input type="checkbox" name="secondEnt<?php echo $control; ?>" value="<?php echo $read_SecondEnt['id'];?>"><?php echo $read_SecondEnt['entity_name']; ?><br>
<?php
                        }
                        else
                        {               //if the user didn't fave any name to the entity e need to search for the attribute of that entity who has a name.
                            
                        }
                        $control++;
                    }
                    $_SESSION['numEnt2Max'] = $control; 
?>
                     <input type="hidden" name="rel_type" value="<?php echo $sltd_RelType;?>">
                    <input type="hidden" name="firstEnt" value="<?php echo  $prev_SelEnt?>">
                    
                    <input type="hidden" name="flag" value="naoeditar">
                    <input type="hidden" name="estado" value="inserir">
                    <input type="submit" value="Associar Segunda Entidade">
                    </form>
                </html>
<?php                              
            }
            else
            {
?>
                <html>
                    <p>Erro, o tipo de relação selecionado anteriormente não é compatível com a entidade criada.</p>
                </html>
<?php
            }
            
        }
	
	/**
	 * This method will activate the relation the user selected.
	 */
	public function activate(){
            if($this->bd->runQuery("UPDATE relation SET state='active' WHERE id=".$_REQUEST['rel']))
            {
?>
                <html>
                    <p>A relação foi ativada.</p>
                    <p>Clique em <a href="/insercao-de-relacoes"/>Continuar</a> para avançar</p>
                </html>
<?php
            }
            else
            {
                ?>
                <html>
                    <p>A ativação da relação falhou.</p>
                </html>
                <?php
                goBack();
            }
        }
        
        /**
	 * This method will desactivate the relation the user selected.
	 */
	public function desactivate(){
            if($this->bd->runQuery("UPDATE relation SET state='inactive' WHERE id=".$_REQUEST['rel']))
            {
?>
                <html>
                    <p>A relação foi desativada.</p>
                    <p>Clique em <a href="/insercao-de-relacoes"/>Continuar</a> para avançar</p>
                </html>
<?php
            }
            else
            {
                ?>
                <html>
                    <p>A desativação da relação falhou.</p>
                </html>
                <?php
                goBack();
            }
        }
	
	/**
	 * This method will handle the insertion that a user will make in the database.
	 */
	public function insertState()
        {
            if($this->ssvalidation())
            {
                if($_REQUEST['flag'] == 'editar')
                {
                    $this->edita();
                }
                else if($_REQUEST['flag'] == 'naoeditar')
                {
                    $this->nedita();
                }
                else if ($_REQUEST['flag'] =='atributosNovos')
                {
                    $this->addNewAttr();
                }
            }
            else 
            {
                goBack();
            }
        }
        
        /**
         *This method will insert new atributtes 
         */
        private function addNewAttr()
        {
            for($i= 0; $i <= $_SESSION['propImpressas']; $i++ )
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
                    
                    
                    
                   if($this->bd->runQuery("INSERT INTO `value`(`id`, `entity_id`, `property_id`, `value`, `date`, `time`, `producer`, `relation_id`) VALUES (NULL,NULL,".$_REQUEST['check'.$i].",".$newValue.",".date("Y-m-d").",".date("H:i:s").",".wp_get_current_user()->user_login.",".$_REQUEST['iddarel'].")"))
                   {
?>
                    <html>
                        <p>As propriedades foram adicionadas à relação.</p>
                    </html>
<?php
                   }
                   else
                   {
?>
                    <html>
                        <p>Erro ao Adicionar as propriedades.</p>
                    </html>
<?php
                   }
                    
                }
            }
        }
        
        /**
         * Method that will only update existing values in the database
         */
        private function edita(){
            
        }
        /**
         * Mehtod that will insert new values in the database.
         */
        private function nedita(){
            //a preencher
            $rel_name="";
            for($i=0; $i <= $_SESSION['numEnt2Max'];$i++){    
                if(isset($_REQUEST['secondEnt'.$i])){
                    if($this->bd->runQuery("INSERT INTO `relation`(`id`, `rel_type_id`, `entity1_id`, `entity2_id`, `relation_name`, `state`) VALUES (NULL,".$_REQUEST['rel_type'].",".$_REQUEST['firstEnt'].",".$_REQUEST['secondEnt'.$i].",'".$rel_name."','active')"))
                    {
                        
?>
                        <html>
                            <p>Associou com sucesso a entidade xxx, a entidade yyy.</p>
                            <p>Clique em <a href="insercao-de-relacoes?estado=editar&rel=<?php echo $this->bd->getMysqli()->insert_id; ?>"/>Inserir Propriedades</a> para preencher informações relativas a relação que acabou de criar.</p>
                        </html>
 <?php
                    }
                    else
                    {
                    
                    }
                }
        
                
            }
        }
}
?>
