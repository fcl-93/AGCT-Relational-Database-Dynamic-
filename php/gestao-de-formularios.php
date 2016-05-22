<?php
require_once("custom/php/common.php");

$gerencia = new GereForms();

/**
 The methods that are present in this class will handle all the operations that we can do in 
 * the page gestão de formularios
 *  @author fabio
 * 
 */
class GereForms
{
	private $bd;
	private $numProp; //printed properties in the table
        private $gereFormHist;
	/**
	 * Constructor
	 */
	public function __construct(){
		$this->bd = new Db_Op();
		$this->gereFormHist = new HistDeForms();
                $this->numProp = 0;
		$this->checkUser();
	}
	
	/**
	 *  This method will check if the user as the permission to acess this page
	 * and will handle all the Requests states
	 */
	public function checkUser(){
		$capability = 'manage_custom_forms';
	
		if ( is_user_logged_in() )
		{
			if(current_user_can($capability))
			{
				if(empty($_REQUEST["estado"]))
				{
					$this->tablePrint();
				}
				else if($_REQUEST['estado'] == 'inserir')
				{
					$this->insertState();
				}
				else if($_REQUEST['estado'] == 'editar_form')
				{
					$this->formEdit();
				}
                                                                else if($_REQUEST['estado'] == 'updateForm')
                                                                {
                                                                                $this->updateForm();
                                                                }
				else if($_REQUEST['estado'] == 'ativar')
				{
					$this->activate();
				}
				else if($_REQUEST['estado'] == 'desativar')
				{
					$this->desactivate();
				}
                                else if($_REQUEST['estado'] == 'historico')
                                {
                                    if(isset($_REQUEST['histAll'])){
                                        $this->gereFormHist->tableHistPrint($this->bd->userInputVal($_REQUEST['data']), $this->bd);
                                    }
                                    else{
                                        $this->gereFormHist->tableHist($this->bd->userInputVal($_REQUEST['form_id']), $this->bd);    
                                    }
                                    
                                }
                                else if ($_REQUEST['estado'] == 'versionBack')
                                {
                                    $this->gereFormHist->changeVersion($this->bd->userInputVal($_REQUEST['histId']), $this->bd);  
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
	 * This method will print the table that will be showing the forms and their state 
	 * in this table the user will be able to desactivate a edit forms.
	 */
	public function tablePrint(){
?>
        <form method="GET">
            Verificar propriedades existentes no dia : 
            <input type="text" class="datepicker" id="datepicker" name="data" placeholder="Introduza uma data"> 
            <input type="hidden" name="estado" value="historico">
            <input type="hidden" name="histAll" value="true">
            <input type="submit" value="Apresentar propriedades">
        </form>
<?php
		$resForm = $this->bd->runQuery("SELECT * FROM custom_form ORDER BY name ASC");
		if($resForm->num_rows == 0)
		{
?>	
			<html>
				<p>Não existem formulários costumizados</p>
			</html>
<?php 
                        $this->intForm();
		}
		else
		{
?>

			<html>
				<table class="table">
					<thead>
						<tr>
							<th>Id</th>
							<th>Nome do formulário customizado</th>
							<th>Estado</th>
							<th>Ação</th>
						</tr>
					</thead>
					<tbody>
<?php 
						while($readForm = $resForm->fetch_assoc())
						{
?>
							<tr>
								<td><?php echo $readForm['id']; ?></td>
								<td><?php echo $readForm['name']; ?></td>
								<td>
<?php
									if($readForm['state'] === 'active')
									{
?>
										Ativo
<?php 
									}
									else
									{
?>
										Inativo
<?php 								}
?>
								</td>
								<td>
									<a href="gestao-de-formularios?estado=editar_form&form_id=<?php echo $readForm['id']; ?>">[Editar]</a>
                                                                       
<?php 
                                                                                       
										if($readForm['state'] === 'active')
										{
?>
											<a href="gestao-de-formularios?estado=desativar&form_id=<?php echo $readForm['id'];?>">[Desativar]</a>
<?php 
										}
										else 
										{
?>
											<a href="gestao-de-formularios?estado=ativar&form_id=<?php echo $readForm['id'];?>">[Ativar]</a>
<?php 
										}
?> 
                                                                                        <a href="gestao-de-formularios?estado=historico&form_id=<?php echo $readForm['id'];?>" >[Histórico]</a>
								</td>
							</tr>
<?php 
						}
						
?>
					</tbody>
				</table>
			</html>
<?php 
			$this->intForm();
		}
	}
	/**
	 * Prints the form composed by a table to create customized forms.
	 */
	public function intForm(){
?>
		<h3>Gestão de formulários customizados - Introdução</h3>
		<br>
<?php 
		//Get all ent_types that have at least one ent_type_id this will unecessary entities from the table form
		$resEnt = $this->bd->runQuery("SELECT DISTINCT ent_type.id, ent_type.name FROM ent_type , property WHERE property.ent_type_id=ent_type.id");

		if($resEnt->num_rows == 0)
		{
?>	
			<html>
				<p>Não pode criar formulários uma vez que ainda não foram inseridas entidades.</p>
			</html>
<?php 
		}
		else 
		{
?>
		<html>
			<form method="POST">
				<input type="hidden" name="estado" value="inserir">
				<label>Nome do formulário customizado:</label> <input type="text" name="nome">
				<label id="nome" class="error" for="nome"></label>
				<br><br>

				<table class="table">
					<thead>
						<tr>
                                                    <th>Entidade</th>
                                                    <th>Id</th>
                                                    <th>Propriedade</th>
                                                    <th>Tipo de valor</th>
                                                    <th>Nome do campo no formulário</th>
                                                    <th>Tipo do campo no formulário</th>
                                                    <th>Tipo de unidade</th>
                                                    <th>Ordem do campo no formulário</th>
                                                    <th>Tamanho do campo no formulário</th>
                                                    <th>Obrigatório</th>
                                                    <th>Estado</th>
                                                    <th>Escolher</th>
                                                    <th>Ordem</th>
                                                    <th>Obrigatório no forumulário customizado</th>
						</tr>
					</thead>
					<tbody>
<?php 
					while($readEnt = $resEnt->fetch_assoc())
					{
						$res_GetProps = $this->bd->runQuery("SELECT p.*FROM property AS p, ent_type AS e WHERE p.ent_type_id = e.id AND e.name LIKE '".$readEnt['name']."' ORDER BY p.name ASC");
?>						
						<tr>
							<td rowspan="<?php echo $res_GetProps->num_rows ?>" style="vertical-align: top;">'<?php echo $readEnt["name"]?>'</td>		
<?php 							
							
							while($readGetProps = $res_GetProps->fetch_assoc())
							{
								$this->numProp++;
?>								
								<td><?php echo $readGetProps['id'];  ?></td>
								<td><?php echo $readGetProps['name'];?></td>
								<td><?php echo $readGetProps['value_type'];?></td>
								<td><?php echo $readGetProps['form_field_name']?></td>
								<td><?php echo $readGetProps['form_field_type']?></td>
								<td>
<?php 
									if(is_null($readGetProps["unit_type_id"]))
									{
?>
										-
<?php 
									}
									else
									{
										$res_UnitName = $this->bd->runQuery("SELECT name FROM prop_unit_type WHERE id = '".$readGetProps['unit_type_id']."'");
										while ($read_UnitName = $res_UnitName->fetch_assoc())
										{
											echo $read_UnitName['name'];
										}
									}
?>
								</td>
								<td><?php echo $readGetProps['form_field_order'];  ?></td>
								<td><?php echo $readGetProps['form_field_size'];  ?></td>
								<td>
<?php 	
								if($readGetProps['mandatory'] == 1)
								{
									echo 'Sim';
								}
								else 
								{
									echo 'Não';
								}
?>
								</td>
								<td>
<?php
								if($readGetProps['state'] == 'active' )
								{
?>
									Ativo									
<?php 
								}
								else
								{
?>
									Inativo
<?php 									
								}
?>
								
								
								
								</td>
								<td><input type="checkbox" name="idProp<?php echo $this->numProp;?>" value="<?php echo $readGetProps['id'];?>"></td>
								
								<td><input type="text" name="ordem<?php echo $this->numProp; ?>"></td>
                                                                <td><input type="radio" name="obrigatorio<?php echo $this->numProp;?>" value="true">Sim
                                                                    <input type="radio" name="obrigatorio<?php echo $this->numProp;?>" value="false">Não
                                                                </td>
							</tr>
<?php 				
							}
?>						
						
<?php 						
					}
?>
					</tbody>
				</table>
				
				<input type="submit" value="Inserir formulário">
			</form>
		</html>
<?php 	
		}
		$_SESSION['propSelected'] = $this->numProp;
	}
	/**
	 * Server side validation when JQuery is disabled
	 */
	public function ssvalidation(){
		if($_REQUEST['estado'] == 'inserir' || $_REQUEST['estado'] == 'updateForm')
		{
                    
                    if(empty($_REQUEST['nome']))
                    {
?>
			<html>	
				<p>Deve introduzir o nome para um novo formulário costumizado.</p>
			</html>
<?php	
                            return false;
                    }
                    //number of check boxes
                    $controlaCheck = 0;
                    for($i = 1; $i <= $_SESSION['propSelected']; $i++)
                    {
                            if(empty($_REQUEST["idProp".$i]))
                            {
                                    $controlaCheck++;
                            }
                    }
                    if($controlaCheck == $_SESSION['propSelected'])
                    {
?>   
                        <html>
                            <p> Deve selecionar pelo menos uma propriedade para o seu formulário <p>
                        </html>
<?php
                        return false;
                    }
                    //
                    $numOrdem = array();
                    for($i = 1; $i <= $_SESSION['propSelected']; $i++)
                    {
                            if((!is_numeric($_REQUEST["ordem".$i]) || $_REQUEST["ordem".$i] < 1) && isset($_REQUEST["idProp".$i]))
                            {
?> 
                                <html>
                                    <p>O campo ordem deve ser numérico e deve introduzir um valor superior a zero.</p><br>
                                </html>
<?php    
                                return false;
                            }
                            if (isset($_REQUEST["idProp".$i]) && empty($_REQUEST["obrigatorio".$i])) {
                                echo $i;
?> 
                                <p>Deve escolher uma opção para o campo Obrigatório no forumlário costumizado.</p><br>
<?php 
                                return false;
                            }
                            if($_REQUEST['ordem'.$i] != ""){
                                array_push($numOrdem, $_REQUEST['ordem'.$i]);
                            }
                    }
                    //Check if there is any duplicated value in order (campo ordem) field
                    $duplicatedValueCheck = array_count_values($numOrdem);
                    print_r($duplicatedValueCheck);
                    foreach ($duplicatedValueCheck as $key => $value) {
                        if($value > 1)
                        {
                            ?>
                                <p>Os campos ordem não devem ter valores iguais.</p><br>
<?php
                        return false;
                        }
                    }
                    return true;
                }	
                else 
                {
                    return true;
                }
	}
	/**
         * Ths will fill all the field in the form to edit the selected dynamic form.
         */
	public function formEdit(){
		$this->numProp = 0;
                $res_Nome = $this->bd->runQuery("SELECT name FROM custom_form WHERE id = ".$_REQUEST['form_id']);
                $read_Name = $res_Nome->fetch_assoc();
 ?>
                <html>
        	<form method="POST">
                    <input type="hidden" name="estado" value="updateForm">
                    <label>Nome do formulário customizado:</label><input type="text" name="nome" value="<?php echo $read_Name['name']; ?>">
                    <br>
                    <table  class="table">
                        <thead>
                            <tr>
                                <th>Entidade</th>
                                <th>Id</th>
                                <th>Propriedade</th>
                                <th>Tipo de valor</th>
                                <th>Nome do campo no formulário</th>
                                <th>Tipo do campo no formulário</th>
                                <th>Tipo de unidade</th>
                                <th>Ordem do campo no formulário</th>
                                <th>Tamanho do campo no formulário</th>
                                <th>Obrigatório</th>
                                <th>Estado</th>
                                <th>Escolher</th>
                                <th>Ordem</th>
                                <th>Obrigatório no forumulário customizado</th>
                            </tr>
                        </thead>
                        <tbody>    
<?php			
                        $res_Ent = $this->bd->runQuery("SELECT DISTINCT ent_type.id, ent_type.name FROM ent_type , property WHERE property.ent_type_id=ent_type.id");
			while($arrayRes = $res_Ent->fetch_assoc())
			{
				$res_getProp = $this->bd->runQuery("SELECT p.* FROM property AS p, ent_type AS e WHERE  p.ent_type_id = e.id AND e.name LIKE '".$arrayRes["name"]."' ORDER BY p.name ASC");
				                                
				$numLinhas = $res_getProp->num_rows;
                ?>
                            <tr>
				<td colspan="1" rowspan="<?php echo $numLinhas; ?>" style="vertical-align: top;"><?php echo $arrayRes['name']; ?></td>
                <?php
				while($read_Props = $res_getProp->fetch_assoc())
				{
					$this->numProp++;
             ?>
					<td><?php echo $read_Props["id"];?></td>
					<td><?php echo $read_Props["name"];?></td>
					<td><?php echo $read_Props["value_type"];?></td>
					<td><?php echo $read_Props["form_field_name"];?></td>
					<td><?php echo $read_Props["form_field_type"];?></td>
					<td>
<?php
                                        if(is_null($read_Props["unit_type_id"]))
					{
?>
						-
<?php
                                        }
					else
					{
						$res_Unit = $this->bd->runQuery("SELECT name FROM prop_unit_type WHERE id = ".$read_Props["unit_type_id"]);
												
						while($arrayNome = $res_Unit->fetch_assoc())
						{
							echo $arrayNome['name'];
						}
					}
?>
					</td>
                                        
                                        <td><?php echo $read_Props["form_field_order"]; ?></td>
					<td><?php echo $read_Props["form_field_size"]; ?></td>
					<td>
<?php					
                                        if($read_Props["mandatory"] == 1)
					{
						echo 'Sim';
					}
					else
					{
						echo 'Não';
					}
?>                                        
					</td>
					<td><?php echo $read_Props["state"]; ?></td>
<?php
						$res_Checkd = $this->bd->runQuery("SELECT * FROM custom_form_has_prop AS cfhp WHERE cfhp.custom_form_id = ".$_REQUEST['form_id']." AND cfhp.property_id = ".$read_Props["id"]);
						if($res_Checkd->num_rows == 1)
						{
                                                    $arrayChecks = $res_Checkd->fetch_assoc();

?>
                                                    <td><input type="checkbox" name="idProp<?php echo $this->numProp; ?>" value="<?php echo $read_Props["id"]; ?>" checked></td>
                                                    <td><input type="text" name="ordem<?php echo $this->numProp; ?>" value="<?php echo $arrayChecks["field_order"]?>"></td>
                                                    <?php
                                                    if ($arrayChecks["mandatory_form"] == 1) {
?>
                                                        <td><input type="radio" name="obrigatorio<?php echo $this->numProp;?>" value="true" checked>Sim
                                                        <input type="radio" name="obrigatorio<?php echo $this->numProp;?>" value="false">Não
                                                    </td>
<?php
                                                    }
                                                    else {
?>
                                                        <td><input type="radio" name="obrigatorio<?php echo $this->numProp;?>" value="true">Sim
                                                        <input type="radio" name="obrigatorio<?php echo $this->numProp;?>" value="false" checked>Não
                                                    </td>
<?php
                                                    }
                                                }
						else
						{
?>
                                                    <td><input type="checkbox" name="idProp<?php echo $this->numProp;?>" value="<?php echo $read_Props["id"];?>"></td>
                                                    <td><input type="text" name="ordem<?php echo $this->numProp ?>"></td>
                                                    <td><input type="radio" name="obrigatorio<?php echo $this->numProp;?>" value="true">Sim
                                                        <input type="radio" name="obrigatorio<?php echo $this->numProp;?>" value="false">Não
                                                    </td>
<?php
                                                }
?>
                                            <input type="hidden" name="id" value="<?php echo $_REQUEST['form_id']; ?>">
                            </tr>	
<?php
                                }
			}
 ?>
                        </tbody>
                <table>
            <input type="submit" value="Atualizar formulário">
        </form>
                                </html>
<?php
	$_SESSION['propSelected'] = $this->numProp;

        }
                                                
	
	/**
	 * This method will activate the custom form the user selected.
	 */
	public function activate(){
            $idForm = $this->bd->userInputVal($_REQUEST['form_id']);
            if($this->gereFormHist->addHist( $idForm,$this->bd))
            {
                $this->bd->runQuery("UPDATE `custom_form` SET state='active' WHERE id=".$idForm);
		$res_formName = $this->bd->runQuery("SELECT name FROM custom_form WHERE id=".$idForm);
		$read_formName = $res_formName->fetch_assoc();
?>
		<html>
		 	<p>O formulário <?php echo $read_formName['name'] ?> foi ativado.</p>
		 	<p>Clique em <a href="/gestao-de-formularios"/>Continuar</a> para avançar.</p>
		</html>
<?php
                $this->bd->getMysqli()->commit();
            }
            else
            {
?>
		<html>
		 	<p>O formulário <?php echo $read_formName['name'] ?> não pôde ser ativado.</p>
                        <p>Ocorreu um erro. Clique em <?php goBack(); ?></p>
		</html>
<?php
                $this->bd->getMysqli()->rollback();
            }
           
        }
	/**
	  * This method will desactivate the custom form the user selected
	  */
	public function desactivate(){
            $idForm = $this->bd->userInputVal($_REQUEST['form_id']);
            $res_formName = $this->bd->runQuery("SELECT name FROM custom_form WHERE id=".$idForm);
            $read_formName = $res_formName->fetch_assoc();
            if($this->gereFormHist->addHist($idForm, $this->bd))
            {
                $this->bd->runQuery("UPDATE `custom_form` SET state='inactive' WHERE id=".$idForm);
		
?>
		<html>
                    <p>O formulário <?php echo $read_formName['name'] ?> foi desativado</p>
                    <p>Clique em <a href="/gestao-de-formularios"/>Continuar</a> para avançar</p>
            	</html>
<?php
                $this->bd->getMysqli()->commit();
            }
            else
            {
?>
		<html>
                    <p>O formulário <?php echo $read_formName['name'] ?> não foi desativado</p>
                    <p>Clique em <a href="/gestao-de-formularios"/>Continuar</a> para avançar</p>
		</html>
<?php   
                $this->bd->getMysqli()->rollback();
            }
        }
	
	/**
	 * This method will handle the insertion that a user will make in the database.
	 */
	public function insertState(){
		if($this->ssvalidation())
		{
                    //echo $_SESSION['propSelected'];
			//Begin Transaction
			$this->bd->getMysqli()->autocommit(false);
			$this->bd->getMysqli()->begin_transaction();
			
			//Starts the insertion in the "database"
			$sanitizedInput = $this->bd->userInputVal($_REQUEST["nome"]);
			$this->bd->runQuery("INSERT INTO `custom_form`(`id`, `name`, `state`)VALUES(NULL,'".$sanitizedInput."','active')");
		
			$getLastId = $this->bd->getMysqli()->insert_id;
			$control = true;
			for($i = 1; $i <= $_SESSION['propSelected'] ; $i++)
			{
				if(isset($_REQUEST["idProp".$i]) && isset($_REQUEST["ordem".$i]) && isset($_REQUEST["obrigatorio".$i]))
				{
					if(!$this->bd->runQuery("INSERT INTO `custom_form_has_prop`(`custom_form_id`, `property_id`, `field_order`, `mandatory_form`) VALUES (".$getLastId.",".$_REQUEST["idProp".$i].",'".$this->bd->userInputVal($_REQUEST["ordem".$i])."',".$this->bd->userInputVal($_REQUEST["obrigatorio".$i]).")"))
					{
                                            $control = false;
?>						
						<html>
							<p>A inserção de do novo formulário falhou</p>
						</html>
<?php 					
                                                goBack();
						$this->bd->getMysqli()->rollback();
					}

					
				}
			}
                        
			if($control == true)
                        {
?>		
						<html>
							<p>Inseriu um novo formulário com sucesso</p>
							<p>Clique em <a href="/gestao-de-formularios/">Continuar</a> para avançar</p>
						</html>
<?php 
						$this->bd->getMysqli()->commit();
			}
		
		}
		else 
		{
			goBack();	
		}
	}
        
        /**
         * This method will update the dataform a selected form
         */
        public function updateForm(){
            if($this->ssvalidation())
            {
                $id = $this->bd->userInputVal($_REQUEST['form_id']);
                
                if($this->gereFormHist->addHist($id,$this->bd))
                {
                    if(!$this->bd->runQuery("UPDATE custom_form  SET name = '".$this->bd->userInputVal($_REQUEST["nome"])."' WHERE id = ".$id.""))
                    {
                        //erro a fazer update ao form
                        $this->bd->getMysqli()->rollback();
                    }
                    else{
                        $control = true;
                        if(!$this->bd->runQuery("DELETE FROM custom_form_has_prop WHERE custom_form_id = ".$id))
                        {
                            //erro a fazer update ao form
                             $control = false;
                             $this->bd->getMysqli()->rollback();
                        }
                        else{
                            for($i = 1; $i <= $_SESSION['propSelected']; $i++)
                                {
                                        if(isset($_REQUEST["idProp".$i]) && isset($_REQUEST["ordem".$i]) && isset($_REQUEST["obrigatorio".$i])) 
                                        {


                                                if(!$this->bd->runQuery("INSERT INTO `custom_form_has_prop`(`custom_form_id`, `property_id`, `field_order`, `mandatory_form`) VALUES (".$id.",".$_REQUEST["idProp".$i].",'".$this->bd->userInputVal($_REQUEST["ordem".$i])."',".$this->bd->userInputVal($_REQUEST["obrigatorio".$i]).")"))
                                                {
                                                       //erro a fazer update ao form
                                                     $control = false;
                                                       $this->bd->getMysqli()->rollback();
                                                }
                                        }
                                }
                                if($control)
                                {
?>
                                    <p>Atualizou o seu formulário com sucesso</p>
                                    <p>Clique em <a href="/gestao-de-formularios/">Continuar</a> para avançar</p>

<?php
                                    $this->bd->getMysqli()->commit();   
                                }
                        }   
                    } 
                }
                else
                {
?>
                        <p>O seu formulário não pôde ser atualizado porque ocorreu um erro.</p>
                        <p>Clique em <?php goBack(); ?></p>
<?php
                        $this->bd->getMysqli()->rollback();
                }
 
            }
            else
            {
                   goBack(); 
            }

        }
}



/**
 * The methods prsent in this class will add the change the users do in the custom
 * forms they have created
 */
class HistDeForms{
    
    public function __construct() {
    }
    /**
     * 
     * @param type $id
     * @param type $bd
     * @return boolean
     */
    public function addHist($id,$bd){
        $bd->getMysqli()->autocommit(false);
        $bd->getMysqli()->begin_transaction();
        
        //gets info from the form that we will be changing 
        $res_getEntTp = $bd->runQuery("SELECT * FROM custom_form WHERE id=".$id."");
        $read_getEntTp = $res_getEntTp->fetch_assoc();
        
        $inactive = date("Y-m-d H:i:s",time());
        if($bd->runQuery("INSERT INTO `hist_custom_form`(`id`, `name`, `state`, `active_on`, `inactive_on`, `custom_form_id`) VALUES (NULL,'".$read_getEntTp['name']."','".$read_getEntTp['state']."','".$read_getEntTp['updated_on']."','".$inactive."',".$id.")")){
           //get all the properties from the seleced form
           $resCf_Prop = $bd->runQuery("SELECT * FROM custom_form_has_prop WHERE custom_form_id=".$id); 
           if($resCf_Prop->num_rows > 0)
           {
             
                while($readCf_Prop = $resCf_Prop->fetch_assoc())
                {
                        //backups the custom form has properties  tuples
                        if(!$bd->runQuery("INSERT INTO `hist_custom_form_has_property`(`property_id`, `field_order`, `mandatory_form`, `active_on`, `inactive_on`, `id`, `custom_form_id`) VALUES (".$readCf_Prop['property_id'].",".$readCf_Prop['field_order'].",".$readCf_Prop['mandatory_form'].",'".$readCf_Prop['updated_on']."','".$inactive."',NULL,".$id.")"))
                        {
                            return false;
                        }
                        //backups each instance of the properties
                        $backupFormProps = $bd->runQuery("SELECT * FROM property WHERE id=".$readCf_Prop['property_id'])->fetch_assoc();
                        if($backupFormProps['rel_type_id'] == ''){
                                $rel_type_id = 'NULL';
                        }
                        else {
                                $rel_type_id =$backupFormProps['rel_type_id'];
                        }
                        
                        if($backupFormProps['unit_type_id'] == ''){
                            $unit_type_id = 'NULL';
                        }
                        else {
                            $unit_type_id = $backupFormProps['unit_type_id'];
                        }
                        if($backupFormProps['fk_ent_type_id'] == '')
                        {
                                $fk_ent_type_id = 'NULL';
                        }
                        else{
                                $fk_ent_type_id = $backupFormProps['fk_ent_type_id'];
                        }
                        if(!$bd->runQuery("INSERT INTO `hist_property`(`id`, `name`, `ent_type_id`, `rel_type_id`, `value_type`, `form_field_name`, `form_field_type`, `unit_type_id`, `form_field_order`, `mandatory`, `state`, `fk_ent_type_id`, `form_field_size`, `property_id`, `active_on`, `inactive_on`) VALUES (NULL,'".$backupFormProps['name']."',".$backupFormProps['ent_type_id'].",".$rel_type_id.",'".$backupFormProps['value_type']."','".$backupFormProps['form_field_name']."','".$backupFormProps['form_field_type']."','".$unit_type_id."',".$backupFormProps['form_field_order'].",".$backupFormProps['mandatory'].",'".$backupFormProps['state']."',".$fk_ent_type_id.",".$backupFormProps['form_field_size'].",".$backupFormProps['id'].",'".$backupFormProps['updated_on']."','".$inactive."')")){
                                return false;
                        }
                }
               
                //updates the current form updated_on field 
                if(!$bd->runQuery("UPDATE `custom_form` SET `updated_on`='".$inactive."' WHERE id=".$id))
                {
                    return false;
                }
                return $inactive;
           }
        }
        return false;   
    }
    
    
    /**
     * This method will create a table where the history will be showned.
     * @param type $id -> id form the selected form
     * @param type $bd
     */
    public function tableHist($id,$bd){
        
                                    if (isset($_REQUEST["controlDia"]) && $_REQUEST["controlDia"] == "ate") {
                                                $goToCFN = $bd->runQuery("SELECT * FROM hist_custom_form WHERE custom_form_id=".$id." AND inactive_on<='".$_REQUEST['data']."' ORDER BY inactive_on DESC");   
                                    }
                                    else if (isset($_REQUEST["controlDia"]) && $_REQUEST["controlDia"] == "aPartir") {
                                                $goToCFN = $bd->runQuery("SELECT * FROM hist_custom_form WHERE custom_form_id=".$id." AND inactive_on>='".$_REQUEST['data']."' ORDER BY inactive_on DESC");
                                    }
                                    else if (isset($_REQUEST["controlDia"]) && $_REQUEST["controlDia"] == "dia"){
                                                $goToCFN = $bd->runQuery("SELECT * FROM hist_custom_form WHERE custom_form_id=".$id." AND inactive_on < '".date("Y-m-d",(strtotime($_REQUEST["data"]) + 86400))."' AND inactive_on >= '".$_REQUEST["data"]."' ORDER BY inactive_on DESC");

                                    }
                                    else {
                                                $goToCFN = $bd->runQuery("SELECT * FROM hist_custom_form WHERE custom_form_id=".$id);

                                    }
        //echo "SELECT * FROM hist_custom_form WHERE custom_form_id=".$id;   
?>
                        <form method="GET">
                                Verificar histórico:<br>
                                <input type="radio" name="controlDia" value="ate">até ao dia<br>
                                <input type="radio" name="controlDia" value="aPartir">a partir do dia<br>
                                <input type="radio" name="controlDia" value="dia">no dia<br>
                                <input type="text" id="datepicker" name="data" placeholder="Introduza uma data">
                                <input type="hidden" name="estado" value="historico">
                                <input type="hidden" name="form_id" value="<?php echo $id; ?>">
                                <input type="submit" value="Apresentar histórico">
                        </form>
                        
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Data de Ativação</th>
                                    <th>Data de Desativação</th>
                                    <th>Nome do Formulário</th>
                                    <th>Propriedade</th>
                                    <th>Nome do campo no formulário</th>
                                    <th>Tamanho do campo no formulário</th>
                                    <th>Ordem do campo no formulário</th>
                                    <th>Obrigatório no forumulário customizado</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
<?php
                           //  echo $goToCFN->num_rows;
                            if($goToCFN->num_rows == 0){
?>                                
                                <tr>
                                    <td colspan="7">Não existe registo referente à entidade selecionada no histórico</td>
                                    <td><?php goBack()?></td>
                                </tr>
<?php 
                            }
                            else
                            {
                                    while($readFNhist = $goToCFN->fetch_assoc()){
?>
                                        <tr> 
<?php
                                            //echo "SELECT * FROM hist_custom_form_has_property WHERE inactive_on='".$readFNhist['inactive_on']."'";
                                            $getPropHist  = $bd->runQuery("SELECT * FROM hist_custom_form_has_property WHERE custom_form_id = ".$id."  AND  inactive_on='".$readFNhist['inactive_on']."'");
                                            $spanSize = $getPropHist->num_rows;                                            
?>              
                                                <td rowspan="<?php echo $spanSize ?>"><?php echo $readFNhist['active_on']?></td>
                                                <td rowspan="<?php echo $spanSize ?>"><?php echo $readFNhist['inactive_on']?></td>
                                                <td rowspan="<?php echo $spanSize ?>"><?php echo $readFNhist['name']?></td>
<?php
                                                $checkIfFIrst = true;
                                                while($getPropId = $getPropHist ->fetch_assoc())
                                               {
                                                        $getPropName = $bd->runQuery("SELECT * FROM hist_property WHERE property_id=".$getPropId['property_id']." AND inactive_on='".$readFNhist['inactive_on']."'" )->fetch_assoc();
?>
                                                    <td><?php echo $getPropName['name']?></td>
                                                    <td><?php echo $getPropName['form_field_name']?></td>
                                                    <td><?php echo $getPropName['form_field_size']?></td>

                                                    <td><?php echo $getPropId['field_order']?></td>
                                                    <td>
<?php 
                                                    if($getPropId['mandatory_form'] == 1)
                                                    {
                                                        echo "Sim";
                                                    }
                                                    else
                                                    {
                                                        echo "Não";
                                                    }
?> 
                                         <?php
                                                        if($checkIfFIrst == true){
                                                           // echo "href=\"?estado=versionBack&histId=<?php echo ."$getPropHist['custom_form_id']."?\>";
?>
                                                        </td> <td rowspan="<?php echo$spanSize ?>">
                                                            
                                                            
                                                        <a href="?estado=versionBack&histId=<?php echo $getPropId['id']?>">Voltar para esta versão</a>
<?php
                                                        $checkIfFIrst =false;
?> 
                                                    </td> 
<?php
                                                        }
?>

                                            </tr>                               
<?php                                   
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
    
    /**
     * Thi method will make the change between the actual form and the form that bellongs to the history
     * @param type $idFormHist -> id from one of the properties of the form that is in the history table.
     * @param type $bd --> database object to allow db operations
     */
    public function changeVersion($idFormHist,$bd){
        //get the id from the form that needs to be changed 
        $formToBack = $bd->runQuery("SELECT custom_form_id, inactive_on FROM hist_custom_form_has_property WHERE id=".$idFormHist)->fetch_assoc();
        
        //Starts doing  abackup onn the actual form that is presented to the user
        $inactive = $this->addHist($formToBack['custom_form_id'], $bd);
        if($inactive== false)
        {
                        $error=true;
        }
        else
        {
                //delete all the tuples that bellong to a form 
                $bd->runQuery("DELETE FROM custom_form_has_prop WHERE custom_form_id = ".$formToBack['custom_form_id']);
                $error = false;
                //Brings back the tuples from the form that is in the history
                $bbProps = $bd->runQuery("SELECT * FROM hist_custom_form_has_property WHERE inactive_on = '".$formToBack['inactive_on']."'");
                $theFirst = true;
                while( $readProps = $bbProps->fetch_assoc()){
                        //insert the old tuples in the main table 
                        if(!$bd->runQuery("INSERT INTO `custom_form_has_prop`(`property_id`, `custom_form_id`, `field_order`, `mandatory_form`, `updated_on`) VALUES (".$readProps['property_id'].",".$readProps['custom_form_id'].",".$readProps['field_order'].",".$readProps['mandatory_form'].",'".$inactive."')")){
                            $error = true;
                            break;
                        }
                        if($theFirst == true){
                        //get the name form the history table
                        $getNamePars = $bd->runQuery("SELECT *  FROM hist_custom_form WHERE inactive_on = '".$formToBack['inactive_on']."'")->fetch_assoc();
                        //updqte tghe current  name to the one that comes form the history
                            if(!$bd->runQuery("UPDATE `custom_form` SET `name`='".$getNamePars['name']."',`state`='".$getNamePars['state']."',`updated_on`='".$inactive."' WHERE id=".$formToBack['custom_form_id'])){
                                $error = true;
                                break;
                            }
                            $theFirst = false;
                        }
                }
                
                if($error == true)
                {
?>
                        <p>O formulário atual não pôde ser substituido pelo antigo.</p>
                        <p>Clique em <?php goBack() ?> para voltar à página anterior</p>
<?php
                        $bd->getMysqli()->rollback();
                }
                else
                {
?>
                        <p>A troca de formulários foi bem sucedida..</p>
                        <p>Clique em <a href="/gestao-de-formularios"/>Continuar</a> para avançar</p>
<?php
                        $bd->getMysqli()->commit();
                }
        }
        
    }
    
    
    /**
     * This method will create a table in a day choosen by the user, and the user will be able to see how the table was in that day
     * @param type $id -> id form the selected form
     * @param type $bd
     */
    	public function tableHistPrint($data,$bd){
                $creatTempTable = "CREATE TEMPORARY TABLE temp_table (
                `id` int(10) unsigned NOT NULL,
                `name` varchar(128) NOT NULL,
                `state` enum('active','inactive') NOT NULL";
                
                $creatTempTable = $bd->runQuery($creatTempTable);
                
                $selecionaProp = "SELECT * FROM custom_form WHERE updated_on < '".$data."' OR updated_on LIKE '".$data."%'";
                $querEntTp = $bd->runQuery($selecionaProp);
                while($readEntTP = $querEntTp->fetch_assoc())
                {
                    $bd->runQuery("INSERT INTO temp_table VALUES (".$readEntTP['id'].",'".$readEntTP['name']."','".$readEntTP['state']."')");
                }
            
                $selecionaHist = "SELECT * FROM hist_custom_form WHERE ('".$data."' > active_on AND '".$data."' < inactive_on) OR ((active_on LIKE '".$data."%' AND inactive_on < '".$data."') OR inactive_on LIKE '".$data."%') GROUP BY id ORDER BY inactive_on DESC";
                $querHist = $bd->runQuery($selecionaHist);
                while($readHist = $querHist->fetch_assoc())
                {
                    $bd->runQuery("INSERT INTO temp_table VALUES (".$readHist['custom_form_id'].",'".$readHist['name']."','".$readHist['state']."')");

                }
                
                $resForm = $bd->runQuery("SELECT * FROM temp_table GROUP BY id ORDER BY id ASC");
		if($resForm->num_rows == 0)
		{
?>	
                                <table class="table">
					<thead>
						<tr>
							<th>Id</th>
							<th>Nome do formulário customizado</th>
							<th>Estado</th>
						</tr>
					</thead>
					<tbody>
                                            <tr>
                            <td colspan="3">Não existem formulários costumizados</td>
                                            </tr>
                                        </tbody>
                                </table>
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
							<th>Nome do formulário customizado</th>
							<th>Estado</th>
						</tr>
					</thead>
					<tbody>
<?php 
						while($readForm = $resForm->fetch_assoc())
						{
?>
							<tr>
								<td><?php echo $readForm['id']; ?></td>
								<td><?php echo $readForm['name']; ?></td>
								<td>
<?php
									if($readForm['state'] === 'active')
									{
?>
										Ativo
<?php 
									}
									else
									{
?>
										Inativo
<?php                                                                   }
?>
								</td>

							</tr>
<?php 
						}
						
?>
					</tbody>
				</table>
			</html>
<?php 
			  $bd->runQuery("DROP TEMPORARY TABLE temp_table");
		}
	}
    
    
    
}
?>
