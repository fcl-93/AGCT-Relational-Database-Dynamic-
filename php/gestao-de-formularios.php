<?php
require_once("custom/php/common.php");

$gerencia = new gereForms();

class gereForms
{
	private $bd;
	private $numProp; //printed properties in the table
	
	public function __construct(){
		$this->bd = new Db_Op();
		$this->numProp = 0;
		$this->checkUser();
	}
	
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
				else if($_REQUEST['estado']== 'inserir')
				{
				
				}
				else if($_REQUEST['estado'] == 'editar_form')
				{
					
				}
				else if ($_REQUEST['estado'] == 'atualizar_form_custom')
				{
					
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
	
	public function tablePrint(){
		$resForm = $this->bd->runQuery("SELECT * FROM custom_form ORDER BY name ASC");
		if($resForm->num_rows == 0)
		{
?>	
			<html>
				<p>Não existem formulários costumizados</p>
			</html>
<?php 
		}
		else
		{
?>
			<html>
				<table>
					<thead>
						<tr>
							<th>Id</th>
							<th>Nome do formulário customizado</th>
						</tr>
					</thead>
					<tbody>
<?php 
						while($readForm = $resForm->fetch_assoc())
						{
?>
							<tr>
								<td><?php echo $readForm['id']; ?></td>
								<td>
									<a href="?estado=editar_form&id='<?php echo $readForm['id']; ?>'"><?php echo $readForm['name']; ?></a>
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

	public function intForm()
	{
?>
		<h3>Gestão de formulários customizados - Introdução</h3>
<?php 
		$resEnt = $this->bd->runQuery("SELECT * FROM ent_type");
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
				<label>Nome do formulário customizado:</label><br>
<<<<<<< HEAD
				<input type="text" name="nome" required>
				<br>
				
=======
				<input type="text" name="nome" required><br>
>>>>>>> branch 'master' of https://github.com/vmcbaptista/AGCT-Relational-Dynamic-Database.git
				<table id="table">
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
						</tr>
					</thead>
					<tbody>
<?php 
					while($readEnt = $resEnt->fetch_assoc())
					{
						$res_GetProps = $this->bd->runQuery("SELECT p.id, p.name, p.value_type, p.form_field_name, p.form_field_type, p.unit_type_id, p.form_field_order,  p.mandatory, p.state FROM property AS p, ent_type AS e WHERE p.ent_type_id = e.id AND e.name LIKE '".$readEnt['name']."' ORDER BY p.name ASC");
						//echo "SELECT p.id, p.name, p.value_type, p.form_field_name, p.form_field_type, p.unit_type_id, p.form_field_order, p.form_field_size, p.mandatory, p.state FROM property AS p, ent_type AS e WHERE p.ent_type_id = e.id AND e.name LIKE ".$readEnt['name']." ORDER BY p.name ASC";
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
								<!--<td><?php echo $readGetProps['form_field_size'];  ?></td>-->
								<td>?</td>					
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
	}
	public function ssvalidation(){}
	
	public function formEdit()
	{}
}
?>
