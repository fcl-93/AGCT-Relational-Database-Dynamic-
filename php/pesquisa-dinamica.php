<?php
require_once("custom/php/common.php");

$pesquisa = new Search();

class Search{
 
    private $bd;
    public function __construct()
    {
        $this->bd = new Db_Op();
        $this->checkUser();
    }
    
    public function checkUser(){
        $capability = 'dynamic_search';
	if(is_user_logged_in()){
            if(current_user_can($capability)){ 
                if(empty($_REQUEST['estado'])){
                 
                   $this->tableEmpStt();
                }
                else if($_REQUEST['estado'] == 'escolha'){
                    //...
                }
                
                
                
                
                
            }
            else {
?>
            <html>
                <p>Não tem autorização para a aceder a esta página.</p>
            </html>
<?php                
            }
        }
        else{
?>
            <html>
                <p>O utilizador não tem sessão iniciada.</p>   
                <p>Clique <a href="/login">aqui</a> para iniciar sessão.</p>
            </html>
<?php
        }
    }
    
    /**
     * Prints the table when the state is empty this table will have
     * all the entities type that you can select to make searches
     */
    private function tableEmpStt()
    {
        $res_entTypeLst = $this->bd->runQuery("SELECT * FROM ent_type ORDER BY name ASC");
        if($res_entTypeLst->num_rows == 0){
?>
            <html>
                <h3>Pesquisa Dinâmica - escolher componente</h3>
                <p>Não pode efetuar pesquisas uma vez que ainda não foram introduzidos tipos de entidades.</p>
            </html>
<?php
        }
        else
        {
?>
            <h3>Pesquisa Dinâmica - escolher componente</h3>
<?php
            $res_getEnt = $this->bd->runQuery("SELECT id, name FROM  ent_type"); //get all entities from ent type 
?>
            <ul>
                <li>Entidade:</li>
<?php
                while($read_getEnt = $res_getEnt->fetch_assoc())
                {
                    //need to filter the entities previously selected.
                    $res_FilterEntities = $this->bd->runQuery("SELECT ent_type.name, ent_type.id FROM ent_type INNER JOIN property ON property.fk_ent_type_id = ent_type.id AND ent_type.id = '".$read_getEnt['id']."'");
?>
<?php
                            while($read_Filter = $res_FilterEntities->fetch_assoc())
                            {
?>                           
                                <li>
                                <ul>
                                    <li><a href="pesquisa-dinamica?estado=escolha&ent=<?php echo $read_Filter['id']; ?>">[<?php echo $read_Filter['name']; ?>]</a></li>
                                </ul></li>
<?php
                            }
?>
                        
                    
<?php
                }
?>
            </ul>
<?php
        }
    }
    
    
    
    

}
?>
