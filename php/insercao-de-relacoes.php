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
				}
                                else if($_REQUEST['estado'] == 'introducao')
                                {
                                }
				else if($_REQUEST['estado'] == 'inserir')
				{
				}
				else if($_REQUEST['estado'] == 'desativar')
				{
				}
                                else if($_REQUEST['estado'] == 'ativar')
                                {
                                    
                                }
                                else if ($_REQUEST['estado'] == 'updateAttrRel')
                                {
                                    $this->updateValues();
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
?>                      
                                         <tr>
                                             <td><?php echo $readRelations['id'];?></td>
                                             <td><?php echo $readRelations['ent_type1_id'];?> - <?php echo $readRelations['ent_type2_id'] ?></td>
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
                                                        <a href="insercao-de-relacoes?estado=desativar&rel=<?php echo $readRelations['id'];?>">[Ativar]</a>
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
	 * This will make you edit and add new values for the selected relation
	 */
        public function editRlationProps(){
           $res_InsProps = $this->bd->runQuery("SELECT * FROM value WHERE relation_id=".$_REQUEST['rel']);
           $res_Tipos = $this->bd->runQuery("SELECT ent_type1_id,ent_type2_id FROM rel_type WHERE id=".$_REQUEST['rel']);
           $read_Types = $res_Tipos->fetch_assoc();
                  
?>
                        <html>
                            <form>
                            <h3>Inserção de Relações - Edição de Propriedades da Relação entre <?php echo$read_Types['ent_type1_id'] ;?> - <?php  echo$read_Types['ent_type2_id'] ;?></h3>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Id</th>
                                            <th>Data</th>
                                            <th>Hora</th>
                                            <th>Nome</th>
                                            <th>Valor</th>
                                            <th>Escolha</th>
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
                                       
                                       <input type="hidden" name="estado" value="updateAttrRel"/>
                                       <input type="submit" value="Atualizar Valores"/>
                                    </tbody>
                                </table>
                            <form>
                        </html>
<?php

            
        }
        
        /**
         * This method will update the new submited values for the relation attributes
         */
        public function updateValues()
        {
            if($this->ssvalidation())
            {
                
            }
            else 
            {
                goBack();
            }
        }
        
	/**
	 * Server side validation when JQuery is disabled
	 */
	public function ssvalidation(){
                $control = true;
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
                return $control;
            /*$controlaCheck = 0;
                    for($i = 1; $i <= $_SESSION['propSelected']; $i++)
                    {
                            if(empty($_REQUEST["idProp".$i]))
                            {
                                    $controlaCheck++;
                            }
                    }
                    
            if($controlaCheck == $_SESSION['propSelected'])
            {//erro}
                
            
             for($i = 1; $i <= $_SESSION['propSelected']; $i++)
                    {
                            if((!is_numeric($_REQUEST["ordem".$i]) || $_REQUEST["ordem".$i] < 1) && isset($_REQUEST["idProp".$i]))
                            {
                            
                            }
                    }*/
            return true;
        }
	/**
         * Ths will fill all the field in the form to edit the selected dynamic form.
         */
	public function formEdit(){ 
            
            
        }
        
                                                
	
	/**
	 * This method will activate the custom form the user selected.
	 */
	public function activate(){}
	
	/**
	 * This method will handle the insertion that a user will make in the database.
	 */
	public function insertState(){}
        
        /**
         * This method will update the dataform a selected form
         */
        public function updateForm(){}
}
?>
