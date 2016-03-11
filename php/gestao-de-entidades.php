<?php
	require_once("custom/php/common.php");
	
	$bd = new Db_op();
	
	if ( is_user_logged_in() )
	{
            echo "#1";
            if(current_user_can('manage_entities'))
		{
                echo "#2";
			if(empty($_REQUEST['estado']))
			{
                            echo "#3";
				//Apresentar tabela
?>
				<html>
					<table>
						<tbody>
							<tr>
								<td> ID</td>
								<td> Nome</td>
								<td> Estado</td>
								<td> A��o</td>
							</tr>
<?php				
				$res_EntType = $bd->runQuery("SELECT * FROM ent_type");
				//verifica se h� ou n�o entidades
				if(!$res_EntType)
				{
					while($read_EntType = $res_EntType->fetch_assoc())
					{
						//printa a restante tabela
?>						
						<tr>
							<td><?php $read_EntType['id']; ?></td>
							<td><?php $read_EntType['name']?></td>
							<td><?php $read_EntType['state']?></td>
							<td>[editar][desativar]</td>
						</tr>
<?php 
					}	
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
						<p> N�o h� componentes.</p>
					</html>
<?php 			}
?>				
			<html>
					<h3>Gest�o de componentes - introdu��o</h3>
					<form>
						<label>Nome:</label>
						<br>
						<input type="text" name="nome" required>
						<br>	
						<label>Estado:</label><br>
<?php 
						$stateEnumValues = getEnumValues('ent_type','state'); //this function is in common.php
						foreach($enumTipos as $value)
						{
?>
						<html>
							<input type="radio" name="atv_int" value="<?php $value ?>" required><?php $value?>
						</html>	
<?php 								
						}
?>						
						<br>
						<input type="hidden" name="estado" value="inserir">
						<input type="submit" value="Inserir Componente">
					</form>
			</html>
<?php 		}
			else if($_REQUEST['estado'] == 'inserir')
			{

			}
			
		}
		else
		{
			echo "#3";//user n�o tem a capability
		}		
	}
	else
	{
		echo "#4";//user n�o esta logado
	}
	
 ?>
