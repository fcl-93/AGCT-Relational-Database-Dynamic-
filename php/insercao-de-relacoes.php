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
           $res_InsProps = $this->bd->runQuery("SELECT * FROM value WHERE relation_id=".$_REQUEST['rel']);
           if($res_InsProps->num_rows == 0)
           {
?>                     
               <html>
                   <p>Não existem propriedades para a relação selecionada.</p>
                   <p>Clique em <?php goBack(); ?> para voltar atrás</p>
               </html>
<?php       

           }
           else
           {
?>
                        <html>
                            <form>
                            <h3>Inserção de Relações - Edição de Propriedades da Relação selecionada.?></h3>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Id</th>
                                            <th>Data</th>
                                            <th>Hora</th>
                                            <th>Nome</th>
                                            <th>Valor</th>
                                            <th>Escolha</th>
                                            <th>Editar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
    <?php   
                                        $checkBoxNumber = 0;
                                        while($read_InsProps = $res_InsProps->fetch_assoc())
                                        {
                                            $res_PropName = $this->bd->runQuery("SELECT name FROM property WHERE id=".$read_InsProps['property_id']);
                                            $read_PropName = $res_PropName->fetch_assoc();
    ?>
                                        <tr>
                                            <td><?php echo $read_InsProps['id']; ?></td>
                                            <td><?php echo $read_InsProps['date'];?></td> 
                                            <td><?php echo $read_InsProps['time'];?></td>
                                            
                                            <td><?php echo $read_PropName['name'];?></td>
                                            <td><input type="text" name="valor<?php echo $checkBoxNumber; ?>" value="<?php echo $read_PropName['value']; ?>"></td>
                                            <td><input type="checkbox" name="valSel<?php echo $checkBoxNumber;?>" value="<?php echo $read_InsProps['id'];?>"></td>			
                                            
                                        </tr>
    <?php
                                        }
                                        $_SESSION['valueNumber'] = $checkBoxNumber;
                                        
    ?>  
                                        
                                       <input type="hidden" name="flag" value="editar"/>
                                       <input type="hidden" name="estado" value="inserir"/>
                                       <input type="submit" value="Atualizar Valores"/>
                                    </tbody>
                                </table>
                            <form>
                        </html>
<?php

            
        }
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
               $res_SencondEnt =  $this->bd->runQuery("SELECT entity.id, entity.entity_name FROM rel_type, entity WHERE rel_type.ent_type2_id = entity.ent_type_id AND rel_type.ent_type2_id=".$read_CompRel['ent_type1_id']);
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
                $res_SencondEnt =  $this->bd->runQuery("SELECT entity.id, entity.entity_name FROM rel_type, entity WHERE rel_type.ent_type1_id = entity.ent_type_id");
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
            }
            else 
            {
                goBack();
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
