<?php 

require_once("custom/php/common.php");
	$addValues = ValoresPermitidos();
/**
 * 
 * @author fabio
 *
 */
class ValoresPermitidos
{
	private $bd;
	/**
	 * Contructor
	 */
	public function __construct(){
		$this->bd = new Dp_Op();
		$this->checkUser();
	}
	/**
	 *  This method will check if the user as the permission to acess this page
	 * and will handle all the Requests states
	 */
	public function checkUser(){
		
		if(is_user_logged_in()){
			if(current_user_can('manage_allowed_values)')){
				if(empty($_REQUEST))
				{
					$res_NProp = $this->bd->runQuery();
					$num_Prop = $res_NProp->num_rows;
					if($num_Prop > 0)
					{
						$this->tablePrint();	
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
				else if($_REQUEST['estado'] == 'introducao') 
				{
					
				}
				else if($_REQUEST['estado'] == 'inserir')
				{
					
				}
			}
			else {
?>
				<html>
					<p>Não tem autorização para a aceder a esta página.</p>
				</html>
<?php 
			}
		}else {
?>
			<html>
				<p>Não tem sessão iniciada.</p>
			</html>
<?php
		}
	}
	public function tablePrint(){}
	public function insertionForm(){}
	
	public function editFrom(){}
	public function activate(){}
	public function desactivate(){}
	public function ssvalidation(){}
	public function insertState(){}
	
}
