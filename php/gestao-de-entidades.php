<?php
require_once("custom/php/common.php");

$entity = new Entidade();

/**
 * This method present in this class will handle all the operations that we can do in 
 * Entity page.
 * @author fabio
 *
 */
class Entidade {

    private $bd;
    private $gereHist;

    /**
     * Constructor
     */
    public function __construct() {
        $this->bd = new Db_Op();
        $this->gereHist = new EntHist();
        $this->checkUser();
    }

    /**
     * Checks if the user has permission to use the page.
     */
    public function checkUser() {
        if (is_user_logged_in()) {
            if (current_user_can('manage_entities')) {
                if (empty($_REQUEST['estado'])) {
                    $this->tableToprint();
                    $this->form(); // object lead the method to print the form 
                } else if ($_REQUEST['estado'] == 'editar') {
                    $this->editEntity($_REQUEST['ent_id']);
                } else if ($_REQUEST['estado'] == 'ativar') {
                    $this->enableEnt();
                } else if ($_REQUEST['estado'] == 'desativar') {
                    $this->disableEnt();
                } else if ($_REQUEST['estado'] == 'alteracao') {
                    $this->changeEnt();
                } else if ($_REQUEST['estado'] == 'inserir') {
                    $this->insertEnt();
                } else if ($_REQUEST['estado'] == 'historico') {
                    if(isset($_REQUEST["histAll"])){
                        if ($this->bd->validaDatas($_REQUEST['data'])) {
                            $this->gereHist->tablePresentHist($this->bd->userInputVal($_REQUEST['data']),$this->bd);
                        }
                    }
                    else{
                        $this->gereHist->tableHist($this->bd->userInputVal($_REQUEST['ent_id']), $this->bd);
                    }
                } else if ($_REQUEST['estado'] == 'versionBack') {
                    $this->gereHist->returnPreviousVersion($this->bd->userInputVal($_REQUEST['histId']), $this->bd);
                }
                else if($_REQUEST['estado'] == 'histAll')
                {
                    
                }
            } else {
                ?>
                <html>
                    <p> Não tem autorização para aceder a esta página.</p>
                </html>
                <?php
            }
        } else {
            ?>
            <html>
                <p> O utilizador não se encontra logado.</p>
                <p>Clique <a href="/login">aqui</a> para iniciar sessão.</p>
            </html>
            <?php
        }
    }

