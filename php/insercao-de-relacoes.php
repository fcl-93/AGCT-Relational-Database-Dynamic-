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
        private $gereInsRel;
	/**
	 * Constructor
	 */
	public function __construct(){
		$this->bd = new Db_Op();
                $this->gereInsRel = new RelHist();
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
                    else if($_REQUEST['estado'] == 'historico')
                    {
                        if(isset($_REQUEST['histAll']))
                        {
                            if ($this->bd->validaDatas($_REQUEST['data'])) {
                                $this->gereInsRel->tableState($this->bd->userInputVal($_REQUEST['data']),$this->bd);
                            }
                        }
                        else{
                            $this->gereInsRel->showHist($this->bd);
                        }
                    }
                     else if($_REQUEST['estado'] == 'voltar')
                    {
                        $this->gereInsRel->estadoVoltar($this->bd);
                    }
                    else if($_REQUEST['estado'] == 'ativarVal')
                    {
                        $this->activateVal();
                    }
                    else if($_REQUEST['estado'] == 'desativarVal')
                    {
                        $this->desactivateVal();
                    }
                    else if($_REQUEST['estado'] == 'histVal')
                    {
                        $this->histVal();
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
?>
        <form method="GET">
            Verificar propriedades existentes no dia : 
            <input type="text" class="datepicker" id="datepicker" name="data" placeholder="Introduza uma data"> 
            <input type="hidden" name="estado" value="historico">
            <input type="hidden" name="histAll" value="true">
            <input type="submit" value="Apresentar propriedades">
        </form>
<?php
                                $res_Rel = $this->bd->runQuery("SELECT * From relation");
                                $numdeRels = $res_Rel->num_rows; 
                                 if($numdeRels == 0)
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
                                            <th>Propriedade</th>
                                            <th>Valor</th>
                                            <th>Estado</th>
                                            <th>Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody>
<?php
                                    while($readRelations = $res_Rel->fetch_assoc()){
                                         $read_RelName = $this->bd->runQuery("SELECT name FROM rel_type WHERE id=".$readRelations['rel_type_id']);
                                         $read_RelProps = $this->bd->runQuery("SELECT * FROM property WHERE rel_type_id=".$readRelations['rel_type_id']);
                                         $num_props = $read_RelProps->num_rows;
                                         $read_RelName = $read_RelName->fetch_assoc();
?>                                         
                                        <tr>
                                             <td rowspan="<?php echo $num_props?>"><?php echo $readRelations['id'];?></td>
                                             <td rowspan="<?php echo $num_props?>"><?php echo $read_RelName['name'] ?></td>
                                             <td rowspan="<?php echo $num_props?>" data-showHidden="true">
<?php 
                                                $_readEnt1 = $this->bd->runQuery("SELECT entity_name FROM entity WHERE id=".$readRelations['entity1_id'])->fetch_assoc();
                                                if($_readEnt1['entity_name'] != '')
                                                {
                                                    echo $_readEnt1['entity_name'];
                                                }
                                                else
                                                {
                                                    echo $readRelations['entity1_id'];
                                                }
                                                
						$sanitizeId = $this->bd->userInputVal($readRelations['entity1_id']);
						$res_GetVal = $this->bd->runQuery("SELECT * FROM value WHERE entity_id=".$sanitizeId);
?>
<?php
                                                $count = 0;
                                             	while($read_Props = $res_GetVal->fetch_assoc())
                                             	{
                                             		$nome = $this->bd->runQuery("SELECT * FROM property WHERE id=".$read_Props['property_id'])->fetch_assoc()['name'];
?>
                                                        <p hidden="hidden"><span><?php echo $nome." : ".$read_Props['value']."\n"; $count++;?></span></p>										
<?php
                                             	}
                                                if($count == 0)
                                                {
                                                    ?>
                                                    <p hidden="hidden"><span>Não existem valores de propriedades para esta entidade</span></p>
                                                 <?php
                                                }                                  
?>                           
                                             </td>
                                           
                                             <td rowspan="<?php echo $num_props?>" data-showHidden="true">
<?php   
                                            $_readEnt2 = $this->bd->runQuery("SELECT entity_name FROM entity WHERE id=".$readRelations['entity2_id'])->fetch_assoc();
                                            if($_readEnt2['entity_name'] != '')
                                            {
                                                echo $_readEnt2['entity_name'];
                                            }
                                            else
                                            {
                                                echo $readRelations['entity2_id'];
                                            }
                                             
                                             $sanitizeId = $this->bd->userInputVal($readRelations['entity2_id']);
                                             $res_GetVal = $this->bd->runQuery("SELECT * FROM value WHERE entity_id=".$sanitizeId);
                                             $count = 0;
                                             while($read_Props = $res_GetVal->fetch_assoc())
                                             {
                                             	$nome = $this->bd->runQuery("SELECT * FROM property WHERE id=".$read_Props['property_id'])->fetch_assoc()['name'];
?>
                                                <p hidden="hidden"><span><?php echo $nome." : ".$read_Props['value']."\n"; $count++ ?></span></p>												
<?php
                                             }
                                             if($count == 0)
                                             {
?>
                                                 <p hidden="hidden"><span>Não existem valores de propriedades para esta entidades</span></p>
<?php                                       }
?>                         
                                             </td>
<?php                                       
                                            $count = 0;
                                            while($relProps = $read_RelProps->fetch_assoc()){
?>
                                                    <td><?php echo $relProps['name']?></td>
<?php
                                                    $getValName = $this->bd->runQuery("SELECT value FROM value WHERE property_id=".$relProps['id']." AND relation_id=".$readRelations['id'])->fetch_assoc();
?>
                                                    <td><?php 
                                                    if(empty($getValName['value'] ))
                                                    {
                                                        echo "Sem valor atribuído";
                                                    }
                                                    else
                                                    {
                                                        echo $getValName['value'];
                                                    }
                                                    ?></td>
<?php
                                                    if($count == 0)
                                                    {
                                                        $getRel = $this->bd->runQuery("SELECT * FROM rel_type WHERE id=".$readRelations['rel_type_id']." AND updated_on >'".$readRelations['updated_on']."'");
                                                        if($readRelations['state'] == 'active')
                                                        {
                                                   
?>                                                  

                                                                <td rowspan="<?php echo $num_props?>">Ativo </td>
                                                                <td rowspan="<?php echo $num_props?>">
<?php
                                                                if($getRel->num_rows == 0){
?>
                                                                    <a href="insercao-de-relacoes?estado=editar&rel=<?php echo $readRelations['id'];?>">[Inserir/Editar Propriedades da Relação]</a>  
<?php                                                           }
?>
                                                                    <a href="insercao-de-relacoes?estado=desativar&rel=<?php echo $readRelations['id'];?>">[Desativar]</a>
                                                                    <a href="insercao-de-relacoes?estado=historico&rel=<?php echo $readRelations['id'];?>">[Histórico]</a>
                                                                </td>
<?php
                                                        } 
                                                        else
                                                        {
?>
                                                            <td rowspan="<?php echo $num_props?>">Inativo</td>
                                                            <td rowspan="<?php echo $num_props?>">
<?php
                                                            if($getRel->num_rows == 0){
?>
                               
                                                                <a href="insercao-de-relacoes?estado=editar&rel=<?php echo $readRelations['id'];?>">[Inserir/Editar Propriedades da Relação]</a>  
<?php
                                                            }
?>
                                                               <a href="insercao-de-relacoes?estado=ativar&rel=<?php echo $readRelations['id'];?>">[Ativar]</a>
                                                                <a href="insercao-de-relacoes?estado=historico&rel=<?php echo $readRelations['id'];?>">[Histórico]</a>
                                                           </td>
<?php   
                                                        }
                                                        
                                                    }
?>                                                    
                                                    
                                                    
                                                    
                                                </tr>
<?php
                                                $count++;
                                            }







                                                 
                                                 
                                                 
?>    
                                                 
                                                 
<?php
                                                if($count == 0){
                                               $getRel = $this->bd->runQuery("SELECT * FROM rel_type WHERE id=".$readRelations['rel_type_id']." AND updated_on >'".$readRelations['updated_on']."'");
                                                if($readRelations['state'] == 'active')
                                                {
                                                   
?>                                                  
                                             
                                                        <td rowspan="<?php echo $num_props?>">Ativo </td>
                                                        <td rowspan="<?php echo $num_props?>">
<?php
                                                            if($getRel->num_rows == 0){
?>
                                                            <a href="insercao-de-relacoes?estado=editar&rel=<?php echo $readRelations['id'];?>">[Inserir/Editar Propriedades da Relação]</a>  
<?php                                                       }
?>
                                                            <a href="insercao-de-relacoes?estado=desativar&rel=<?php echo $readRelations['id'];?>">[Desativar]</a>
                                                            <a href="insercao-de-relacoes?estado=historico&rel=<?php echo $readRelations['id'];?>">[Histórico]</a>
							</td>
<?php
                                                } 
                                                else
                                                {
?>
                                                    <td rowspan="<?php echo $num_props?>">Inativo</td>
                                                    <td rowspan="<?php echo $num_props?>">
<?php
                                                            if($getRel->num_rows == 0){
?>
                               
                                                        <a href="insercao-de-relacoes?estado=editar&rel=<?php echo $readRelations['id'];?>">[Inserir/Editar Propriedades da Relação]</a>  
<?php

                                                            }
?>
                                                        <a href="insercao-de-relacoes?estado=ativar&rel=<?php echo $readRelations['id'];?>">[Ativar]</a>
                                                        <a href="insercao-de-relacoes?estado=historico&rel=<?php echo $readRelations['id'];?>">[Histórico]</a>
                                                   </td>
<?php   
                                                }
                                            }
?>
                                         </tr>
<?php
                                     }
                                 }
?>                               
                                </tbody>
                            </table>   
                            <div id="pager" class="tablesorter-pager">
                              <form>
                                <img src="/custom/images/first.png" class="first"/>
                                <img src="/custom/images/prev.png" class="prev"/>
                                <span class="pagedisplay"></span> <!-- this can be any element, including an input -->
                                <img src="/custom/images/next.png" class="next"/>
                                <img src="/custom/images/last.png" class="last"/>
                                <select class="pagesize">
                                  <option value="10">10</option>
                                  <option value="20">20</option>
                                  <option value="30">30</option>
                                  <option value="40">40</option>
                                  <option value="<?php echo $numdeRels?>">All Rows</option>
                                </select>
                              </form>
                            </div>
                    </html>

<?php
        $this->createNewRel();
        }
        
        
	
        /**
	 * This will make you edit values for the selected relation or add the value 
         * prints two tables 
         * one with all the possible properties you can add to your relation depending on the rel_type
         * and the other with the existing properties and their values in your ralation
	 */
        public function editRlationProps(){
            //get relation type from the relation selected
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
                <h3>Inserção de Relações - Inserção de propriedades das relações</h3>
<?php
                $this->possibleValuesToAdd($read_relTypeId['rel_type_id'],$_REQUEST['rel']);
            }
            else
            {
?>
                        <html>
                             <h3>Inserção de Relações - Inserção de propriedades das relações</h3>
                            <p>Não existem propriedades que possam ser adicionadas.</p>
                        </html>
<?php
            }
           
            //Show a table with all the property values associated to the selected relation
