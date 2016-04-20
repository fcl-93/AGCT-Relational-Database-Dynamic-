<?php

require_once("custom/php/common.php");
$addValues = new ValoresPermitidos();
/**
 *
 * @author fabio
 *
 */
class ValoresPermitidos
{
	private $bd;
        private $histVal;
	/**
	 * Contructor
	 */
	public function __construct(){
		$this->bd = new Db_Op();
                $this->histVal = new ValPerHist();
		$this->checkUser();
	}
	/**
	 *  This method will check if the user as the permission to acess this page
	 * and will handle all the Requests states
	 */
	public function checkUser(){
		$capability = 'manage_custom_forms';
		if(is_user_logged_in())
		{
			if(current_user_can($capability))
			{
				if(empty($_REQUEST))
				{
					$this->tablePrintEntities();
                                        $this->tablePrintRelation();
				}
				else if($_REQUEST['estado'] == 'introducao') 
				{
					$this->insertionForm();
				}
				else if($_REQUEST['estado'] == 'inserir')
				{
					$this->insertState();
				}
				else if($_REQUEST['estado'] == 'ativar')
	 			{
					$this->activate();
	 			}
	 			else if($_REQUEST['estado'] == 'desativar')
	 			{
	 				$this->desactivate();
	 			}
	 			else if($_REQUEST['estado']=='editar')
	 			{
	 				$this->editForm();	 				
	 			}
	 			else if($_REQUEST['estado'] == 'alteracao')
	 			{
	 				$this->changeEnum();
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
                            <p> O utilizador não se encontra logado.</p>
                            <p>Clique <a href="/login">aqui</a> para iniciar sessão.</p>
			</html>
<?php
		}
	}
	/**
	 * This method will be responsable for the table print that will show properties with enum value 
	 * and the diferent values assigned to that field
	 */
	public function tablePrintEntities()
	{
		// gets all properties with enum in value_type.
?>
                        <h3>Gestão de valores permitidos - Entidades</h3>
<?php
		$res_NProp = $this->bd->runQuery("SELECT * FROM property WHERE value_type = 'enum' AND rel_type_id IS NULL ORDER BY `property`.`ent_type_id` ASC"); 
		$num_Prop = $res_NProp->num_rows;
		if($num_Prop > 0)
		{
?>
			<html>
				<table class="table">
					<thead>
						<tr>
							<th>Entidade</th>
							<th>Id</th>
							<th>Propriedade</th>
							<th>Id</th>
							<th>Valores permitidos</th>
							<th>Estado</th>
							<th>Ação</th>
						<tr>
					</thead>
					<tbody>
<?php
						$printedNames = array();
						while($read_PropWEnum = $res_NProp->fetch_assoc())
						{
?>
							<tr>
<?php 				
								//Get all enum values for the property that in will start printing now
								$res_Enum = $this->bd->runQuery("SELECT * FROM prop_allowed_value WHERE property_id=".$read_PropWEnum['id']);
								
								//Get the entity name and id that is related to the property we are printing
								$res_Ent = $this->bd->runQuery("SELECT id, name FROM ent_type WHERE id = ".$read_PropWEnum['ent_type_id']);
								$read_EntName = $res_Ent->fetch_assoc();
								
								//Get the number of properties with that belonh to the etity I'm printing and have enum tipe
								$res_NumProps= $this->bd->runQuery("SELECT * FROM property WHERE ent_type_id = ".$read_PropWEnum['ent_type_id']." AND value_type = 'enum'");
								
								//Get all the enum values that we wil print this is only the number.
								$acerta = $this->bd->runQuery("SELECT * FROM prop_allowed_value as pav ,property as prop, ent_type as ent WHERE ent.id = ".$read_EntName['id']." AND  prop.ent_type_id = ".$read_EntName['id']." AND prop.value_type = 'enum' AND prop.id = pav.property_id");
                                                                $acerta2 = $this->bd->runQuery("SELECT * FROM property WHERE property.id NOT IN (SELECT property_id FROM prop_allowed_value) AND property.value_type='enum' AND ent_type_id =".$read_EntName['id']);
                                                        //verifies if the name i'm printing has ever been written
							$conta = 0;
							for($i = 0; $i < count($printedNames); $i++)
							{
								if($printedNames[$i] == $read_EntName['name'])
								{
									$conta++;
								}
							}

							if($conta == 0)
							{
?>
								<td rowspan='<?php echo $acerta->num_rows + $acerta2->num_rows; ?>'><?php echo $read_EntName['name'];?></td>
<?php 	
								$printedNames[] = $read_EntName['name'];
							}
							else
							{
								//echo '<td rowspan='.mysqli_num_rows($acerta).'>';	

							}
?>
							<td rowspan="<?php echo $res_Enum->num_rows;?>"><?php echo $read_PropWEnum['id'];?></td>
							<!-- Nome da propriedade -->
							<td rowspan="<?php echo $res_Enum->num_rows;?>"><a href="gestao-de-valores-permitidos?estado=introducao&propriedade=<?php echo $read_PropWEnum['id'];?>">[<?php echo $read_PropWEnum['name'];?>]</a></td>

<?php 							
							//$propAllowedArray = mysqli_fetch_assoc($propAllowed);
							if($res_Enum->num_rows == 0)
							{
?>
								<td colspan=4> Não há valores permitidos definidos </td>
<?php 
							}
							else
							{
								while($read_EnumValues = $res_Enum->fetch_assoc())
								{			
?>									
										<td><?php  echo $read_EnumValues['id'];?></td>
										<td><?php echo $read_EnumValues['value'];?></td>
										<td>
<?php 			
										if($read_EnumValues['state'] == 'active')
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
										<td>
										<a href="gestao-de-valores-permitidos?estado=editar&enum_id=<?php echo $read_EnumValues['id'];?>&prop_id=<?php echo $read_PropWEnum['id'];?>">[Editar]</a>  
<?php 
										if($read_EnumValues['state'] === 'active')
										{
?>
											<a href="gestao-de-valores-permitidos?estado=desativar&enum_id=<?php echo $read_EnumValues['id'];?>">[Desativar]</a>
<?php 
										}
										else 
										{
?>
											<a href="gestao-de-valores-permitidos?estado=ativar&enum_id=<?php echo $read_EnumValues['id'];?>">[Ativar]</a>
<?php 
										}
?>										
										</td>
									</tr>		
<?php 								
								}
							}
?>
							</tr>
<?php 
						}
?>
					<tbody>
				</table>
			<html>	
<?php 										
		}
		else
		{
?>
			<html>
				<p>Não existem propriedades especificadas para entidades, cujo tipo de valor seja enum. <br>
				Especificar primeiro nova(s) propriedade(s) e depois voltar a esta opção</p>
			</html>
<?php 						
		}
	}
        
        
        public function tablePrintRelation(){
?>
            <h3>Gestão de valores permitidos - Relações</h3>
<?php
            $res_NProp = $this->bd->runQuery("SELECT * FROM property WHERE value_type = 'enum' AND ent_type_id IS NULL ORDER BY `property`.`rel_type_id` ASC");
            $numberRltn = $res_NProp->num_rows;
            if($numberRltn > 0)
            {
?>
            <html>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Relação</th>
                            <th>Id</th>
                            <th>Propriedade</th>
                            <th>Id</th>
                            <th>Valores permitidos</th>
                            <th>Estado</th>
                            <th>Ação</th>
                        <tr>
                    </thead>
                    <tbody>
<?php
                    $printedId = array();
                    while($read_PropWEnum = $res_NProp->fetch_assoc())
                    {
?>
                        <tr>
<?php
                            //Get all enum values for the property that in will start printing now
                            $res_Enum = $this->bd->runQuery("SELECT * FROM prop_allowed_value WHERE property_id=".$read_PropWEnum['id']);
                                                                    
                            //Get the entity name and id that is related to the property we are printing
                            $res_Rel = $this->bd->runQuery("SELECT * FROM rel_type WHERE id = ".$read_PropWEnum['rel_type_id']);
                            $read_RelName = $res_Rel->fetch_assoc();
                            
                            $res_name1 = $this->bd->runQuery("SELECT * FROM ent_type WHERE id=".$read_RelName['ent_type1_id']);
                            $read_name1 = $res_name1->fetch_assoc();
                            $res_name2 = $this->bd->runQuery("SELECT * FROM ent_type WHERE id=".$read_RelName['ent_type2_id']);
                            $read_name2 = $res_name2->fetch_assoc();
                                                                    
                            //Get the number of properties with that belong to the entity I'm printing and have enum type
                            $res_NumProps= $this->bd->runQuery("SELECT * FROM property WHERE rel_type_id = ".$read_PropWEnum['rel_type_id']." AND value_type = 'enum'");
                                                                    
                            //Get all the enum values that we wil print this is only the number.
                            $acerta = $this->bd->runQuery("SELECT * FROM prop_allowed_value as pav ,property as prop, rel_type as rl_tp WHERE rl_tp.id = ".$read_RelName['id']." AND  prop.rel_type_id = ".$read_RelName['id']." AND prop.value_type = 'enum' AND prop.id = pav.property_id");
                            $acerta2 = $this->bd->runQuery("SELECT * FROM property WHERE property.id NOT IN (SELECT property_id FROM prop_allowed_value) AND property.value_type='enum' AND rel_type_id =".$read_RelName['id']);
                            //verifies if the id i'm printing has ever been printed before
                            $conta = 0;
                            for($i = 0; $i < count($printedId); $i++)
                            {
				if($printedId[$i] == $read_PropWEnum['rel_type_id'])
				{
                                    $conta++;
				}
                                                             
                            }
                                                        
                            if($conta == 0)
                            {
?>
                                <td rowspan='<?php echo $acerta->num_rows + $acerta2->num_rows; ?>'><?php echo $read_name1['name'] ?> - <?php echo $read_name2['name'] ;?></td>
<?php                           
                                $printedId[] = $read_PropWEnum['rel_type_id'];
                            }
?>
                            <td rowspan="<?php echo $res_Enum->num_rows;?>"><?php echo $read_PropWEnum['id'];?></td>
                            <!-- Nome da propriedade -->
                            <td rowspan="<?php echo $res_Enum->num_rows;?>"><a href="gestao-de-valores-permitidos?estado=introducao&propriedade=<?php echo $read_PropWEnum['id'];?>">[<?php echo $read_PropWEnum['name'];?>]</a></td>
                                
<?php 							
							
                            if($res_Enum->num_rows == 0)
                            {
?>
                            <td colspan=4> Não há valores permitidos definidos </td>
<?php

                            }
                            else
                            {
                            while($read_EnumValues = $res_Enum->fetch_assoc()){			
?>			
                            <td><?php  echo $read_EnumValues['id'];?></td>
                            <td><?php echo $read_EnumValues['value'];?></td>
                            <td>
<?php 			
                            if($read_EnumValues['state'] == 'active')
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
                            <td>
                                <a href="gestao-de-valores-permitidos?estado=editar&enum_id=<?php echo $read_EnumValues['id'];?>&prop_id=<?php echo $read_PropWEnum['id'];?>">[Editar]</a>  
<?php 
                                if($read_EnumValues['state'] === 'active')
                                {
?>
                                    <a href="gestao-de-valores-permitidos?estado=desativar&enum_id=<?php echo $read_EnumValues['id'];?>">[Desativar]</a>
<?php 
				}
				else 
				{
?>
                                    <a href="gestao-de-valores-permitidos?estado=ativar&enum_id=<?php echo $read_EnumValues['id'];?>">[Ativar]</a>
<?php 
				}
?>										
                            </td>
                        </tr>		
<?php           

                                }
                                     
                            }
?>
                        </tr>
<?php               }
?>
                    </tbody>
                </table>
            </html>    
<?php
            }
            else
            {
?>
                        <html>
				<p>Não há propriedades especificadas cujo tipo de valor seja enum. <br>
				Especificar primeiro nova(s) propriedade(s) e depois voltar a esta opção</p>
			</html>
<?php                
            }
        }
        
        
        
        
	/**
	 * This method will print the for to insert new enum values.
	 */
	public function insertionForm()
	{
		$_SESSION['property_id'] = $_REQUEST['propriedade'];//
		//print_r($_SESSION);
?>
		<h3>Gestão de valores permitidos - introdução</h3><br>
			<form id="insertForm">
				<label>Valor: </label>
				<input type="text" name="valor">
				<input type="hidden" name="estado" value="inserir">
				<input type="submit" value="Inserir valor permitido">
				<br>
				<label id="valor" class="error" for="valor"></label>
			</form>
<?php 
	}
	/**
	 * This method will print the form and fill it with the properties from the selected enum.
	 */
	public function editForm(){
		$res_EnumName=$this->bd->runQuery("SELECT value FROM prop_allowed_value WHERE id=".$_REQUEST['enum_id']);
		$read_EnumName = $res_EnumName->fetch_assoc();
		?>
			<h3>Gestão de valores permitidos - introdução</h3><br>
				<form id="editForm">
					<label>Valor: </label>
					<input type="text" name="valor" value="<?php echo $read_EnumName['value']; ?>">
					
					<input type="hidden" name="enum_id" value="<?php echo $_REQUEST['enum_id']; ?>">
					<input type="hidden" name="estado" value="alteracao">
					<input type="submit" value="Inserir valor permitido">
					<br>
					<label id="valor" class="error" for="valor"></label>
				</form>
	<?php 
		}
	/**
	 * Check if the value of the form is empty or not
	 */
	public function ssvalidation()
	{
		if(empty($_REQUEST['valor']))
		{
?>
			<html>
				<p>O campo valor é de preenchimento obrigatório.</p>
			</html>
<?php 
			return false;
		}
		else 
		{
			$sanitizedName = $this->bd->userInputVal($_REQUEST['valor']);//for both if's the value input
			$res_CheckPropEnums = $this->bd->runQuery("SELECT * FROM prop_allowed_value WHERE property_id=".$_SESSION['property_id']." AND value='".$sanitizedName."'");
			
			//for the edit submission
			
			if($_REQUEST['estado'] == 'alteracao')
			{
				if($res_CheckPropEnums->num_rows != 0)
				{
?>
					<p>	O valor que está a tentar introduzir já se encontra registado.</p>
<?php 
					return false;
				}
				else
				{
					return true;
				}
			}
			else
			{
				//for the insert submission
				if($_REQUEST['estado'] == 'inserir' && $res_CheckPropEnums->num_rows)
				{
?>
					<p>	O valor que está a tentar introduzir já se encontra registado.</p>
<?php 
					return false;	
				}
				else
				{
					return true;
				}
			}	
		}
	}
	/**
	 * This method will handle the insertion state if the user input is ok
	 */
	public function insertState()
	{
?>
		<h3>Gestão de valores permitidos - inserção</h3>
<?php 
		if($this->ssvalidation())
		{
			//echo "INSERT INTO `prop_allowed_value`(`id`, `property_id`, `value`, `state`) VALUES (NULL,".$_SESSION['property_id'].",'".$_REQUEST['valor']."','active')";
			$_sanitizedInput = $this->bd->userInputVal($_REQUEST['valor']);                        
			$this->bd->runQuery("INSERT INTO `prop_allowed_value`(`id`, `property_id`, `value`, `state`) VALUES (NULL,".$_SESSION['property_id'].",'".$_sanitizedInput."','active')");
?>
		<p>	Inseriu os dados de novo valor permitido com sucesso.</p>
		<p>	Clique em <a href="gestao-de-valores-permitidos"> Continuar </a> para avançar</p>
<?php 
		}
		else 
		{
			goBack();
		}
	}
	

	
	/**
	 * This method will check if the edition that we are trying to make in the enum is of and if it 
	 * is it will submit.
	 */
	public function changeEnum(){
		if($this->ssvalidation())
		{               //new name
				$sanitizedName = $this->bd->userInputVal($_REQUEST['valor']);
				//History generation 
                                $getEnumId = $this->bd->userInputVal($_REQUEST['enum_id']);
                                
                                if($this->histVal->addHist($getEnumId, $this->bd)){
                                    //insert the new value for the enum.
                                    $this->bd->runQuery("UPDATE `prop_allowed_value` SET value='".$sanitizedName."' WHERE id=".$getEnumId);
				//echo "UPDATE `prop_allowed_value` SET value='".$sanitizedName."' WHERE id=".$_REQUEST['enum_id'];
?>
                                    <p>	Alterou o nome do valor enum selecionado para <?php echo $_REQUEST['valor'] ?>.</p>
                                    <p>	Clique em <a href="gestao-de-valores-permitidos"> Continuar </a> para avançar</p>
<?php 
                                }
                                else
                                {
?>
                                
                                    <p>O nome do valor enum selecionado não pode ser alterado para <?php echo $_REQUEST['valor'] ?>.</p>
                                    <p>	Clique em <?php goBack(); ?></p>
<?php
                                }
                                
		}
		else
		{
			goBack();
		}
	}
	/**
	 * This method will activate the enum.
	 */
	public function activate(){
		$this->bd->runQuery("UPDATE `prop_allowed_value` SET state='active' WHERE id=".$_REQUEST['enum_id']);
		$res_enumName = $this->bd->runQuery("SELECT value FROM prop_allowed_value WHERE id=".$_REQUEST['enum_id']);
		$read_enumName = $res_enumName->fetch_assoc();
?>
	<html>
	 	<p>O valor <?php echo $read_enumName['value'] ?> foi ativado</p>
	 	<p>Clique em <a href="/gestao-de-valores-permitidos"/>Continuar</a> para avançar</p>
	</html>
<?php
	}
	/**
	 * This method will desactivate the enum values
	 */
	public function desactivate(){
		$this->bd->runQuery("UPDATE `prop_allowed_value` SET state='inactive' WHERE id=".$_REQUEST['enum_id']);
		$res_enumName = $this->bd->runQuery("SELECT value FROM prop_allowed_value WHERE id=".$_REQUEST['enum_id']);
		$read_enumName = $res_enumName->fetch_assoc();
?>
		<html>
		 	<p>O valor <?php echo $read_enumName['value'] ?> foi desativado</p>
		 	<p>Clique em <a href="/gestao-de-valores-permitidos"/>Continuar</a> para avançar</p>
		</html>
<?php
	}
	
}
/**
 * History table gestion class 
 * will have all the methods to change the history
 */
class ValPerHist{
    
    //Constructor
    public function __construct(){}
    
    /**
     * Will an item to the table hist_prop_allowed_value
     * to generate the historic with all modifications.
     *
     * @param type $id -> enum from the id that will be changed, this id comes sanitized.
     * @param type $bd -> database object to allow me to use the database run querys.
     * @return boolean 
     */
    public function addHist($id,$bd){
        //get the old enum
        $res_oldEnum = $bd->runQuery("SELECT * FROM prop_allowed_value WHERE id=".$id);      
        
        if($res_oldEnum->num_rows == 1)
        {
            $read_oldEnum = $res_oldEnum->fetch_assoc();
            
            if($bd->runQuery("INSERT INTO `hist_prop_allowed_value`(`id`, `property_id`, `value`, `state`, `prop_allowed_value_id`, `active_on`, `inactive_on`)"
                    . " VALUES (NULL,".$read_oldEnum['property_id'].",'".$read_oldEnum['value']."','".$read_oldEnum['state']."',".$read_oldEnum['id'].",'".$read_oldEnum['updated_on']."','".date("Y-m-d H:i:s",time())."'")
                    )
            {
                //the history was created
                return true;
            }
        }//the history is not created due to an error in one of the queries or insertions.
        return false;
        
    }
}

?>