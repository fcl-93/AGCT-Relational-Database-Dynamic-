<?php
require_once("custom/php/common.php");
	
	$getData = new FetchData();

class FetchData{
	
	private $bd;
	
	public function __construct(){
		$this->bd = new Db_Op();
		$this->getData();
	}
	
	public function getData(){
                $sanitizeId = $this->bd->userInputVal($_REQUEST['ent']);
		$res_Props = $this->bd->runQuery("SELECT * FROM value WHERE entity_id=".$sanitizeId);
                if($res_Props->num_rows == 0)
                {
?>
                    <p><span id="results">A entidade selecionada não tem propriedades associadas.</span></p>
<?php
                }
                else 
                {
                               while($read_Props = $res_Props->fetch_assoc())
                               {
                                       $nome = $this->bd->runQuery("SELECT * FROM property WHERE id=".$read_Props['property_id'])->fetch_assoc()['name'];

?>
                                       <p><span id="results"><?php echo $nome . " : " .$read_Props['value']."\n";?></span></p>
<?php
                               }

                }


                
	}
}
?>