    /**
     * This method will print the table that will show all the ent_types
     */
    public function tableToprint() {
    //echo "Olá Olé";
?>      
        <form method="GET">
            Verificar entidades existentes no dia : 
            <input type="text" class="datepicker" id="datepicker" name="data" placeholder="Introduza uma data"> 
            <input type="hidden" name="estado" value="historico">
            <input type="hidden" name="histAll" value="true">
            <input type="submit" value="Apresentar propriedades">
        </form>
<?php
        //Apresentar tabela
        $res_EntType = $this->bd->runQuery("SELECT * FROM ent_type");
       
        //verifica se há ou não entidades
        if ($res_EntType->num_rows > 0) {
            ?>
            <html>
                <table class="table">
                    <thead>
                        <tr>
                            <th> ID</th>
                            <th> Nome</th>
                            <th> Propriedade</th>
                            <th>Tipo de Valor</th>
                            <th> Estado</th>
                            <th> Ação</th>
                        </tr>
                    </thead>
                    <tbody>
<?php
            while ($read_EntType = $res_EntType->fetch_assoc()) { //print_r($read_EntType);
            //printa a restante tabela
                $selProp = "SELECT * FROM property WHERE ent_type_id = ".$read_EntType['id']." AND state = 'active'";
                $selProp = $this->bd->runQuery($selProp);
                //print_r($selProp);
                $numLinhas = $selProp->num_rows;
                $conta = 0;
                if($selProp->num_rows == 0)
                {
?>
                    <tr>
                        <td rowspan="<?php echo 1;?>"><?php echo $read_EntType['id']; ?></td>
                        <td rowspan="<?php echo 1;?>"><?php echo $read_EntType['name'] ?></td>
                        <td colspan="2"> Não existem propriedades associadas a este tipo de entidade </td>
                        <td>
<?php
                        if ($read_EntType['state'] === 'active') 
                        {
                            echo "Ativo";
                        }
                        else
                        {
                            echo "Inativo";
                        }
?>
                        </td>
                        <td>
                            <a href="gestao-de-entidades?estado=editar&ent_id=<?php echo $read_EntType['id']; ?>">[Editar Tipo de entidade]</a>
<?php
                            if ($read_EntType['state'] === 'active') {
?>
                                <a href="gestao-de-entidades?estado=desativar&ent_id=<?php echo $read_EntType['id']; ?>">[Desativar]</a>
<?php
                            }
                            else
                            {
?>
                            <a href="gestao-de-entidades?estado=ativar&ent_id=<?php echo $read_EntType['id']; ?>">[Ativar]</a>
<?php
                            }
?>
                            <a href="gestao-de-entidades?estado=historico&ent_id=<?php echo $read_EntType['id']; ?>">[Histórico]</a><br>
                            <a href="gestao-de-propriedades?estado=introducao&ent_id=<?php echo $read_EntType['id']; ?>">[Adicionar propriedades]</a>
                            <a href="gestao-de-propriedades?estado=editar&ent_id=<?php echo $read_EntType['id']; ?>">[Editar propriedades]</a>

                            <a href="pesquisa-dinamica?estado=execucao&ent=<?php echo $read_EntType['id']; ?>">[Ver instâncias das entidades]</a>

                        </td>
                    </tr>
<?php
                }
                else
                {
                    while ($prop = $selProp->fetch_assoc()) {
                        if ($conta > $numLinhas) {
                            $conta = 0;
                        }
    ?>						
                    <tr>
    <?php
                        if ($conta == 0) {
    ?>
                                <td rowspan="<?php echo $numLinhas;?>"><?php echo $read_EntType['id']; ?></td>
                                <td rowspan="<?php echo $numLinhas;?>"><?php echo $read_EntType['name'] ?></td>
    <?php
                               }
    ?>
                        <!--property name-->
                        <td><?php echo $prop['name'] ?></td>
                        <td><?php echo $prop['value_type'] ?></td>
    <?php
                        if($conta == 0) {
                            if ($read_EntType['state'] === 'active') {
    ?>								
                                <td rowspan="<?php echo $numLinhas;?>"> Ativo </td>
                                <td rowspan="<?php echo $numLinhas;?>">
                                    <a href="gestao-de-entidades?estado=editar&ent_id=<?php echo $read_EntType['id']; ?>">[Editar]</a>  
                                    <a href="gestao-de-entidades?estado=desativar&ent_id=<?php echo $read_EntType['id']; ?>">[Desativar]</a>
                                    <a href="gestao-de-entidades?estado=historico&ent_id=<?php echo $read_EntType['id']; ?>">[Histórico]</a> 
                                    <br>
                                    <a href="gestao-de-propriedades?estado=introducao&ent_id=<?php echo $read_EntType['id']; ?>">[Adicionar propriedades]</a>
                                    <a href="gestao-de-propriedades?estado=editar&ent_id=<?php echo $read_EntType['id']; ?>">[Editar propriedades]</a>

                                    <a href="pesquisa-dinamica?estado=execucao&ent=<?php echo $read_EntType['id']; ?>">[Ver instâncias das entidades]</a>
                                </td>
    <?php
                            } else {
    ?>
                                <td rowspan="<?php echo $numLinhas;?>"> Inativo </td>
                                <td rowspan="<?php echo $numLinhas;?>">
                                    <a href="gestao-de-entidades?estado=editar&ent_id=<?php echo $read_EntType['id']; ?>">[Editar]</a>  
                                    <a href="gestao-de-entidades?estado=ativar&ent_id=<?php echo $read_EntType['id']; ?>">[Ativar]</a>
                                    <a href="gestao-de-entidades?estado=historico&ent_id=<?php echo $read_EntType['id']; ?>">[Histórico]</a><br>  
                                    
                                    <a href="gestao-de-propriedades?estado=introducao&ent_id=<?php echo $read_EntType['id']; ?>">[Adicionar propriedades]</a>
                                    <a href="gestao-de-propriedades?estado=editar&ent_id=<?php echo $read_EntType['id']; ?>">[Editar propriedades]</a>

                                     <a href="pesquisa-dinamica?estado=execucao&ent=<?php echo $read_EntType['id']; ?>">[Ver instâncias das entidades]</a>
                                </td>	
                                
    <?php
                            }
                        }
    ?>
                    </td>
                </tr>
    <?php
                    $conta++;
                    }
                }
            }
?>
        </tbody>
    </table>
</html>

<?php
        } else {
            ?>
            <html>
                <p> Não há entidades.</p>
            </html>
                            <?php
                        }
}


                    
                    
    /**
      * This method will be responsable for the print of the form
      */
    public function form() {
                        ?>
        <html>
            <h3 align="center">Gestão de Componentes - Introdução</h3>
            <form id="insertForm" align="center"> 
                <label>Nome:</label>
                <br>
                <input type="text" id="nome" name="nome">
                <br>
                <label class="error" for="nome"></label>
                <br>
                <label>Estado:</label><br>
        <?php
        $stateEnumValues = $this->bd->getEnumValues('ent_type', 'state'); //this function is in common.php
        //print_r($stateEnumValues);

        foreach ($stateEnumValues as $value) {
            if ($value == 'active') {
                ?>				
                        <html>
                            <input type="radio" id="atv_int" name="atv_int" value="active" >Ativo
                            <br>
                        </html>
                <?php
            } else {
                ?>
                        <html>
                            <input type="radio" id="atv_int" name="atv_int" value="inactive" >Inativo
                            <br>
                        </html>
                <?php
            }
        }
        ?>
                <label class="error" for="atv_int"></label>
                <br>
                <input type="hidden" name="estado" value="inserir">
                <input type="submit" value="Inserir Componente">
            </form>
        </html>
        <?php
    }

