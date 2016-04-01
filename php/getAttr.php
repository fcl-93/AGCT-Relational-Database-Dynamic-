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
            //echo $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
            $json = file_get_contents("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
            $obj = json_decode($json,true);
            if(json_decode($json) == true )
            {
                echo true;
            }
            else
            {
                echo false;
            }
            
            
            print_r($obj);
            echo $obj->data->id;
            
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