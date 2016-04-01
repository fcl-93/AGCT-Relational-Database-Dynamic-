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
            //Decode Json
            echo  echo $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
            $json = file_get_contents(get_site_url());
            $obj = json_decode($json,TRUE);
            echo $obj['data']['id'];
            
		/*$sanitizeId = $this->bd->userInputVal($obj->data);
		$res_Props = $this->bd->runQuery("SELECT * FROM value WHERE entity_id=".$sanitizeId);
                
                $data = array();
		while($read_Props = $res_Props->fetch_assoc())
		{
			$nome = $this->bd->runQuery("SELECT * FROM property WHERE id=".$read_Props['property_id'])->fetch_assoc()['name'];
                        
                       $data[$nome] = $read_Props['value'].'\n';
			//echo $nome . " : " .$read_Props['value']."</br>";
		}
                
                    //header('Content-Type: application/json');
                    echo json_encode($data);
	*/}
}
?>