    /**
     * This method will do the server side validation
     */
    public function ssvalidation() {
        echo '<h3>Gestão de componentes - inserção</h3>';
        if (empty($_REQUEST['nome'])) {
            ?>
            <html><p>O campo nome é de preenchimento obrigatório.</p></html>
                    <?php
                    return false;
                } elseif (empty($_REQUEST['atv_int'])) {
                    ?>
            <html><p>Deve escolhe uma das opções do campo estado.</p></html>
                    <?php
                    return false;
                } else {
                    $sanitizeName = $this->bd->userInputVal($_REQUEST['nome']);
                    $res_checkRep = $this->bd->runQuery("SELECT * FROM ent_type WHERE name like '" . $sanitizeName . "'");
                    if ($res_checkRep->num_rows) {
                        ?>
                <html><p>Já existe uma entidade do tipo que está a introduzir.</p></html>
                <?php
                return false;
            } else {
                return true;
            }
        }
    }

    /**
     * This method will be responsable for populated the form for the user to be able to  edit a selected entity
     */
    public function editEntity($ent_id) {
        $res_EntEdit = $this->bd->runQuery("SELECT * FROM ent_type WHERE id='" . $ent_id . "'");
        $read_EntToEdit = $res_EntEdit->fetch_assoc();
        ?>		
        <html>
            <h3>Gestão de Componentes - Edição</h3>
            <form id="editForm">
                <label>Nome:</label>
                <br>
                <input type="text" id="nome" name="nome" value="<?php echo $read_EntToEdit['name'] ?>">
                <br>
                <label class="error" for="nome"></label>
                <br>
        <?php
        $stateEnumValues = $this->bd->getEnumValues('ent_type', 'state');
        foreach ($stateEnumValues as $value) {
            if ($value == 'active') {
                if ($read_EntToEdit['state'] == 'active') {
                    ?>
                            <input type="radio" id="atv_int" name="atv_int" value="active" checked="checked" >Ativo
                            <br>
                    <?php
                } else {
                    ?>
                            <input type="radio" id="atv_int" name="atv_int" value="active" >Ativo
                            <br>
                    <?php
                }
            } else {
                if ($read_EntToEdit['state'] == 'inactive') {
                    ?>
                            <input type="radio" id="atv_int" name="atv_int" value="inactive" checked="checked" >Inativo
                            <br>
                    <?php
                } else {
                    ?>
                            <input type="radio" id="atv_int" name="atv_int" value="inactive" >Inativo
                            <br>	
                    <?php
                }
            }
        }//fim for each
        ?>

                <label class="error" for="atv_int"></label>
                <br>
                <input type="hidden" name="ent_id" value="<?php echo $read_EntToEdit['id'] ?>">
                <input type="hidden" name="estado" value="alteracao">
                <input type="submit" value="Alterar Componente">
            </form>
        </html>
    <?php
    }

    /**
     *  This method will check if is everything ok with the submited data and if really is
     *  it will update the existing entity
     */
    public function changeEnt() {
        if ($this->ssvalidation()) { // / verifies if all the field are filled and if the name i'm trying to submit exists in ent_type
            $sanitizeName = $this->bd->userInputVal($_REQUEST['nome']);

            //	print_r($_REQUEST);
            //	echo "UPDATE `ent_type` SET `name`=".$sanitizeName.",`state`=".$_REQUEST['atv_int']." WHERE id = ".$_REQUEST['ent_id']."";


            $id = $this->bd->userInputVal($_REQUEST['ent_id']);
            if ($this->gereHist->addHist($id, $this->bd)) {
                $res_EntTypeAS = $this->bd->runQuery("UPDATE `ent_type` SET `name`='" . $sanitizeName . "',`state`='" . $_REQUEST['atv_int'] . "' WHERE id = " . $id . "");
                ?>
                <p>Alterou os dados da entidade com sucesso.</p>
                <p>Clique em <a href="/gestao-de-entidades"/>Continuar</a> para avançar</p>
                        <?php
                        $this->bd->getMysqli()->commit();
                    } else {
                        ?>
                <h3>Gestão de Componentes - Edição</h3>
                <p>O tipo de entidade não foi alterado.</p>

                        <?php
                        goBack();
                        $this->bd->getMysqli()->rollback();
                    }
                } else {
                    goBack();
                }
            }