?>
            <h3>Inserção de Relações - Alteração de propriedades das relações</h3>
    <?php   
           $res_GetPropRel = $this->bd->runQuery("SELECT * FROM value WHERE relation_id = ".$_REQUEST['rel']);
           if($res_GetPropRel->num_rows == 0)
           {
?>               
               <html>
                <p>Não existem propriedades associadas a esta relação.</p>
                <p>Pelo que não pode efetuar nenhuma alteração.</p>
               </html>
 <?php
           }
           else
           {
               $this->changePropValue($read_relTypeId['rel_type_id'],$_REQUEST['rel'],$res_GetPropRel);
           }
            
            
            
        } 
       
        /**
         * This method prints a table with all the property values that are no assigned to the selectd relation
         * @param type $res_PropAded
         * @param type $res_GetPropFromRelType
         */
        private function possibleValuesToAdd($tipyRelSel,$idDaRel)
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
                                $res_CanBeAdded = $this->bd->runQuery("SELECT DISTINCT p.id, p.name, p.value_type FROM value as v, property as p WHERE p.rel_type_id=".$tipyRelSel." AND p.id NOT IN (SELECT value.property_id FROM value WHERE value.relation_id = ".$idDaRel.") ");
                               while($read_CanBeAdded = $res_CanBeAdded->fetch_assoc())
                                {
?>                                  
                                    <tr>
                                        <td><?php echo $read_CanBeAdded['id']; ?></td>
                                        <td><?php echo $read_CanBeAdded['name']; ?></td>
                                        <td><?php  echo $read_CanBeAdded['value_type'];?></td>
                                        <td><input type="checkbox" name="check<?php echo $conta; ?>" value="<?php echo $read_CanBeAdded['id']?>"></td>
                                        <td>
<?php
                                            //verifies the value type
                                            if($read_CanBeAdded['value_type'] == 'bool')
                                            {
?>
                                                <input type="radio" name="<?php echo 'radio'.$conta ?>" value="true">True
                                                <input type="radio" name="<?php echo 'radio'.$conta ?>" value="false">False
<?php
                                            }
                                            else if($read_CanBeAdded['value_type'] == 'enum')
                                            {   
                                                $res_EnumValue = $this->bd->runQuery("SELECT * FROM prop_allowed_value WHERE property_id=".$read_CanBeAdded['id']);
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
                                            $conta++;
                                            
?>          
                                        </td>
                                    </tr>
<?php
                                }
                                $_SESSION['propImpressas'] = $conta;
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
         * This method prints a table with all the properties that are associted to the select relation
         * @param type $tipyRelSel
         * @param type $idDaRel
         * @param type $res_GetPropRel -> mysqli object
         */
        private function changePropValue($tipyRelSel,$idDaRel,$res_GetPropRel)
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
                                <td>Valor Atual </td>
                                <td>Estado</td>
                                <td>Seleção</td>
                                <td>Novo valor</td>
                                <td>Ação</td>
                            </tr>
                        </thead>
                        <tbody>
<?php
                            $conta = 0;
                            while($read_GetPropRel = $res_GetPropRel->fetch_assoc())
                            {
                                $res_Prop= $this->bd->runQuery("SELECT p.id, p.name, p.value_type FROM property as p WHERE id =".$read_GetPropRel['property_id']);
                                $read_PropValues = $res_Prop->fetch_assoc();
?>
                            <tr>
                                <td><?php echo $read_PropValues['id'];?></td>
                                <td><?php echo $read_PropValues['name']?></td>
                                <td><?php echo $read_PropValues['value_type']?></td>
                                <td><?php echo $read_GetPropRel['value'] ?></td>
<?php
                                if($read_GetPropRel['state'] == 'active')
                                {
?>       
                                    <td>Ativo </td>
<?php
                                }
                                else
                                {
?>
                                    <td>Inativo</td>
<?php
                                }
?>
                                <td><input type="checkbox" name="check<?php echo $conta; ?>" value="<?php echo $read_GetPropRel['id']?>"></td>
                                <td>
<?php
                                    if($read_PropValues['value_type'] == 'bool')
                                    {
                                        
?>
                                        <input type="radio" name="<?php echo 'radio'.$conta ?>" value="true">True
                                        <input type="radio" name="<?php echo 'radio'.$conta ?>" value="false">False
<?php
                                    }
                                    else if($read_PropValues['value_type'] == 'enum')
                                    {   
                                        $res_EnumValue = $this->bd->runQuery("SELECT * FROM prop_allowed_value WHERE property_id=".$read_GetPropRel['property_id']);
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
<?php
                                if($read_GetPropRel['state'] == 'active')
                                {
?>       
                                    <td>
                                        <a href="insercao-de-relacoes?estado=desativarVal&rel=<?php echo $_REQUEST['rel'];?>&val=<?php echo $read_GetPropRel['id'];?>">[Desativar]</a>
                                        <a href="insercao-de-relacoes?estado=histVal&rel=<?php echo $_REQUEST['rel'];?>&val=<?php echo $read_GetPropRel['id'];?>">[Histórico]</a>
                                    </td>
<?php
                                } 
                                else
                                {
?>
                                    <td>
                                        <a href="insercao-de-relacoes?estado=ativarVal&rel=<?php echo $_REQUEST['rel'];?>&val=<?php echo $read_GetPropRel['id'];?>">[Ativar]</a>
                                        <a href="insercao-de-relacoes?estado=histVal&rel=<?php echo $_REQUEST['rel'];?>&val=<?php echo $read_GetPropRel['id'];?>">[Histórico]</a>
                                   </td>
<?php   
                                }
?>
                            </tr>
<?php   
                                $conta++;
                            }
                            $_SESSION['attrDaRelImp'] = $conta;
?>
                        </tbody>
                    </table>
                
                    <input type="hidden" name="iddarel" value="<?php echo $idDaRel ?>" >
                    <input type="hidden" name="flag" value="UpdateAttr">
                    <input type="hidden" name="estado" value="inserir">
                    <input type="submit" value="Atualizar Propriedades">
                </form>
           </html>
<?php                    
        }
        
        /**
         * Get all the value of a relation from the history and prints them.
         */
        public function histVal(){
            $queruGetVals = "SELECT * FROM hist_value WHERE value_id=".$this->bd->userInputVal($_REQUEST['val']);
            echo $queruGetVals;
            $runVals = $this->bd->runQuery($queruGetVals);
?>
            <table class="table">
                <thead>
                    <th>Data Início</th>
                    <th>Data Fim </th>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th>Valor</th>
                    <th>Ação</th>
                </thead>
                <tbody>
<?php
                while($rdVal = $runVals->fetch_assoc()){
?>
                    <tr>
                    <td><?php echo $rdVal['active_on'] ?></td>
                    <td><?php echo $rdVal['inactive_on'] ?></td>
<?php
                    $getProp = $this->bd->runQuery("SELECT * FROM property WHERE id=".$rdVal['property_id']." ORDER BY id DESC")->fetch_assoc();
?>  
                    <td><?php echo $getProp['name'] ?></td>
                    <td><?php echo $getProp['value_type'] ?></td>
                    <td><?php echo $rdVal['value'] ?></td>
                    <td><?php echo "Voltar ATrás"?></td>
                    </tr>
<?php
                }
?>
                </tbody>
            </table>
<?php
        }
        
	/**
         * Associates the newy creaed value and associates it with another 
         * existing value.
         */
	public function associar(){
            if(empty($this->bd->userInputVal($_REQUEST['ent'])))
            {
?>
                 <h3>Inserção de Relações - Lista Tipos de relação</h3>
                  <p>Não selecionou uma entidade.</p>
                  <p>Clique em <?php goBack(); ?> para voltar à página anterior</p>
<?php
                 exit;
            }
            else {
                if(is_numeric($_REQUEST['ent']))
                {
                }
                else
                {
                    //this comes from the bottom it cuts out of the string the url that is used to make the ajax pushes
                    
                  $_REQUEST['ent'] = substr_replace($this->bd->userInputVal($_REQUEST['ent']),"",0,12);    
                }
            }
            //after
            $res_EntType = $this->bd->runQuery("SELECT * FROM entity WHERE id=".$this->bd->userInputVal($_REQUEST['ent']));
            $read_EntType = $res_EntType->fetch_assoc();
            //print_R($res_EntType);
            $res_RelTypes = $this->bd->runQuery("SELECT * FROM rel_type WHERE ent_type1_id=".$read_EntType['ent_type_id']." OR ent_type2_id=".$read_EntType['ent_type_id']);
            //echo "SELECT * FROM rel_type WHERE ent_type1_id=".$read_EntType['ent_type_id']." OR ent_type2_id=".$read_EntType['ent_type_id'];
            if($res_RelTypes->num_rows == 0 )
            {
?>
                 <h3>Inserção de Relações - Lista Tipos de relação</h3>
                 <p>Não existem tipos de relação aos quais a entidade selecionada possa ser associada.</p>
                 <p>Clique em <?php goBack(); ?> e selecione outra entidade.</p>
<?php
            }
            else
            {
 ?>          
            <h3>Inserção de Relações - Lista Tipos de relação</h3>
            <html>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Tipo de Relação</th>
                        </tr>
                    </thead>
                    <tbody>
<?php
                        if($res_RelTypes->num_rows == 0)
                        {
?>
                            <td colspan="2"> Não existem tipos de relações compativeis com a entidade escolhida</td>
<?php                        }
                        while($read_RelTypes = $res_RelTypes->fetch_assoc())
                        {
?>
                        <tr>
                            <td><?php echo $read_RelTypes['id']?></td>
                            <td>
                                <a href="insercao-de-relacoes?estado=introducao&ent=<?php echo $_REQUEST['ent']; ?>&rel_type=<?php echo $read_RelTypes['id'];?>">[<?php echo $read_RelTypes['name'];?>]</a>
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
                        <table class="table">
                            <thead>
                            <th>Entidades que podem ser associadas</th>
                            <th>Nome da relação</th>
                            <th>Ação</th>
                            </thead>
                            <tbody>
<?php
                                $control = 0;
                                while($read_SecondEnt = $res_SencondEnt->fetch_assoc())
                                {
                                    $verificaRel = "SELECT * FROM relation WHERE (entity1_id = ".$prev_SelEnt." AND entity2_id = ".$read_SecondEnt['id'].") OR (entity2_id = ".$prev_SelEnt." AND entity1_id = ".$read_SecondEnt['id'].")";
                                    if ($this->bd->runQuery($verificaRel)->num_rows === 0) {
                                        if($read_SecondEnt['entity_name'] != '')
                                        {
    ?>
                                        <tr>
                                            <td><?php echo $read_SecondEnt['entity_name']; ?></td>
                                            <td> <!--<label>Nome para a relação </label>--><input type="text" name ="nomeDaRel<?php echo $control; ?>"></td>
                                            <td><input type="checkbox" name="secondEnt<?php echo $control; ?>" value="<?php echo $read_SecondEnt['id'];?>"></td>
                                        </tr>
    <?php
                                        }
                                        else
                                        { 
                                            //if the user didn't fave any name to the entity e need to search for the attribute of that entity who has a name.
    ?>
                                        <tr>
                                            <td><?php echo  $read_SecondEnt['id']; ?></td>
                                            <td> <!--<label>Nome para a relação </label>--><input type="text" name ="nomeDaRel<?php echo $control; ?>"></td>
                                            <td><input type="checkbox" name="secondEnt<?php echo $control; ?>" value="<?php echo $read_SecondEnt['id'];?>"></td>

                                        </tr>
    <?php
                                        }
                                        $control++;
                                    }
                                }
                                $_SESSION['numEnt2Max'] = $control; 
                                if($control == 0)
                                {
?>
                                <td colspan="3">Não existem entidades que possam ser associadas a entidade selecionada.</td>
<?php
                                }
?> 
                            </tbody>
                        </table>
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
                $res_SencondEnt =  $this->bd->runQuery("SELECT DISTINCT entity.id, entity.entity_name FROM rel_type, entity WHERE rel_type.ent_type1_id = entity.ent_type_id  AND rel_type.ent_type1_id=".$read_CompRel['ent_type1_id']);
?>
                <html>
                    <form>
                        <table class="table">
                            <thead>
                            <th>Entidades que podem ser associadas</th>
                            <th>Nome da relação</th>
                            <th>Ação</th>
                            </thead>
                            <tbody>
<?php
                            $control = 0;
                            while($read_SecondEnt = $res_SencondEnt->fetch_assoc())
                            {
                                $verificaRel = "SELECT * FROM relation WHERE (entity1_id = ".$prev_SelEnt." AND entity2_id = ".$read_SecondEnt['id'].") OR (entity2_id = ".$prev_SelEnt." AND entity1_id = ".$read_SecondEnt['id'].")";
                                if ($this->bd->runQuery($verificaRel)->num_rows === 0) {
                                    if($read_SecondEnt['entity_name'] != '')
                                    {
            ?>
                                    <tr>
                                        <td><?php echo $read_SecondEnt['entity_name']; ?></td>
                                        <td><input type="text" name ="nomeDaRel<?php echo $control; ?>"></td>
                                        <td><input type="checkbox" name="secondEnt<?php echo $control; ?>" value="<?php echo $read_SecondEnt['id'];?>"></td>
                                    </tr>
            <?php   
                                    }
                                    else
                                    {               //if the user didn't fave any name to the entity e need to search for the attribute of that entity who has a name.
    ?>
                                        <tr>
                                            <td><input type="checkbox" name="secondEnt<?php echo $control; ?>" value="<?php echo $read_SecondEnt['id'];?>"><?php echo  $read_SecondEnt['id']; ?></td>
                                            <td> <!--<label>Nome para a relação </label>--><input type="text" name ="nomeDaRel<?php echo $control; ?>"></td>
                                            <td><input type="checkbox" name="secondEnt<?php echo $control; ?>" value="<?php echo $read_SecondEnt['id'];?>"></td>
                                        </tr>
    <?php                                    

                                    }
                                    $control++;
                                }
                            }
                            $_SESSION['numEnt2Max'] = $control; 
                            if($control == 0)
                            {
?>
                                <td colspan="3">Não existem entidades que possam ser associadas a entidade selecionada.</td>
<?php
                       
                            }
        ?>
                                    </tbody>
                        </table>

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
         * When we change a state od 0one f the value backups the relation and saves the changes in the database
         */
        private function activateVal() {
            $idVal = $this->bd->userInputVal($_REQUEST['val']);
            $idRel = $this->bd->userInputVal($_REQUEST['rel']);
            if( $this->gereInsRel->addHist($idRel,$this->bd)) {
                if ($this->gereInsRel->addValHist($idVal,$this->bd)) {
                    if($this->bd->runQuery("UPDATE value SET updated_on = '".date("Y-m-d H:i:s",time())."', state = 'active' WHERE id=".$idVal)) {
?>
                        <html>
                           <p>O valor da propriedade foi ativado.</p>
                           <p>Clique em <a href="/insercao-de-relacoes"/>Continuar</a> para avançar</p>
                        </html>
<?php
                        $this->bd->getMysqli()->commit();
                    }
                    else {
?>
                    <html>
                        <p>A ativação do valor da propriedade falhou.</p>
                        <p>Clique em <?php goBack();?>.</p>
                    </html>
<?php
                     $this->bd->getMysqli()->rollback();
                    }
                }
                else {
?>
                    <html>
                        <p>A ativação do valor da propriedade falhou.</p>
                        <p>Clique em <?php goBack();?>.</p>
                    </html>
<?php
                    $this->bd->getMysqli()->rollback();  
                }
            }
            else {
?>
                <html>
                    <p>A ativação do valor da propriedade falhou.</p>
                    <p>Clique em <?php goBack();?>.</p>
                </html>
<?php   
                $this->bd->getMysqli()->rollback();
            }
        }
	/**
         * When we change a state od 0one f the value backups the relation and saves the changes in the database
         */
        private function desactivateVal () {
            $idVal = $this->bd->userInputVal($_REQUEST['val']);
            $idRel = $this->bd->userInputVal($_REQUEST['rel']);
            if( $this->gereInsRel->addHist($idRel,$this->bd)) {
                if ($this->gereInsRel->addValHist($idVal,$this->bd)) {
                    if($this->bd->runQuery("UPDATE value SET updated_on = '".date("Y-m-d H:i:s",time())."', state = 'inactive' WHERE id=".$idVal)) {
                        $this->bd->getMysqli()->commit();
?>
                        $this->bd->getMysqli()->commit();<html>
                           <p>O valor da propriedade foi desativado.</p>
                           <p>Clique em <a href="/insercao-de-relacoes"/>Continuar</a> para avançar</p>
                        </html>
<?php
                        
                    }
                    else {
?>
                    <html>
                        <p>A desativação do valor da propriedade falhou.</p>
                        <p>Clique em <?php goBack();?>.</p>
                    </html>
<?php
                     $this->bd->getMysqli()->rollback();
                    }
                }
                else {
?>
                    <html>
                        <p>A desativação do valor da propriedade falhou.</p>
                        <p>Clique em <?php goBack();?>.</p>
                    </html>
<?php
                    $this->bd->getMysqli()->rollback();  
                }
            }
            else {
?>
                <html>
                    <p>A desativação do valor da propriedade falhou.</p>
                    <p>Clique em <?php goBack();?>.</p>
                </html>
<?php   
                $this->bd->getMysqli()->rollback();
            }
        }
        
	/**
	 * This method will activate the relation the user selected.
	 */
	public function activate(){
            
            $idRel = $this->bd->userInputVal($_REQUEST['rel']);
            $selRel = $this->bd->runQuery("SELECT * FROM relation WHERE id = ".$idRel)->fetch_assoc();
            $selEnt1 = $selRel['entity1_id'];
            $selEnt2 = $selRel['entity2_id'];
            
            $checkActive1 = $this->bd->runQuery("SELECT * FROM entity WHERE state = 'active' AND id = ".$selEnt1)->num_rows;
            $checkActive2 = $this->bd->runQuery("SELECT * FROM entity WHERE state = 'active' AND id = ".$selEnt2)->num_rows;
            
            if ($checkActive1 > 0 && $checkActive2 > 0){
            if( $this->gereInsRel->addHist($idRel,$this->bd))
            {
                if($this->bd->runQuery("UPDATE relation SET updated_on = '".date("Y-m-d H:i:s",time())."', state = 'active' WHERE id=".$idRel))
                {
?>
                   <html>
                        <p>A relação foi ativada.</p>
                        <p>Clique em <a href="/insercao-de-relacoes"/>Continuar</a> para avançar</p>
                   </html>
<?php
                 $this->bd->getMysqli()->commit();
                }
                else
                {
?>
                <html>
                    <p>A ativação da relação falhou.</p>
                    <p>Clique em <?php goBack();?>.</p>
                </html>
<?php
                 $this->bd->getMysqli()->rollback();
                }
            }
            else
            {
?>
                <html>
                    <p>A ativação da relação falhou.</p>
                    <p>Clique em <?php goBack();?>.</p>
                </html>
<?php   
            $this->bd->getMysqli()->rollback();
            }
            }
            else {
?>
                <p>Não pode ativar esta relação uma vez que uma das suas entidades encontra-se desativada</p>
                <p>Clique em <?php goBack();?>.</p>
<?php
            }
        }
        
        /**
	 * This method will desactivate the relation the user selected.
	 */
	public function desactivate(){
             $idRel = $this->bd->userInputVal($_REQUEST['rel']);
            if( $this->gereInsRel->addHist($idRel,$this->bd))
            {
                if($this->bd->runQuery("UPDATE relation SET updated_on = '".date("Y-m-d H:i:s",time())."', state = 'inactive' WHERE id=".$idRel))
                {
                    $getHour = $this->bd->runQuery("SELECT * FROM hist_value ORDER BY inactive_on DESC LIMIT 1")->fetch_assoc();
                    
                    $queryDisVal = "SELECT * FROM value WHERE relation_id=".$idRel."";
                    $runDis = $this->bd->runQuery($queryDisVal);
                    while($disableVal = $runDis->fetch_assoc()){
                        $this->bd->runQuery("UPDATE value SET state='inactive', updated_on='".$getHour['inactive_on']."' WHERE id=".$disableVal['id']);
                    }
?>
                    <html>
                        <p>A relação foi desativada.</p>
                        <p>Clique em <a href="/insercao-de-relacoes"/>Continuar</a> para avançar</p>
                    </html>
<?php
                $this->bd->getMysqli()->commit();
                }
                else
                {
?>
                    <html>
                        <p>A desativação da relação falhou.</p>
                         <p>Clique em <?php goBack();?>.</p>
                    </html>
<?php
                 $this->bd->getMysqli()->rollback();
                }
            }
            else
            {
?>
                    <html>
                        <p>A desativação da relação falhou.</p>
                         <p>Clique em <?php goBack();?>.</p>
                    </html>
<?php             
            $this->bd->getMysqli()->rollback();
            }
        }
	
	/**
	 * This method will handle the insertion that a user will make in the database.
	 */
	public function insertState()
        {
            if($this->ssvalidation())
            {
                if($_REQUEST['flag'] == 'naoeditar')
                {
                    $this->nedita();
                }
                else if ($_REQUEST['flag'] =='atributosNovos')
                {
                    $this->addNewAttr();
                }
                else if($_REQUEST['flag'] =='UpdateAttr')
                {
                    $this->updateAttr();
                }
            }
            else 
            {
                goBack();
            }
        }
        
        /**
         * Updates the current value from the properties to new ones.
         */
        private function updateAttr()
        {
            $erro = false;
            for($i= 0; $i <= $_SESSION['attrDaRelImp']; $i++ )
            {
                $id = $this->bd->userInputVal($_REQUEST['iddarel']);
                if ($i == $_SESSION['propImpressas']) {
                    if(!$this->gereInsRel->addHist($id, $this->bd)) {
                        $erro = true;
                        break;
                    }
                }
                if(isset($_REQUEST['check'.$i]))
                {
                    if(isset($_REQUEST['radio'.$i]))
                    {
                        $newValue = $_REQUEST['radio'.$i];
                    }
                    else if(isset($_REQUEST['select'.$i]))
                    {
                        $newValue = $_REQUEST['select'.$i];
                        //echo $_REQUEST['select'.$i];
                    }
                    else if(isset($_REQUEST['textbox'.$i]))
                    {
                        $newValue =$_REQUEST['textbox'.$i];
                    }
                    if(!$this->bd->runQuery("UPDATE `value` SET `value`='".$newValue."', `updated_on`='".date("Y-m-d H:i:s",time())."' WHERE id=".$_REQUEST['check'.$i]))
                    {
                        $erro = true;
                        break;
                    }
                }
            }
            if ($erro) {
?>
                <html>
                    <p>Ocorreu um erro pelo que a propriedade não foi atualizada</p>
                    <p>Clique em <a href="/insercao-de-relacoes"/>Continuar</a> para avançar</p>
                </html>
<?php
                $this->bd->getMysqli()->rollback();
            }
            else {
?>
                <html>
                    <p>A propriedades foi atualizada</p>
                    <p>Clique em <a href="/insercao-de-relacoes"/>Continuar</a> para avançar</p>
                </html>
<?php
                $this->bd->getMysqli()->commit();
            }
        }
        
        /**
         *This method will insert new atributtes 
         */
        private function addNewAttr()
        {
            $erro = false;
            for($i= 0; $i <= $_SESSION['propImpressas']; $i++ )
            {
                $id = $this->bd->userInputVal($_REQUEST['iddarel']);
                if ($i == $_SESSION['propImpressas']) {
                    if(!$this->gereInsRel->addHist($id, $this->bd)) {
                        $erro = true;
                        break;
                    }
                }
                if(isset($_REQUEST['check'.$i]))
                {
                    if(isset($_REQUEST['radio'.$i]))
                    {
                        $newValue = $_REQUEST['radio'.$i];
                    }
                    else if(isset($_REQUEST['select'.$i]))
                    {
                        $newValue = $_REQUEST['select'.$i];
                        //echo $_REQUEST['select'.$i];
                    }
                    else if(isset($_REQUEST['textbox'.$i]))
                    {
                        $newValue =$_REQUEST['textbox'.$i];
                    }
                    if(!$this->bd->runQuery("INSERT INTO `value`(`id`, `entity_id`, `property_id`, `value`, `producer`, `relation_id`, `state`, `updated_on`) VALUES (NULL,NULL,".$_REQUEST['check'.$i].",'".$newValue."','".wp_get_current_user()->user_login."',".$id.",'active','".date("Y-m-d H:i:s",time())."')"))
                    {
                        $erro = true;
                        break;
                    }
                }
            }
            if ($erro) {
?>
                <html>
                    <p>Erro ao Adicionar as propriedades à relação.</p>
                    <p>Clique em <a href="/insercao-de-relacoes"/>Continuar</a> para avançar</p>
                </html>
<?php  
                $this->bd->getMysqli()->rollback();
            }
            else {
?>
                <html>
                    <p>As propriedades foram adicionadas à relação.</p>
                    <p>Clique em <a href="/insercao-de-relacoes"/>Continuar</a> para avançar</p>
                </html>
<?php
                $this->bd->getMysqli()->commit();
            }
        }
        
        
        /**
         * Mehtod that will insert new values in the database.
         */
        private function nedita(){
            //a preencher
            $erro = false;
            for($i=0; $i <= $_SESSION['numEnt2Max'];$i++){    
                
                if(isset($_REQUEST['nomeDaRel'.$i]))
                {
                     $rel_name= $this->bd->userInputVal($_REQUEST['nomeDaRel'.$i]);
                      
                }
                else
                {
                    $rel_name="";
                }
                if(isset($_REQUEST['secondEnt'.$i])){
                    if($this->bd->runQuery("INSERT INTO `relation`(`id`, `rel_type_id`, `entity1_id`, `entity2_id`, `relation_name`, `state`) VALUES (NULL,".$_REQUEST['rel_type'].",".$_REQUEST['firstEnt'].",".$_REQUEST['secondEnt'.$i].",'".$rel_name."','active')"))
                    {
                        $ent1 = $this->bd->runQuery("SELECT entity_name FROM entity WHERE id = ".$this->bd->userInputVal($_REQUEST['firstEnt']))->fetch_assoc()['entity_name'];
                        $ent2 = $this->bd->runQuery("SELECT entity_name FROM entity WHERE id = ".$this->bd->userInputVal($_REQUEST['secondEnt'.$i]))->fetch_assoc()['entity_name'];
                        if ($ent1 == '') {
                            $ent1 = $_REQUEST['firstEnt'];
                        }
                        elseif ($ent2 == '') {
                            $ent2 =$_REQUEST['secondEnt'.$i];
                        }
                        
?>
                        <html>
                            <p>Associou com sucesso a entidade <?php echo $ent1; ?>, a entidade <?php echo $ent2;?>.</p>
                            <p>Clique em <a href="insercao-de-relacoes?estado=editar&rel=<?php echo $this->bd->getMysqli()->insert_id; ?>"/>Inserir Propriedades</a> para preencher informações relativas a relação que acabou de criar.</p>
                        </html>
 <?php
                    }
                    else
                    {
?>                      
                        <html>
                            <p>A inserção de esta nova relação falhou.</p>
                            <p>Clique em <a href="insercao-de-relacoes"/>Continuar</a> para continuar</p>
                        </html>
 <?php
                    }
                }
        
                
            }
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
	 * Server side validation when JQuery is disabled
	 */
	public function ssvalidation(){
            if($_REQUEST['flag'] == 'UpdateAttr')
            {
                //validation you should select at least one checkbox
               //validation check if for every check box there is at least one value 
                $count = 0;
                for($i=0; $i <= $_SESSION['attrDaRelImp']; $i++)
                    {
                        if(isset($_REQUEST['check'.$i]))
                        {
                            //echo $i;
                            //there is no 
                            //echo $_REQUEST['textbox'.$i];
                            if(empty($_REQUEST['select'.$i]) && empty($_REQUEST['radio'.$i]) && empty($_REQUEST['textbox'.$i]))
                            {
?>
                        <html>
                            <p>Verifique se para todas as checkBoxes selecionadas introduziu valores.</p>
                        </html>
<?php
                                return false;
                            }
                            else
                            {
                                if(isset($_REQUEST['select'.$i]))
                                {}
                                else if(isset($_REQUEST['radio'.$i]))
                                {}
                                else if(isset($_REQUEST['textbox'.$i]))
                                {
                                    $res_getPropId = $this->bd->runQuery("SELECT property_id FROM value WHERE id=".$this->bd->userInputVal($_REQUEST['check'.$i]));
                                    $getPropId = $res_getPropId->fetch_assoc();
                                    
                                    $res_getValue_Type = $this->bd->runQuery("SELECT value_type FROM property WHERE id=".$getPropId['property_id']);
                                    $getValue_Type = $res_getValue_Type->fetch_assoc();
                                   
                                    if($this->typeValidation($getValue_Type['value_type'], $this->bd->userInputVal($_REQUEST['textbox'.$i]))== false)
                                    {
                                        ?>
                        <html>
                            <p>Verifique se o tipo introduzido num dos campos é compativel com o valor aceite na base de dados.</p>
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
                    </html>
<?php
                    return false;
                }
                    return true;
                    
                    
                }
            else if($_REQUEST['flag'] == 'atributosNovos')
                {
                    $count = 0;
                    for($i=0; $i <=  $_SESSION['propImpressas']; $i++)
                    {
                        if(isset($_REQUEST['check'.$i]))
                        {
                           // echo $i;
                            if(empty($_REQUEST['select'.$i]) && empty($_REQUEST['radio'.$i]) && empty($_REQUEST['textbox'.$i]))
                            {
?>
                                <html>
                                    <p>Verifique se para todas as checkBoxes selecionadas introduziu valores.</p>
                                </html>
<?php
                                return false;
                            }
                            else
                            {
                                if(isset($_REQUEST['select'.$i])){}
                                else if(isset($_REQUEST['radio'.$i])){}
                                else if(isset($_REQUEST['textbox'.$i]))
                                {                                    
                                    $res_getValue_Type = $this->bd->runQuery("SELECT value_type FROM property WHERE id=".$this->bd->userInputVal($_REQUEST['check'.$i]));
                                    $getValue_Type = $res_getValue_Type->fetch_assoc();
                                   
                                    if($this->typeValidation($getValue_Type['value_type'], $this->bd->userInputVal($_REQUEST['textbox'.$i]))== false)
                                    {
?>
                                        <html>
                                            <p>Verifique se o tipo introduzido num dos campos é compativel com o valor aceite na base de dados.</p>
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
                            </html>
<?php
                            return false;
                        }
                        return true;
                    }
            else if($_REQUEST['flag'] == 'naoeditar')
            {
                        return true;
            }
        }
        
        /**
         * Allow's the user to create a new relation with the existing entities.
         */
        private function createNewRel(){
?>
            <h3 align="center">Inserção de Relações - Nova Relação</h3>
<?php
            $relType = $this->bd->runQuery("SELECT * FROM rel_type");
            if ($relType->num_rows == 0) {
?>
                <p align="center">Não existem ainda quaisquer tipos de relações, pelo que não pode introduzir qualquer relação.</p>
                <p align="center">Clique em <a href="/gestao-de-relacoes">Criar tipo de relação</a> para criar um novo tipo de relação.</p>
<?php
            }
            else {
?>
            <form align="center">
                <label>Entidade 1</label><br>
                <select id="ent" name="ent">
                        <option></option>
<?php
                    $res_GetEntities = $this->bd->runQuery("SELECT * FROM entity");
                    while($read_GetEnt = $res_GetEntities->fetch_assoc()){
 
                        if($read_GetEnt['entity_name'] == '')
                        {
?>
                            <option value="getAttr?ent=<?php echo $read_GetEnt['id']?>"><?php echo $read_GetEnt['id']?></option>
<?php
                        }
                        else
                        {
?>
                            <option value="getAttr?ent=<?php echo $read_GetEnt['id']?>"><?php echo $read_GetEnt['entity_name']?></option>
<?php                            
                        }
                       
                    }
?>
                </select>
                
                <div id="showAttr" type="hidden">
                    
                </div>
                
                <noscript></noscript>
                
                <input type="hidden" name="estado" value="associar">
                <input type="submit" value="Inserir nova Relação">
            </form>
<?php
        
            //mandar um ent com o id da propriedade selecionada para o associar

            
            }
        }  
}

/**
 * This class has all the methods to manage all the history of the table hist_relation
 */
class RelHist{
    
    public function __construct(){}
    
    /**
     * This method will make a backup from all the change that are made in the relations
     * @param type $id -> id form the selected relation
     * @param type $bd -> is an object that will allow ius to use the database functions
     * @return boolean
     */
    public function addHist($id,$bd){
        $bd->getMysqli()->autocommit(false);
	$bd->getMysqli()->begin_transaction();
        
        $res_oldRel = $bd->runQuery("SELECT * FROM relation WHERE id=".$id);
        if($res_oldRel->num_rows == 1)
        {
            $inactive = date("Y-m-d H:i:s",time());
            $read_oldRel = $res_oldRel->fetch_assoc();
            if($bd->runQuery("INSERT INTO `hist_relation`(`id`, `rel_type_id`, `entity1_id`, `entity2_id`, `relation_name`, `state`, `relation_id`, `active_on`, `inactive_on`) "
                    . "VALUES (NULL,".$read_oldRel['rel_type_id'].",".$read_oldRel['entity1_id'].",".$read_oldRel['entity2_id'].",'".$read_oldRel['relation_name']."','inactive',".$id.",'".$read_oldRel['updated_on']."','".$inactive."')"))
            {
                
                $resSVal = $bd->runQuery("SELECT * FROM value  WHERE relation_id=".$id);
                while($readSVal = $resSVal->fetch_assoc())
                {
                    if($readSVal['entity_id']=='')
                    {
                        if(!$bd->runQuery("INSERT INTO `hist_value`(`id`, `entity_id`, `property_id`, `value`, `producer`, `relation_id`, `value_id`, `active_on`, `inactive_on`, `state`) VALUES (NULL,NULL,".$readSVal['property_id'].",'".$readSVal['value']."','".$readSVal['producer']."',".$id.",".$readSVal['id'].",'".$readSVal['updated_on']."','".$inactive."','inactive')"))
                        {
                            return false;
                        }
                    }
                    else
                    {
                        if(!$bd->runQuery("INSERT INTO `hist_value`(`id`, `entity_id`, `property_id`, `value`, `producer`, `relation_id`, `value_id`, `active_on`, `inactive_on`, `state`) VALUES (NULL,".$readSVal['entity_id'].",".$readSVal['property_id'].",'".$readSVal['value']."','".$readSVal['producer']."',".$id.",".$readSVal['id'].",'".$readSVal['updated_on']."','".$inactive."','inactive')"))
                        {
                            return false;
                        }
                    }
                }
                if($bd->runQuery("UPDATE relation SET updated_on = '".$inactive."' WHERE id = ".$id))
                {
                    return true;
                }
                else {
                    return false;
                }
            }
           
        }
        return false;    
    }
    
    /**
     * This method is responsible for the execution flow when the state is Histórico.
     * He starts by presenting a datepicker with options to do a kind of filter of 
     * all the history of the selected relation.
     * After that he presents a table with all the versions presented in the history
     * @param type $bd (object form the class Db_Op)
     */
    public function showHist ($bd) {  
        if (empty($_REQUEST["selData"]) || (!empty($_REQUEST["selData"]) && $bd->validaDatas($_REQUEST['data']))) {
?>
        <form method="GET">
            Verificar histórico:<br>
            <input type="radio" name="controlDia" value="ate">até ao dia<br>
            <input type="radio" name="controlDia" value="aPartir">a partir do dia<br>
            <input type="radio" name="controlDia" value="dia">no dia<br>
            <input type="text"  class="datepicker" id="datepicker" name="data" placeholder="Introduza uma data">
            <input type="hidden" name="selData" value="true">
            <input type="hidden" name="estado" value="historico">
            <input type="hidden" name="rel" value="<?php echo $_REQUEST["rel"]; ?>">
            <input type="submit" value="Apresentar histórico">
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>Data de Início</th>
                    <th>Data de FIm</th>
                    <th>Tipo de Relação</th>
                    <th>Entidade 1</th>
                    <th>Entidade 2</th>
                    <th>Propriedade</th>
                    <th>Valor</th>
                    <th>Estado da Relação</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
<?php
        if (empty($_REQUEST["data"])) {
            $queryHistorico = "SELECT * FROM hist_relation WHERE relation_id = ".$_REQUEST["rel"]." ORDER BY inactive_on DESC";
        }
        else {
            if (isset($_REQUEST["controlDia"]) && $_REQUEST["controlDia"] == "ate") {
                $queryHistorico = "SELECT * FROM hist_relation WHERE relation_id = ".$_REQUEST["rel"]." AND inactive_on <= '".$_REQUEST["data"]."' ORDER BY inactive_on DESC, property_id ASC";
            }
            else if (isset($_REQUEST["controlDia"]) && $_REQUEST["controlDia"] == "aPartir") {
                $queryHistorico = "SELECT * FROM hist_relation WHERE relation_id = ".$_REQUEST["rel"]." AND inactive_on >= '".$_REQUEST["data"]."' ORDER BY inactive_on DESC, property_id ASC";
            }
            else if (isset($_REQUEST["controlDia"]) && $_REQUEST["controlDia"] == "dia"){
                $queryHistorico = "SELECT * FROM hist_relation WHERE relation_id = ".$_REQUEST["rel"]." AND inactive_on < '".date("Y-m-d",(strtotime($_REQUEST["data"]) + 86400))."' AND inactive_on >= '".$_REQUEST["data"]."' ORDER BY inactive_on DESC, property_id ASC LIMIT 1";
            }
            else {
                $queryHistorico = "SELECT * FROM hist_relation WHERE relation_id = ".$_REQUEST["rel"]." AND inactive_on < '".date("Y-m-d",(strtotime($_REQUEST["data"]) + 86400))."' AND inactive_on >= '".$_REQUEST["data"]."' ORDER BY inactive_on DESC, property_id ASC";
            }
        }
        $queryHistorico = $bd->runQuery($queryHistorico);
        if ($queryHistorico->num_rows == 0) {
?>
            <tr>
                <td colspan="8">Não existe registo referente à propriedade selecionada no histórico</td>
                <td><?php goBack(); ?></td>
            </tr>
<?php
        }
        else {
            while ($hist = $queryHistorico->fetch_assoc()) {
                $res_RelName = $bd->runQuery("SELECT name FROM rel_type WHERE id=".$hist['rel_type_id']);
                $res_RelName = $res_RelName->fetch_assoc();
                $props = $bd->runQuery("SELECT * FROM property WHERE rel_type_id = ".$hist["rel_type_id"]);
                $numProp = $props->num_rows;
                     
?>
                <tr>
                    <td rowspan="<?php echo $numProp;?>"><?php echo $hist["active_on"];?></td>
                    <td rowspan="<?php echo $numProp;?>"><?php echo $hist["inactive_on"];?></td>
                    <td rowspan="<?php echo $numProp;?>"><?php echo $res_RelName['name'];?></td>
                    <td rowspan="<?php echo $numProp;?>">
<?php
                    $_readEnt1 = $bd->runQuery("SELECT entity_name FROM entity WHERE id=".$hist['entity1_id'])->fetch_assoc();
                    if($_readEnt1['entity_name'] != '')
                    {
                        echo $_readEnt1['entity_name'];
                    }
                    else
                    {
                        echo $hist['entity1_id'];
                    }
?>
                    </td>
                    <td rowspan="<?php echo $numProp;?>">
<?php
                    $_readEnt2 = $bd->runQuery("SELECT entity_name FROM entity WHERE id=".$hist['entity2_id'])->fetch_assoc();
                    if($_readEnt2['entity_name'] != '')
                    {
                        echo $_readEnt2['entity_name'];
                    }
                    else
                    {
                        echo $hist['entity2_id'];
                    }
                    $primeiraVez = true;
?>
                    </td>
<?php
                    while ($prop = $props->fetch_assoc()) {
                        if ($primeiraVez) {
?>                       
                            <td><?php echo $prop["name"];?></td>
                            <td>
<?php
                            $queryValue = $bd->runQuery("SELECT * FROM hist_value WHERE inactive_on = '".$hist["inactive_on"]."' AND property_id = ".$prop["id"]." AND relation_id = ".$_REQUEST["rel"]);
                            $value = $queryValue->fetch_assoc();
                            if (isset($value["value"])) {
                                echo $value["value"];
                            }
                            else {
                                echo "-";
                            }
?>
                            </td>
                            <td rowspan="<?php echo $numProp;?>">
<?php
                            if ($hist["state"] === "active")
                            {
                                echo 'Ativo';
                            }
                            else
                            {
                                echo 'Inativo';
                            }
?>
                            </td>
                            <td rowspan="<?php echo $numProp;?>"><a href ="?estado=voltar&hist=<?php echo $hist["id"];?>&rel=<?php echo $_REQUEST["rel"];?>">Voltar para esta versão</a></td>
                        </tr>
                       
<?php
                            $primeiraVez = false;
                        }
                        else {
?>
                        <tr>
                            <td><?php echo $prop["name"];?></td>
                            <td>
<?php
                            $queryValue = $bd->runQuery("SELECT * FROM hist_value WHERE inactive_on = '".$hist["inactive_on"]."' AND property_id = ".$prop["id"]." AND relation_id = ".$_REQUEST["rel"]);
                            $value = $queryValue->fetch_assoc();
                            if (isset($value["value"])) {
                                echo $value["value"];
                            }
                            else {
                                echo "-";
                            }
?>
                            </td>
                        </tr>
<?php
                        }

                    }
?>
                    
<?php
            }
        }
?>
            <tbody>
        </table>
<?php
        }
    }
    
    /**
     * This method controls the excution flow when the state is Voltar
     * Basicly he does all the necessary queries to reverse a relation to an old version
     * saved in the history
     * @param type $bd (object form the class Db_Op)
     */
    public function estadoVoltar ($bd) {
        $dataUpdate = date("Y-m-d H:i:s",time());
        $this->atualizaHistorico($bd,$dataUpdate);
        $selectAtributos = "SELECT * FROM hist_relation WHERE id = ".$_REQUEST['hist'];
        $selectAtributos = $bd->runQuery($selectAtributos);
        $atributos = $selectAtributos->fetch_assoc();
        $updateHist = "UPDATE relation SET ";
        foreach ($atributos as $atributo => $valor) {
            if ($atributo == 'state') {
                $valor = "active";
            }
            if ($atributo != "id" && $atributo != "inactive_on" && $atributo != "active_on" && $atributo != "relation_id" && !is_null($valor)) {
                $updateHist .= $atributo." = '".$valor."',"; 
            }
        }
        $updateHist .= " updated_on = '".$dataUpdate."' WHERE id = ".$_REQUEST['rel'];
        $updateHist =$bd->runQuery($updateHist);
        if ($updateHist) {
            if ($this->updateValue($bd,$dataUpdate)) {
                echo "#1 ";
                $bd->getMysqli()->commit();
    ?>
                <p>Atualizou a propriedade com sucesso para uma versão anterior.</p>
                <p>Clique em <a href="/insercao-de-relacoes/">Continuar</a> para avançar.</p>
<?php
        
            }
            else {
                echo "#2 ";
?>
                <p>Não foi possível reverter a propriedade para a versão selecionada</p>
<?php
                $bd->getMysqli()->rollback();
                goBack();
            }
        }
        else {
            echo "#3 ";
?>
            <p>Não foi possível reverter a propriedade para a versão selecionada</p>
<?php
            $bd->getMysqli()->rollback();
            goBack();
        }
    }
    
    private function updateValue ($bd,$dataUpdate) {
        $queryRelHis = $bd->runQuery("SELECT * FROM hist_relation WHERE id = ".$_REQUEST["hist"]);
        $relHist = $queryRelHis->fetch_assoc();
        $querySelRel = "SELECT rel_type_id FROM relation WHERE id = ".$_REQUEST["rel"];
        $relType = $bd->runQuery($querySelRel)->fetch_assoc()["rel_type_id"];
        $queryPropRel = "SELECT * FROM property WHERE rel_type_id = ".$relType;
        echo $queryPropRel."<br>";
        $queryPropRel = $bd->runQuery($queryPropRel);
        while ($prop = $queryPropRel->fetch_assoc()) {
            print_r($prop);
            $queryHistValue ="SELECT * FROM hist_value WHERE inactive_on = '".$relHist["inactive_on"]."' AND property_id = ".$prop["id"]." AND relation_id = ".$_REQUEST["rel"];
            echo $queryHistValue."<br>";
            $queryHistValue = $bd->runQuery($queryHistValue);
            $queryValue = "SELECT * FROM value WHERE property_id = ".$prop["id"]." AND relation_id = ".$_REQUEST["rel"];
            echo $queryValue."<br>";
            $queryValue = $bd->runQuery($queryValue);
            while ($values = $queryValue->fetch_assoc()) {
                print_r($values);
                $insertHist = "INSERT INTO `hist_value`"
                        . "(`property_id`, `value`, `producer`, `relation_id`, `value_id`, `active_on`, `inactive_on`, `state`) "
                        . "VALUES "
                        . "(".$values["property_id"].",'".$values["value"]."','".$values["producer"]."',".$_REQUEST["rel"].",".$values["id"].",'".$values["updated_on"]."','".$dataUpdate."','inactive')";
                echo $insertHist."<br>";
                $insertHist = $bd->runQuery($insertHist);
                if (!$insertHist) {
                    echo "#4 ";
                    return false;
                }
            }
            while ($histValues = $queryHistValue->fetch_assoc()){
                $updateValue = "UPDATE `value` SET "
                        . "`property_id`= ".$histValues["property_id"].","
                        . "`value`= '".$histValues["value"]."',"
                        . "`producer`= '".$histValues["producer"]."',"
                        . "`relation_id`= ".$histValues["relation_id"].","
                        . "`updated_on`= '".$dataUpdate."',"
                        . "`state`= '".$histValues["state"]."'"
                        . "WHERE id = ".$histValues["value_id"];
                echo $updateValue."<br>";
                $updateValue = $bd->runQuery($updateValue);
                if (!$updateValue) {
                    echo "#6 ";
                    return false;
                }
            }  
        }
        $selValOutdated = "SELECT * FROM value WHERE updated_on < '".$dataUpdate."' AND relation_id = ".$_REQUEST["rel"];
        $selValOutdated = $bd->runQuery($selValOutdated);
        while ($valOutadet = $selValOutdated->fetch_assoc()) {
            $updateValue = "UPDATE `value` SET "
                            . "`updated_on`= '".$dataUpdate."',"
                            . "`state`= 'inactive'"
                            . "WHERE id = ".$valOutadet["id"];
                    echo $updateValue."<br>";
                    $updateValue = $bd->runQuery($updateValue);
                    if (!$updateValue) {
                        echo "#8 ";
                        return false;
                    }
        }
        echo "#5 ";
        return true;
    }
    
     /**
     * This method is responsible for insert into the history a copy of the relation
     * before being updated
     * @param type $bd (object form the class Db_Op)
     */
    public function atualizaHistorico ($bd, $dataUpdate) {
        $bd->getMysqli()->autocommit(false);
        $bd->getMysqli()->begin_transaction();
        $selectAtributos = "SELECT * FROM relation WHERE id = ".$_REQUEST['rel'];
        $selectAtributos = $bd->runQuery($selectAtributos);
        $atributos = $selectAtributos->fetch_assoc();
        $attr = $val = "";
        foreach ($atributos as $atributo => $valor) {
            if ($atributo == 'state') {
                $valor = "inactive";
            }
            if ($atributo == "updated_on") {
                $atributo = "active_on";
            }
            if ($atributo != "id" && !is_null($valor)) {
                $attr .= "`".$atributo."`,";
                $val .= "'".$valor."',"; 
            }
        }
        $updateHist = "INSERT INTO `hist_relation`(".$attr." inactive_on, relation_id) "
                . "VALUES (".$val."'".$dataUpdate."',".$_REQUEST["rel"].")";
        $updateHist =$bd->runQuery($updateHist);
        if ($updateHist) {
            return true;
        }
        else {
            $bd->getMysqli()->rollback();
            return false;
        }
    }
    
    /**
     * this method adds all the values to the history of the relation when a change is made
     * @param type $idVal
     * @param type $bd
     * @return boolean
     */
    public function addValHist($idVal,$bd) {
        $selVal = $bd->runQuery("SELECT * FROM  value WHERE id = ".$idVal);
        while ($val = $selVal->fetch_assoc()) {
            $insertHist = "INSERT INTO `hist_value`"
                    . "(`property_id`, `value`, `producer`, `relation_id`, `value_id`, `active_on`, `inactive_on`, `state`)"
                    . " VALUES "
                    . "(".$val["property_id"].",'".$val["value"]."','".$val["producer"]."',".$val["relation_id"].",".$idVal.",".date("Y-m-d H:i:s",time()).",'inactive')";
            if (!$insertHist) {
                return false;
            }
        }
        return true;
    }
    


   
    
   public function tableState ($data,$bd) {      
?>
        <table class="table">
            <thead>
                <tr>
                    <th>Tipo de Relação</th>
                    <th>Entidade 1</th>
                    <th>Entidade 2</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
<?php
        $creatTempTable = "CREATE TEMPORARY TABLE temp_table (
        `id` int(11) NOT NULL,
        `rel_type_id` int(11) NOT NULL,
        `entity1_id` int(11) NOT NULL,
        `entity2_id` int(11) NOT NULL,
        `relation_name` varchar(255) DEFAULT NULL,
        `state` enum('active','inactive') NOT NULL)";
        $creatTempTable = $bd->runQuery($creatTempTable);
        
        $selecionaRel = "SELECT * FROM relation WHERE updated_on < '".$data."' OR updated_on LIKE '".$data."%'";
        //echo $selecionaRel . "<br>";
        $runRel = $bd->runQuery($selecionaRel);
        while($readRel = $runRel->fetch_assoc())
        {
            $bd->runQuery("INSERT INTO temp_table VALUES (".$readRel['id'].",'".$readRel['rel_type_id']."','".$readRel['entity1_id']."','".$readRel['entity2_id']."','".$readRel['relation_name']."','".$readRel['state']."')");
        }
        
        $selecionaHist = "SELECT * FROM hist_relation WHERE ('".$data."' > active_on AND '".$data."' < inactive_on) OR ((active_on LIKE '".$data."%' AND inactive_on < '".$data."') OR inactive_on LIKE '".$data."%') GROUP BY rel_type_id ORDER BY inactive_on DESC";
        //echo $selecionaHist . "<br>";
        $querHist = $bd->runQuery($selecionaHist);
        while($readRel = $querHist->fetch_assoc())
        {
            $bd->runQuery("INSERT INTO temp_table VALUES (".$readRel['relation_id'].",'".$readRel['rel_type_id']."','".$readRel['entity1_id']."','".$readRel['entity2_id']."','".$readRel['relation_name']."','".$readRel['state']."')");
        }
        $queryHistorico = $bd->runQuery("SELECT * FROM temp_table GROUP BY id ORDER BY id ASC");
        if ($queryHistorico->num_rows == 0) {
?>
            <tr>
                <td colspan="8">Não existe registo referente à propriedade selecionada no histórico</td>
            </tr>
<?php
        }
        else {
            while ($hist = $queryHistorico->fetch_assoc()) {
                $read_RelName = $bd->runQuery("SELECT name FROM rel_type WHERE id=".$hist['rel_type_id']);
                $read_RelName = $read_RelName->fetch_assoc();
?>
                <tr>
                    <td><?php echo $read_RelName['name'];?></td>
                    <td>
<?php
                    $_readEnt1 = $bd->runQuery("SELECT entity_name FROM entity WHERE id=".$hist['entity1_id'])->fetch_assoc();
                    if($_readEnt1['entity_name'] != '')
                    {
                        echo $_readEnt1['entity_name'];
                    }
                    else
                    {
                        echo $hist['entity1_id'];
                    }
?>
                    </td>
                    <td>
<?php
                    $_readEnt2 = $bd->runQuery("SELECT entity_name FROM entity WHERE id=".$hist['entity2_id'])->fetch_assoc();
                    if($_readEnt2['entity_name'] != '')
                    {
                        echo $_readEnt2['entity_name'];
                    }
                    else
                    {
                        echo $hist['entity2_id'];
                    }

?>
                    </td>
<?php
?>                       

                            <td>
<?php
                            if ($hist["state"] === "active")
                            {
                                echo 'Ativo';
                            }
                            else
                            {
                                echo 'Inativo';
                            }
?>
                            </td>
                         </tr>
                       
<?php
                    }
        
?>
            <tbody>
        </table>
<?php
        $bd->runQuery("DROP TEMPORARY TABLE temp_table");
    }
}
}