            /**
             * This method will disable an enttity when we click in desactivar button 
             */
            public function disableEnt() {
                $id = $this->bd->userInputVal($_REQUEST['ent_id']);
            //verifica se existem instancias deste tipo de entidade ativos.
            $checkEnt = $this->bd->runQuery("SELECT * FROM entity WHERE ent_type_id=".$id." AND state='active'");
             $res_EntTypeD = $this->bd->runQuery("SELECT name FROM ent_type WHERE id = " . $id);
            if($checkEnt->num_rows == 0)
            {
               
                $read_EntTypeD = $res_EntTypeD->fetch_assoc();

                if ($this->gereHist->addHist($id, $this->bd)) 
                {
                    $this->bd->runQuery("UPDATE ent_type SET state='inactive', updated_on='" . date("Y-m-d H:i:s", time()) . "' WHERE id =" . $id);
?>
                    <p>A entidade <?php echo $read_EntTypeD['name'] ?>  foi desativada</p>
                    <p>Clique em <a href="/gestao-de-entidades"/>Continuar</a> para avançar</p>
                    <?php
                    $this->bd->getMysqli()->commit();
                } else {
                    ?>
                    <p>A entidade <?php echo $read_EntTypeD['name'] ?>  não pode ser desativada</p>
                    <?php
                    goBack();

                    $this->bd->getMysqli()->rollback();
                }
            }
            else
            {
                $read_EntTypeD = $res_EntTypeD->fetch_assoc();

?>
                    <p>O tipo de entidade <b><?php echo $read_EntTypeD['name'] ?></b>  não pode ser desativado.</p>
                    <p>Uma vez que existem instâncias deste tipo de entidade ativas.</p>
                    <p>Clique em <a href="/pesquisa-dinamica/?estado=execucao&ent=<?php echo $id?>"/>desativar</a> para pesquisar pelas</p>
                    <p>entidades que pretende desativar ou clique em <?php goBack();?> para voltar a página anterior.</p>
<?php
            }
        ?>

        <?php
    }

    /**
     * This method will enable the entity when we click in then activate button 
     */
    public function enableEnt() {

        $id = $this->bd->userInputVal($_REQUEST['ent_id']);
        
        $res_EntTypeA = $this->bd->runQuery("SELECT name FROM ent_type WHERE id = " . $id);
        $read_EntTypeA = $res_EntTypeA->fetch_assoc();


        if ($this->gereHist->addHist($id, $this->bd)) {
            $this->bd->runQuery("UPDATE ent_type SET state='active', updated_on='" . date("Y-m-d H:i:s", time()) . "' WHERE id =" . $id);
            ?>                        
            <html>
                <p>A entidade <?php echo $read_EntTypeA['name'] ?> foi ativada</p>
                <p>Clique em <a href="/gestao-de-entidades"/>Continuar</a> para avançar</p>
            </html>
            <?php
            $this->bd->getMysqli()->commit();
        } else {
            ?>
            <p>A entidade <?php echo $read_EntTypeA['name'] ?>  não pode ser ativada</p>
            <?php
            goBack();
            $this->bd->getMysqli()->rollback();
        }
    }

    /**
     * This method will insert a new entity in the database
     */
    public function insertEnt() {
        if ($this->ssvalidation()) {
            //print_R($_REQUEST);
            $sanitizeName = $this->bd->userInputVal($_REQUEST['nome']);

            //get time stamp 
            //$time = $_SERVER['REQUEST_TIME'];
            $queryInsert = "INSERT INTO `ent_type`(`id`, `name`, `state`, `updated_on`) VALUES (NULL,'" . $sanitizeName . "','" . $_REQUEST['atv_int'] . "','" . date("Y-m-d H:i:s", time()) . "')";
            $res_querState = $this->bd->runQuery($queryInsert);
            ?>
            <p>Inseriu os dados de uma nova entidade com sucesso</p>
            <p>Clique em <a href="/gestao-de-entidades"/>Continuar</a> para avançar</p>
            <?php
        } else {
            goBack();
        }
    }

}

class EntHist {

    public function __construct() {
        
    }

    /**
     * This method will add a backup to the mirror table of ent_type all the tuples in that table are
     * @param type $id
     * @return boolean
     */
    public function addHist($id, $bd) {

        $bd->getMysqli()->autocommit(false);
        $bd->getMysqli()->begin_transaction();
        //gets info from the ent_type that id about to get changed
        $res_getEntTp = $bd->runQuery("SELECT * FROM ent_type WHERE id=" . $id . "");
        $read_getEntTp = $res_getEntTp->fetch_assoc();
        //create a copy in the history table  
        $inactive = date("Y-m-d H:i:s", time());
        echo $inactive;
        if ($bd->runQuery("INSERT INTO `hist_ent_type`(`id`, `name`, `state`, `active_on`, `inactive_on`, `ent_type_id`) VALUES (NULL,'" . $read_getEntTp['name'] . "','inactive','" .$read_getEntTp['updated_on']. "','" . $inactive . "'," . $id . ")")) {
           $bd->runQuery("UPDATE ent_type SET updated_on='" .$inactive. "' WHERE id =" . $id);
           
           $error = false;
//           $saveProps = $bd->runQuery 
//               
//           }
           return true;
        }
        return false;
//           if($error == false){
//            return true;
//           }
//           return false;
//        } else {
//            return false;
//        }
    }

    /**
     * This method will change the atual entitie to an old one
     * that is present in the history table
     */
    public function returnPreviousVersion($id, $bd) {
        $bd->getMysqli()->autocommit(false);
        $bd->getMysqli()->begin_transaction();
        $inactive = date("Y-m-d H:i:s", time()); //current date.
        $goToEnt = $bd->runQuery("SELECT * FROM `hist_ent_type` WHERE id=" . $id)->fetch_assoc();
        //Backup the actual entities and properties that exist in the table.
         if($this->helpReturnPrev($goToEnt['ent_type_id'],$bd,$inactive,$goToEnt['id']))
         {//if sucessfully backup
             
             //update ao updated_on do tipo de entidade
            //$bd->runQury("UPDATE ent_type SET updated_on='".$inactive."' WHERE id=".$goToEnt['ent_type_id']);
             //update updated_on das notas propriedades

?>                <html>
                    <p>A reversão para uma versão anterior foi bem sucedida.</p>
                    <p>Clique em <a href="/gestao-de-entidades"/>Continuar</a> para avançar</p>
                </html>
                <?php
                $bd->getMysqli()->commit();
         }
         else
         {
             //if something went wrong during the backup process of the entity
?>
             <html>
                    <p>Ocorreu um erro. Não possível voltar à versão anterior (#1).</p>
                    <p>Clique em <?php goBack() ?> para voltar a página anterior</p>
                </html>
<?php
                $bd->getMysqli()->rollback();             
         }
       
    }
    /**
     * This method will make the backup of the current entities and properties that exist
     * $id id from the entity that is currently active
     * $bd used to make database operations
     * $hour in which we are turning off the properties and the entity type
     */
    private function helpReturnPrev($id,$bd,$inactive,$idEntHist){
        $getCurrEnt = "SELECT * FROM ent_type WHERE id=".$id;
        echo "SELECT * FROM ent_type WHERE id=".$id;
        $resCurrEnt = $bd->runQuery($getCurrEnt);
        if($resCurrEnt)
        {
           //it always returns one value no need for a while
           $readCurrEnt = $resCurrEnt->fetch_assoc();
           if($bd->runQuery("INSERT INTO `hist_ent_type`(`id`, `name`, `state`, `active_on`, `inactive_on`, `ent_type_id`) VALUES (NULL,'" . $readCurrEnt['name'] . "','inactive','" .$readCurrEnt['updated_on']. "','" . $inactive . "'," . $id . ")")) 
           {
               $bd->runQuery("UPDATE ent_type SET updated_on='".$inactive."'");
                $getCurrProps = $bd->runQuery("SELECT * FROM property WHERE ent_type_id = " .$id."");
                
                $getEntHist = $bd->runQuery("SELECT * FROM hist_ent_type WHERE id=".$idEntHist)->fetch_assoc();
                $error = false;
                while($prop = $getCurrProps->fetch_assoc())
                {
                    
                    //Se as propriedades são diferente vão pro historico caso contrário não faço nada
                  
                    if( $prop['updated_on'] < $getEntHist['inactive_on'] )//propriedade ta na principal
                    {}
                    else
                    { //prop tá no historico (vai pro)
                        
                        $checkPropAct = $bd->runQuery("SELECT * FROM property WHERE id=".$prop['id']);
                        $checkPropHist = $bd->runQuery("SELECT * from hist_property WHERE property_id=".$prop['id']);
                        //echo "SELECT * FROM property WHERE id=".$prop['id']."<br>";
                        //echo "SELECT * from hist_property WHERE property_id=".$prop['id']."<br>";
                        //echo $checkPropAct->num_rows ." = ". $checkPropHist->num_rows ." ? <br>";
                        
                        //Verifica se tenho uma propriedade agora que n esteja no historico
                        if($checkPropAct->num_rows > 0 && $checkPropHist->num_rows == 0)
                        {
                            $bd->runQuery("UPDATE property SET state='inactive', updated_on='".$inactive."' WHERE id=".$prop['id']);
                            //echo "UPDATE property SET state='inactive', updated_on='".$inactive."' WHERE id=".$prop['id'];
                        }
                        else{
                            $getAllHist = "SELECT * FROM hist_property WHERE property_id=".$prop['id']." AND inactive_on='".$getEntHist['inactive_on']."'";
                            $resGetHist = $bd->runQuery($getAllHist);
                            $getHist = $resGetHist->fetch_assoc();
                            
                            
                        $getHist['rel_type_id']==""? $rel = "NULL" : $rel = $getHist['rel_type_id'];
                        $getHist['unit_type_id'] == "" ? $unit = "NULL" : $unit = $getHist['unit_type_id'];
                        $getHist['form_field_size'] == "" ? $f_sz = "NULL" : $f_sz = $getHist['form_field_size'];
                        $getHist['fk_ent_type_id'] == ""? $fk_ent= "NULL" : $fk_ent = $getHist['fk_ent_type_id'];
                        
                            
                            
                            //$bd->runQuery("UPDATE property SET state='active', ,rel_type_id='".$rel."',ent_type_id='".$getHist['ent_type_id']."' ,name='".$getHist['name']."', updated_on='".$inactive."', form_field_order='".$getHist['form_field_order']."' WHERE id=".$prop['id']);
                            $bd->runQuery("UPDATE `property` SET `name`='".$getHist['name']."',`ent_type_id`='".$getHist['ent_type_id']."',`rel_type_id`=".$rel.",`value_type`='".$getHist['value_type']."',`form_field_name`='".$getHist['form_field_name']."',`form_field_type`='".$getHist['form_field_type']."',`unit_type_id`='".$unit."',`form_field_order`='".$getHist['form_field_order']."',`mandatory`='".$getHist['mandatory']."',`state`='".$getHist['state']."',`fk_ent_type_id`='".$fk_ent."',`form_field_size`='".$f_sz."',`updated_on`='".$inactive."' WHERE id=".$prop['id']."");
                        }
                        
                        $prop['rel_type_id']==""? $rel = "NULL" : $rel = $prop['rel_type_id'];
                        $prop['unit_type_id'] == "" ? $unit = "NULL" : $unit = $prop['unit_type_id'];
                        $prop['form_field_size'] == "" ? $f_sz = "NULL" : $f_sz = $prop['form_field_size'];
                        $prop['fk_ent_type_id'] == ""? $fk_ent= "NULL" : $fk_ent = $prop['fk_ent_type_id'];

                        $query = "INSERT INTO `hist_property`(`id`, `name`, `ent_type_id`, `rel_type_id`, `value_type`, `form_field_name`, `form_field_type`, `unit_type_id`, `form_field_order`, `mandatory`, `state`, `fk_ent_type_id`, `form_field_size`, `property_id`, `active_on`, `inactive_on`) "
                                . "VALUES (NULL,'".$prop['name']."',".$prop['ent_type_id'].",'".$rel."','".$prop['value_type']."','".$prop['form_field_name']."','".$prop['form_field_type']."',".$unit.",'".$prop['form_field_order']."','".$prop['mandatory']."','inactive',".$fk_ent.",'".$f_sz."','".$prop['id']."','".$prop['updated_on']."','".$inactive."')";
                        
                        if(!$bd->runQuery($query))
                        {
                            $error = true;
                            break;
                        }
                        
                        
                       
                    }
                }
                
                if($error == false)
                {
                    return true;
                }
                return false;
           }
           return false;
        }
        return false;
    }
    
    /**
     * Changes the state of the property according with the version.
     * @param type $idEnt
     */
    private function changeProp ($idEnt, $bd) {
        $getProp = $bd->runQuery("SELECT * from property WHERE ent_type_id = ".$idEnt);
        
        while ($prop = $getProp->fetch_assoc()) {
            $histProp = $bd->runQuery("SELECT * from hist_property WHERE property_id = ".$prop['id']);
            while ($hist = $histProp->fetch_assoc()) {
                
            }
        }
        
    }

    /**
     * This method will create a table where the history will be showned.
     * @param type $id -> entity type id
     * @param type $bd -> object to work with database querys
     */
    public function tableHist($id, $bd) {
        ?>
                
            <form method="GET">
                Verificar histórico:<br>
                <input type="radio" name="controlDia" value="ate">até ao dia<br>
                <input type="radio" name="controlDia" value="aPartir">a partir do dia<br>
                <input type="radio" name="controlDia" value="dia">no dia<br>
                <input type="text" id="datepicker" class="datepicker" name="data" placeholder="Introduza uma data">
                <input type="hidden" name="selData" value="true">
                <input type="hidden" name="estado" value="historico">
                <input type="hidden" name="ent_id" value="<?php echo $_REQUEST['ent_id']; ?>">
                <input type="submit" value="Apresentar histórico">
            </form>
        <table class="table">
            <thead>
                <th>Data de Início</th>
                <th>Data de Fim</th>
                <th>Nome Tipo de Entidade</th>
                <th>Propriedade</th>	
                <th>Tipo de Valor</th>
                <th>Estado da Propriedade</th>
                <!--<th>Estado Durante o Período</th>-->
                <th>Ação</th>
            </thead>
        <tbody>
        <?php
        
        if (isset($_REQUEST['data'])) {
            $data = $bd->userInputVal($_REQUEST['data']);
        }
        
        if (isset($_REQUEST["controlDia"]) && $_REQUEST["controlDia"] == "ate") {
            $resHE = $bd->runQuery("SELECT * FROM `hist_ent_type` WHERE ent_type_id=".$id." AND inactive_on<='".$data."' ORDER BY inactive_on DESC");
        }
        else if (isset($_REQUEST["controlDia"]) && $_REQUEST["controlDia"] == "aPartir") {
            $resHE = $bd->runQuery("SELECT * FROM `hist_ent_type` WHERE ent_type_id=".$id." AND inactive_on>='".$data."' ORDER BY inactive_on DESC");
        }
        else if (isset($_REQUEST["controlDia"]) && $_REQUEST["controlDia"] == "dia"){
            $resHE = $bd->runQuery("SELECT * FROM `hist_ent_type` WHERE ent_type_id=".$id." AND inactive_on < '".date("Y-m-d",(strtotime($data) + 86400))."' AND inactive_on >= '".$data."' ORDER BY inactive_on DESC");
        }
        else {              
            $resHE = $bd->runQuery("SELECT * FROM `hist_ent_type` WHERE ent_type_id=" . $id);
        }
        
        
        
        if ($resHE->num_rows < 1) {
            ?>
                <tr>
                    <td colspan="6">Não existe registo referente à entidade selecionada no histórico</td>
                    <td><?php goBack(); ?></td>
                </tr>
            <?php
        } else {
            while ($readHE = $resHE->fetch_assoc()) {
                //get properties from the history where the inactive field has an hour equal the ent_type selected
                $getPropsHist = $bd->runQuery("SELECT * FROM hist_property WHERE ent_type_id = ".$id." AND '".$readHE['inactive_on']."' > active_on AND '".$readHE['inactive_on']."'<= inactive_on");
                
                //selects a property from properties table where the updated value is smaller than the inactive from the ent_type selected
                $getProp = $bd->runQuery("SELECT * FROM property WHERE updated_on < '".$readHE['inactive_on']."' AND ent_type_id =".$readHE['ent_type_id']."");
                $conta = 0;
                $numlinhas = $getPropsHist->num_rows + $getProp->num_rows;
 ?>
                <tr>
                        <td rowspan="<?php echo $numlinhas ?>"><?php echo $readHE['active_on'] ?></td>
                        <td rowspan="<?php echo $numlinhas ?>"><?php echo $readHE['inactive_on'] ?></td>
                        <td rowspan="<?php echo $numlinhas ?>"><?php echo $readHE['name'] ?></td>                   
<?php
                        if($numlinhas == 0)
                        {
?>
                            <td colspan="3">Não existem propriedades associadas a este tipo de entidade.</td>
                            <td><a href="?estado=versionBack&histId=<?php echo $readHE['id'] ?>">Voltar para esta versão</a></td>
<?php
                        }
                        else{
                            $saveName = array();
                            //print properties from the hist table
                            while($readProp = $getPropsHist->fetch_assoc()){
?>
                                <td><?php echo $readProp['name']?></td>
                                <td><?php echo $readProp['value_type']?></td>
                                <td><?php if($readProp['state'] == "active"){echo "Ativo";}else{echo "Inativo";}?></td>
<?php
                                if($conta == 0)
                                {
?>
                                    <td rowspan="<?php echo $numlinhas ?>"><a href="?estado=versionBack&histId=<?php echo $readHE['id'] ?>">Voltar para esta versão</a></td>     
<?php
                                }
                                $conta++;
?>
                                </tr>
<?php
                            }
                            //Print properties from normal table
                            while($readProp = $getProp->fetch_assoc()){
?>
                                <td><?php echo $readProp['name']?></td>
                                <td><?php echo $readProp['value_type']?></td>
                                <td><?php if($readProp['state'] == "active"){echo "Ativo";}else{echo "Inativo";}?></td>

<?php
                                if($conta == 0)
                                {
?>
                                    <td rowspan="<?php echo $numlinhas ?>"><a href="?estado=versionBack&histId=<?php echo $readHE['id'] ?>">Voltar para esta versão</a></td>     
<?php
                                }
                                $conta++;
?>
                                </tr>
<?php
                            }
                        }
            }
        
    }
    ?>                                
        </tbody>
        </table>
        <?php
    }

    public function tablePresentHist($data,$bd){
        //echo "Qj";        
        ?>
                
                
        <table class="table">
            <thead>
                <th>Id</th>
                <th>Nome Tipo de Entidade</th>
                <th>Propriedade</th>	
                <th>Tipo de Valor</th>
                <th>Estado da Propriedade</th>
            </thead>
        <tbody>
        <?php
        
        $creatTempTable = "CREATE TEMPORARY TABLE temp_table (
        `id` int(10) unsigned NOT NULL,
        `name` varchar(128) NOT NULL,
        `state` enum('active','inactive') NOT NULL)";
        $creatTempTable = $bd->runQuery($creatTempTable);
                   

        $selecionaProp = "SELECT * FROM ent_type WHERE updated_on < '".$_REQUEST["data"]."' OR updated_on LIKE '".$_REQUEST["data"]."%'";
    echo $selecionaProp."<br>";
        $querEntTp = $bd->runQuery($selecionaProp);
         while($readEntTP = $querEntTp->fetch_assoc())
         {
             $bd->runQuery("INSERT INTO temp_table VALUES (".$readEntTP['id'].",'".$readEntTP['name']."','".$readEntTP['state']."')");
         }
         
         $selecionaHist = "SELECT * FROM hist_ent_type WHERE ('".$_REQUEST["data"]."' > active_on AND '".$_REQUEST["data"]."' < inactive_on) OR ((active_on LIKE '".$_REQUEST["data"]."%' AND inactive_on < '".$_REQUEST["data"]."') OR inactive_on LIKE '".$_REQUEST["data"]."%') GROUP BY ent_type_id ORDER BY inactive_on DESC";
    echo $selecionaHist."<br>";
        $querHist = $bd->runQuery($selecionaHist);
        while($readHist = $querHist->fetch_assoc())
        {
            $bd->runQuery("INSERT INTO temp_table VALUES (".$readHist['ent_type_id'].",'".$readHist['name']."','".$readHist['state']."')");
        }
       //get the properties
        $createTempProp = "CREATE TEMPORARY TABLE  temp_hist_property (
                        `id` INT UNSIGNED NOT NULL ,
                        `name` VARCHAR(128) NOT NULL,
                        `value_type` ENUM('text', 'bool', 'int', 'double', 'enum', 'ent_ref') NOT NULL COMMENT 'text, int, double, boolean, enum',
                        `ent_type_id` INT NULL,
                        `state` ENUM('active','inactive') NOT NULL)";
        $createTempProp = $bd->runQuery($createTempProp);     
        
        $selecionaProp = "SELECT * FROM property WHERE updated_on < '".$_REQUEST["data"]."' OR updated_on LIKE '".$_REQUEST["data"]."%'";
    echo $selecionaProp."<br>"; 
        $res_getProp = $bd->runQuery($selecionaProp);
         while($prop = $res_getProp->fetch_assoc()){
                $prop['ent_type_id'] == "" ? $entID = "NULL" : $entID =$prop['ent_type_id'];
                $bd->runQuery("INSERT INTO temp_hist_property VALUES (".$prop['id'].",'".$prop['name']."','".$prop['value_type']."',".$entID.",'".$prop['state']."')");
         }
        $selecionaHist = "SELECT * FROM hist_property WHERE ('".$_REQUEST["data"]."' > active_on AND '".$_REQUEST["data"]."' < inactive_on) OR ((active_on LIKE '".$_REQUEST["data"]."%' AND inactive_on < '".$_REQUEST["data"]."') OR inactive_on LIKE '".$_REQUEST["data"]."%') GROUP BY ent_type_id ORDER BY inactive_on DESC";
    echo $selecionaHist."<br>";
        $res_getPropHist = $bd->runQuery($selecionaHist);
         while($propHist = $res_getPropHist->fetch_assoc()){
             $propHist['ent_type_id'] == "" ? $entID = "NULL" : $entID =$propHist['ent_type_id'];
             $bd->runQuery("INSERT INTO temp_hist_property VALUES (".$propHist['property_id'].",'".$propHist['name']."','".$propHist['value_type']."',".$entID.",'".$propHist['state']."')");
         }
        
        $resHe = $bd->runQuery("SELECT * FROM temp_table GROUP BY id ORDER BY id ASC");
        if ($resHe->num_rows < 1) {
            ?>
                <tr>
                    <td colspan="7">Não existe registos para esta tabela no dia selecionado</td>
                </tr>
            <?php
        } else {
            
            while ($readHE = $resHe->fetch_assoc()) {
                $propPrint = $bd->runQuery("SELECT * FROM temp_hist_property WHERE ent_type_id=".$readHE['id']."");
                $numLines = $propPrint->num_rows;
?>
                    <tr>

                        <td rowspan="<?php echo $numLines ?>"><?php echo $readHE['id'] ?></td>  
                                <td rowspan="<?php echo $numLines ?>"><?php echo $readHE['name'] ?></td>
<?php                        
                        $count = 0;
                        while($propP = $propPrint->fetch_assoc()){
?>
<?php
?>
                                <td><?php echo $propP['name']; ?></td>
                                <td><?php echo $propP['value_type']; ?></td>
<?php
                            if($count == 0)
                            {
?>
                               <td rowspan="<?php echo $numLines; ?>"><?php if($readHE['state'] == 'active')
                                {
                                    echo "Ativo";
                                }else
                                {
                                    echo "Inativo";
                                }?></td>
<?php
                            }

                            $count ++;
                            ?></tr><?php
                        }
                        if($count == 0)
                        {
?>
                             <td colspan="2">Não existem propriedades associadas a este tipo de entidade</td>
                              <td><?php if($readHE['state'] == 'active')
                                {
                                    echo "Ativo";
                                }else
                                {
                                    echo "Inativo";
                                }?></td>
<?php 
                        }
?>
                      
                <?php
            }
            $bd->runQuery("DROP TEMPORARY TABLE temp_table");
            $bd->runQuery("DROP TEMPORARY TABLE temp_hist_property");
        }
        ?>                                
        </tbody>
        </table>
        <?php
    }
}
?>
