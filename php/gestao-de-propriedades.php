<?php
require_once("custom/php/common.php");

/**
 * Class that handle all the methods that are necessary to execute this component
 */
class PropertyManage
{
    private $db;            // Object from DB_Op that contains the access to the database
    private $capability;    // Wordpress's Capability for this component
    private $gereHist;

    /**
     * Constructor method
     */
    public function __construct(){
        $this->db = new Db_Op();
        $this->capability = "manage_properties";
        $this->gereHist = new PropHist();
        $this->executaScript();
    }

    /**
     * Main method that controls the capability of the current user to acces this component
     */
    public function executaScript()
    {
        // Check if any user is logged in
        if ( is_user_logged_in() )
        {
            // Check if the current user as the capability to use this component
            if(current_user_can($this->capability))
            {
                $this->verificaEstado();
                
            }
            else
            {
        ?>
            <html>
                <p>Não tem autorização para aceder a esta página</p>
            </html>
        <?php
            }
        }
        else
        {
        ?>
            <html>
                <p>Não tem sessão iniciada.</p>
                <p>Clique <a href="/login">aqui</a> para iniciar sessão.</p>
            </html>
        <?php
        }
    }

    /**
     * Method that controls the execution flow of this component
     */
    private function verificaEstado()
    {
        if (empty($_REQUEST["estado"]))
        {
    ?>
        <html>
            <p>Por favor escolha que tipo de propriedades quer gerir.</p>
            <ul>
                <li><a href="/gestao-de-propriedades?estado=entity">Entidade</a></li>
                <li><a href="/gestao-de-propriedades?estado=relation">Relação</a></li>
            </ul>
        </html>
    <?php
        }
        elseif ($_REQUEST["estado"] === "relation")
        {
            $this->estadoEntityRelation("relation");
        }
        elseif ($_REQUEST["estado"] === "entity")
        {
            $this->estadoEntityRelation("entity");
        }
        elseif ($_REQUEST["estado"] === "inserir")
        {
            if($this->validarDados())
            {
                 $this->estadoInserir();
            }
        }
        elseif($_REQUEST['estado'] =='editar')
        {
            $this->estadoEditar();
        }
        elseif($_REQUEST['estado'] =='update')
        {
            if($this->validaEdicoes())
            {
                 $this->estadoUpdate();
            }
           
        }
        elseif($_REQUEST['estado'] =='historico')
        {
             $this->gereHist->estadoHistorico($this->db);
        }
        elseif($_REQUEST['estado'] =='voltar')
        {
            if(!$this->validaEdicoes())
            {
                $this->gereHist->estadoVoltar($this->db);
            }
            else {
?>
                <p>Não pode efetuar a atualização pretendida uma vez que já existem entidades/relações com valores atribuídos para essa propriedade.</p>
<?php
                goBack();
            }
             
        }
        elseif($_REQUEST['estado'] == 'ativar' || $_REQUEST['estado'] == 'desativar')
        {
            $this->estadoAtivarDesativar();		
        }
        elseif($_REQUEST['estado'] == 'introducao')
        {
            $this->apresentaForm();		
        }
        elseif($_REQUEST['estado'] == 'conclusao')
        {
            $this->estadoConclusao();		
        }
    }
    
    /**
     * Method that checks if there are any enitity/relation according with the selected before
     * @param string $tipo ("relation" if we want to check properties's relation, "entity" for entities's properties)
     * @return boolean (true if there are properties otherwise it will return false)
     */
    private function existeTipo($tipo)
    {
        if ($tipo === "relation")
        {
            if ($this->db->runQuery("SELECT * FROM rel_type")->num_rows == 0) {
?>
                <p>Não pode adicionar/gerir propriedades uma vez que ainda não existem tipos de relações.</p>
                <p>Clique em <a href="/gestao-de-relacoes">Gestão de relações</a> para criar um novo tipo de relação.</p>
<?php                
                return false;
            }
            return true;
        }
        else
        {
            if ($this->db->runQuery("SELECT * FROM ent_type")->num_rows == 0) {
?>
                <p>Não pode adicionar/gerir propriedades uma vez que ainda não existem tipos de entidades.</p>
                <p>Clique em <a href="/gestao-de-entidades">Gestão de entidades</a> para criar um novo tipo de entidade.</p>
<?php               
                return false;
            }
            return true;
        }
    }

    /**
     * This method is responsible to control the flow execution of the state entity and relation
     * @param string $tipo ("relation" if we selected relation in first state, "entity" if we selected entity in first state)
     */
    private function estadoEntityRelation($tipo)
    {
        if($this->existeTipo($tipo))
        {
            $this->apresentaTabela($tipo);

        }
    }

    /**
     * Method that builds and print the table of properties that already exists for the property type selected in the first state
     * @param type $tipo ("relation" if we selected relation in first state, "entity" if we selected entity in first state)
     */
    private function apresentaTabela($tipo)
    {
    ?>
        <form method="GET">
            Verificar propriedades existentes no dia : 
            <input type="text" class="datepicker" id="datepicker" name="data" placeholder="Introduza uma data"> 
            <input type="hidden" name="estado" value="historico">
            <input type="hidden" name="histAll" value="true">
            <input type="hidden" name="tipo" value="<?php echo $tipo; ?>">
            <input type="submit" value="Apresentar propriedades">
        </form>
        <table class="table">
            <thead>
                <tr>
                <?php
                    if ($tipo === "entity")
                    {
                ?>
                    <th>Entidade</th>
                <?php
                    }
                    else
                    {
                ?>
                    <th>Relação</th>
                <?php
                    }
                ?>
                    <th>ID</th>
                    <th>Propriedade</th>
                    <th>Tipo de valor</th>
                    <th>Nome do campo no formulário</th>
                    <th>Tipo do campo no formulário</th>
                    <th>Tipo de unidade</th>
                    <th>Ordem do campo no formulário</th>
                    <th>Tamanho do campo no formulário</th>
                    <th>Obrigatório</th>
                    <th>Estado</th>
                    <th>Ação sobre a propriedade</th>
                <?php
                    if ($tipo === "entity")
                    {
                ?>
                    <th>Ação sobre a entidade</th>
                <?php
                    }
                    else
                    {
                ?>
                    <th>Ação sobre a relação</th>
                <?php
                    }
                ?>   
                </tr>
            </thead>
            <tbody>
                <?php
                    if ($tipo === "entity")
                    {
                        $selecionaEntOrRel = "SELECT name, id FROM ent_type";
                        $resultSelEntOrRel = $this->db->runQuery($selecionaEntOrRel);
                    }
                    else
                    {
                        $selecionaEntOrRel = "SELECT name, id FROM rel_type";
                        $resultSelEntOrRel = $this->db->runQuery($selecionaEntOrRel);
                    }
                    while ($resEntRel = $resultSelEntOrRel->fetch_assoc())
                    {
                        $idEntRel = $resEntRel["id"];
                        if ($tipo === "entity")
                        {
                            $nome = $resEntRel["name"];
                            $selecionaProp = "SELECT * FROM property WHERE ent_type_id =".$idEntRel;
                        }
                        else
                        {
                            $nome = $resEntRel["name"];
                            $selecionaProp = "SELECT * FROM property WHERE rel_type_id =".$idEntRel;
                        }
                        $resultSeleciona = $this->db->runQuery($selecionaProp);
                        $numLinhas = $resultSeleciona->num_rows;
                ?>
                <tr>
                        <td rowspan="<?php echo $numLinhas; ?>"><?php echo $nome; ?></td>
                <?php
                        if ($resultSeleciona->num_rows === 0) {
                            if ($tipo === "entity") {
?>
                                <td colspan="11">Esta entidade ainda não possui quaisquer propriedades</td>
                                <td rowspan="<?php echo $numLinhas; ?>">
                                    <a href="gestao-de-propriedade?estado=introducao&ent_id=<?php echo $idEntRel;?>">[Inserir propriedades]</a>
                                </td> 
<?php
                            }
                            else {
?>
                                <td colspan="11">Esta relação ainda não possui quaisquer propriedades</td>
                                <td rowspan="<?php echo $numLinhas; ?>">
                                    <a href="gestao-de-propriedade?estado=introducao&rel_id=<?php echo $idEntRel;?>">[Inserir propriedades]</a>
                                </td> 
<?php
                            }
                        }
                        $controlo = 1;
                        while($arraySelec = $resultSeleciona->fetch_assoc())
                        {
                            if ($controlo === $numLinhas + 1) {
                                $controlo = 1;
                            }
                ?>
                            <td><?php echo $arraySelec["id"]; ?></td>
                            <td><?php echo $arraySelec["name"]; ?></td>
                            <td><?php echo $arraySelec["value_type"]; ?></td>
                            <td><?php echo $arraySelec["form_field_name"]; ?></td>
                            <td><?php echo $arraySelec["form_field_type"]; ?></td>
                            <td>
                <?php
                            if (empty($arraySelec["unit_type_id"]))
                            {
                                echo "-";
                            }
                            else
                            {
                                $queryUn = "SELECT name FROM prop_unit_type WHERE id =".$arraySelec["unit_type_id"];
                                echo $this->db->runQuery($queryUn)->fetch_assoc()["name"];
                            }
                ?>
                            </td>
                            <td><?php echo $arraySelec["form_field_order"];?></td>
                            <td><?php echo $arraySelec["form_field_size"]; ?></td>
                            <td>
                <?php
                            if ($arraySelec["mandatory"] == 1)
                            {
                                echo "sim";
                            }
                            else
                            {
                                echo " não";
                            }
                 ?>
                            </td>
                            
                <?php
                            if ($arraySelec["state"] === "active")
                            {
                ?>
                                <td>Ativo</td>
                                <td>  
                                    <a href="gestao-de-propriedade?estado=desativar&prop_id=<?php echo $arraySelec['id'];?>">[Desativar]</a>
                                    <a href="?estado=historico&id=<?php echo $arraySelec["id"];?>">[Histórico]</a>
                                </td>
                <?php
                            }
                            else
                            {
                ?>
                                <td>Inativo</td>
                                <td>
                                    <a href="gestao-de-propriedade?estado=ativar&prop_id=<?php echo $arraySelec['id'];?>">[Ativar]</a>
                                    <a href="?estado=historico&id=<?php echo $arraySelec["id"];?>">[Histórico]</a>
                                </td>
<?php
                            }
                
                            if ($tipo === "entity")
                            {
                                if ($controlo === 1) {
?>
                                <td rowspan="<?php echo $numLinhas; ?>">
                                    <a href="gestao-de-propriedade?estado=editar&ent_id=<?php echo $arraySelec['ent_type_id'];?>">[Editar propriedades]</a>
                                    <a href="gestao-de-propriedade?estado=introducao&ent_id=<?php echo $arraySelec['ent_type_id'];?>">[Inserir propriedades]</a>
                                </td>  
<?php
                                }
                            }
                            else
                            {
                                if ($controlo === 1) {
?>
                                <td rowspan="<?php echo $numLinhas; ?>">
                                    <a href="gestao-de-propriedade?estado=editar&rel_id=<?php echo $arraySelec['rel_type_id'];?>">[Editar propriedades]</a>
                                    <a href="gestao-de-propriedade?estado=introducao&rel_id=<?php echo $arraySelec['rel_type_id'];?>">[Inserir propriedades]</a>
                                </td> 
<?php
                                }
                            }
?>
                            </td>
                </tr>
                <?php
                            $controlo++;
                        }
                    }
                ?>
            </tbody>
        </table>
    <?php
    }
    
    /**
     * Method that builds and print the form that user uses to add properties to the type selected in the first state
     */
    private function apresentaForm()
    {
        $existeEntRel = true;
        if(isset($_REQUEST['ent_id']))
        {
            $verificaEntidades = "SELECT * FROM ent_type";
            $numEnt = $this->db->runQuery($verificaEntidades)->num_rows;
            if($numEnt === 0)
            {
            ?>
                <p>Não poderá inserir propriedades uma vez que ainda não foram criadas quaisquer entidades</p>
            <?php
                $existeEntRel = false;
            }

        }
        else
        {
            $verificaRelacoes = "SELECT * FROM rel_type";
            $numEnt = $this->db->runQuery($verificaRelacoes)->num_rows;
            if($numEnt === 0)
            {
            ?>
                <p>Não poderá inserir propriedades uma vez que ainda não foram criadas quaisquer relações</p>
            <?php
                $existeEntRel = false;
            }

        }   
        if ($existeEntRel)
        {
            if(isset($_REQUEST['ent_id']))
            {
                $nomeEnt = $this->db->getEntityName($_REQUEST['ent_id'])
?>
            <h3>Gestão de propriedades - Entidade <?php echo $nomeEnt;?> - introdução</h3>
<?php
            }
            else {
                $queryEnt = "SELECT name FROM rel_type WHERE id = ".$_REQUEST['rel_id'];
                $nomeRel = $this->db->runQuery($queryEnt)->fetch_assoc()["name"];
?>
            <h3>Gestão de propriedades - Relação <?php echo $nomeRel;?> - introdução</h3>
<?php 
            }
?>

            <form id="insertProp" method="POST">
                <label>Nome da Propriedade:</label><br>
                    <input id="nome" type="text" name="nome">
                    <br><label class="error" for="nome"></label>
                <br>
                <label>Tipo de valor:</label><br>
<?php
                $field = 'value_type';
                $table = 'property';
                $array =$this->db->getEnumValues($table, $field);
                foreach($array as $values)
                {
?>
                    <input id="tipoValor" type="radio" name="tipoValor" value="<?php echo $values;?>"><?php echo $values;?><br>
<?php
                }
?>
                <label class="error" for="tipoValor"></label>
                <br>
                <label>Tipo do campo do formulário</label><br>
<?php
                $field = 'form_field_type';
                $table = 'property';
                $array = $this->db->getEnumValues($table, $field);
                foreach($array as $values)
                {
?>
                    <input id="tipoCampo" type="radio" name="tipoCampo" value="<?php echo $values;?>"><?php echo $values;?><br>
<?php
                }
?>
                <label class="error" for="tipoCampo"></label>
                <br>
                <label>Tipo de unidade</label><br>
                <select id="tipoUnidade" name="tipoUnidade">
                    <option value="NULL"></option>';
                        <?php
                            $selecionaTipoUnidade = "SELECT name, id FROM prop_unit_type";
                            $result = $this->db->runQuery($selecionaTipoUnidade);
                            while($guardaTipoUnidade = $result->fetch_assoc())
                            {
?>
                                <option value="<?php echo $guardaTipoUnidade["id"];?>"><?php echo $guardaTipoUnidade["name"]?></option>
<?php
                            }
                        ?>
                </select><br><br>
                <label class="error" for="tipoUnidade"></label>
                <label>Ordem do campo no formulário</label><br>
                <input id="ordem" type="text" name="ordem" min="1"><br>
                <label class="error" for="ordem"></label><br>
                <label>Tamanho do campo no formulário</label><br>
                <input id="size" type="text" name="tamanho"><br>
                <label id="errTam"></label><br>
                <label>Obrigatório</label><br>
                <input id="obrigatorio" type="radio" name="obrigatorio" value="1">Sim
                <br>
                <input id="obrigatorio" type="radio" name="obrigatorio" value="0">Não
                <br>
                <label class="error" for="obrigatorio"></label><br>
<?php
                if (isset($_REQUEST['ent_id']))
                {
?>
                    <label>Entidade referenciada por esta propriedade</label><br>
                    <select id="entidadeReferenciada" name="entidadeReferenciada">
                    <option value="NULL"></option>
<?php
                    $selecionaEntidades= "SELECT id, name FROM ent_type";
                    $result = $this->db->runQuery($selecionaEntidades);
                    while($guardaEntidade = $result->fetch_assoc())
                    {
?>
                        <option value="<?php echo $guardaEntidade["id"];?>"><?php echo $guardaEntidade["name"];?></option>
<?php
                    }
?>
                    </select><br><br>
<?php
                }
?>
                <label class="error" for="entidadeReferenciada"></label>
<?php
                if(isset($_REQUEST['ent_id']))
                {
?>
                    <input type ="hidden" name="entidadePertence" value="<?php echo $_REQUEST['ent_id'];?>">
<?php
                }
                else {
?>
                    <input type ="hidden" name="relacaoPertence" value="<?php echo $_REQUEST['rel_id'];?>">
<?php
                }
                if (empty($_REQUEST['maisProp'])) {
?>
                    <input type ="hidden" name="primeiraVez" value="true">
<?php                    
                }
?>
                <input type="hidden" name="estado" value="inserir"><br>
                <input type="submit" value="Inserir propriedade">
            </form>
<?php
            }
    }
    
    /**
     * This method finishe the introductionoff new properties
     */
    private function estadoConclusao () {
        $this->db->getMysqli()->begin_transaction();
        if (!empty($_REQUEST["ent_id"]) && $this->gereHist->createNewEnt($_REQUEST["ent_id"], $this->db, $_SESSION["data"])) {
            $this->db->getMysqli()->commit();
?>
            <p>Inseriu todas as propriedades com sucesso.</p>
            <p>Clique em <a href="/gestao-de-propriedades/">Continuar</a> para avançar.</p>
<?php
        }
        else if (!empty($_REQUEST["rel_id"]) && $this->gereHist->createNewRel($_REQUEST["rel_id"], $this->db, $_SESSION["data"])) {
            $this->db->getMysqli()->commit();
?>
            <p>Inseriu todas as propriedades com sucesso.</p>
            <p>Clique em <a href="/gestao-de-propriedades/">Continuar</a> para avançar.</p>
<?php
        }
        else {
            $this->db->getMysqli()->rollback();
            $this->rollbackNewProp ();
?>
            <p>Devido a um erro não foi possível inserir as propriedades pretendidas.</p>
<?php
            goBack();
        }
    }

    /**
     * This method inserts the new property in the database
     */
    private function estadoInserir()
    {
?>
        <h3>Gestão de propriedades - inserção</h3>
<?php
        if(!empty($_REQUEST["entidadePertence"]))
        {
            $entRelQuery = 'SELECT name FROM ent_type WHERE id = '.$_REQUEST["entidadePertence"];
        }
        else
        {
            $entRelQuery = "SELECT name FROM rel_type AS rel WHERE rel.id = ".$_REQUEST["relacaoPertence"];
        }
        $entRelResult = $this->db->runQuery($entRelQuery);
        $entRelArray = $entRelResult->fetch_assoc();
        // contrução do form_field_name
        // obtém-se o nome da entidade a que corresponde a propriedade que queremos introduzir
        $entRel = $entRelArray["name"];
	// Obtemos as suas 3 primeiras letras
	$entRel = substr($entRel, 0 , 3);
	$traco = '-';
	$idProp = '';
	// Garantimos que não há SQL injection através do campo nome
	$nome = $this->db->getMysqli()->real_escape_string($_REQUEST["nome"]);
	// Substituimos todos os carateres por carateres ASCII
	$nomeField = preg_replace('/[^a-z0-9_ ]/i', '', $nome);
	// Substituimos todos pos espaços por underscore
	$nomeField = str_replace(' ', '_', $nomeField);
	$form_field_name = $entRel.$traco.$idProp.$traco.$nomeField;
        if (isset($_REQUEST["primeiraVez"])) {
            // Inicia uma tansação uma vez que, devido ao id no campo form_field_name vamos ter de atualizar esse atributo, após a inserção
            $this->db->getMysqli()->autocommit(false);
            $this->db->getMysqli()->begin_transaction();
            $_SESSION['newProp'] = array();
            $_SESSION["data"] = date("Y-m-d H:i:s",time());
        }
	// De modo a evitar problemas na execução da query quando o campo form_field_size é NULL, executamos duas queries diferentes, uma sem esse campo e outra com esse campo
	$queryInsere = 'INSERT INTO `property`(`id`, `name`,';
        if(!empty($_REQUEST["entidadePertence"]))
        {
           $queryInsere .=  '`ent_type_id`,';
        }
        else
        {
            $queryInsere .=  '`rel_type_id`,';
        }
        $queryInsere .=  ' `value_type`, `form_field_name`, `form_field_type`, `unit_type_id`,';
        if(!empty($_REQUEST["tamanho"]))
	{
            $queryInsere .= '`form_field_size`, ';
        }
        $queryInsere .=  '`form_field_order`, `mandatory`, `state`';
        if (!empty($_REQUEST["entidadeReferenciada"]))
        {
            $queryInsere .= ', `fk_ent_type_id`';
        }
        $queryInsere .= ', `updated_on`) VALUES (NULL,\''.$this->db->getMysqli()->real_escape_string($_REQUEST["nome"]).'\',';
        if(!empty($_REQUEST["entidadePertence"]))
        {
           $queryInsere .= $_REQUEST["entidadePertence"];
        }
        else
        {
            $queryInsere .=  $_REQUEST["relacaoPertence"];
        }
        $queryInsere .= ',\''.$_REQUEST["tipoValor"].'\',\''.$form_field_name.'\',\''.$_REQUEST["tipoCampo"].'\','.$_REQUEST["tipoUnidade"];
        if(!empty($_REQUEST["tamanho"]))
	{
            $queryInsere .= ',"'.$this->db->getMysqli()->real_escape_string($_REQUEST["tamanho"]).'"';
	}
        $queryInsere .= ','.$this->db->getMysqli()->real_escape_string($_REQUEST["ordem"]).','.$_REQUEST["obrigatorio"].',"active"';
	if (!empty($_REQUEST["entidadeReferenciada"]))
        {
            $queryInsere .=  ','.$_REQUEST["entidadeReferenciada"];
        }
        $queryInsere .=  ', "'.$_SESSION["data"].'")';
        $insere = $this->db->runQuery($queryInsere);
	if(!$insere)
	{
            $this->db->getMysqli()->rollback();
            $this->rollbackNewProp ();
?>
            <p>Não foi possível inserir uma nova propriedade.</p>
<?php
            goBack();
	}
	else
	{
            //obtem o último id que foi introduzido na BD
            $id = $this->db->getMysqli()->insert_id;
            // constroi novamente o form_field_name agora com o id do tuplo que acabou de ser introduzido
            $form_field_name = $entRel.$traco.$id.$traco.$nomeField;
            // atualiza esse atributo
            $atualiza = "UPDATE property SET updated_on = '".$_SESSION['data']."', form_field_name = '".$form_field_name."' WHERE property.id = ".$id;
            $atualiza = $this->db->runQuery($atualiza);
            if(!$atualiza)
            {
                $this->db->getMysqli()->rollback();
                $this->rollbackNewProp ();
?>
                <p>Não foi possível inserir uma nova propriedade.</p>
<?php
                goBack();
            }
            else
            {
                if (!empty($_REQUEST["entidadePertence"])) {
                    $this->db->getMysqli()->commit();
                    array_push($_SESSION['newProp'], $id);
?>
                    <p>Inseriu os dados de nova propriedade com sucesso.</p>
                    <p>Clique em <a href="/gestao-de-propriedades/?estado=introducao&ent_id=<?php echo $_REQUEST["entidadePertence"];?>&maisProp=true">Adicionar mais Propriedade</a> para continuar a introduzir propriedades nesta entidade.</p>
                    <p>Ou clique em <a href="/gestao-de-propriedades/?estado=conclusao&ent_id=<?php echo $_REQUEST["entidadePertence"];?>">Concluir</a> para terminar o processo de inserção de propriedades.</p>
<?php
                }
                else if (!empty($_REQUEST["relacaoPertence"])) {
                    $this->db->getMysqli()->commit();
                    array_push($_SESSION['newProp'], $id);
?>
                    <p>Inseriu os dados de nova propriedade com sucesso.</p>
                    <p>Clique em <a href="/gestao-de-propriedades/?estado=introducao&rel_id=<?php echo $_REQUEST["relacaoPertence"];?>&maisProp=true">Adicionar mais Propriedade</a> para continuar a introduzir propriedades nesta relação.</p>
                    <p>Ou clique em <a href="/gestao-de-propriedades/?estado=conclusao&rel_id=<?php echo $_REQUEST["relacaoPertence"];?>">Concluir</a> para terminar o processo de inserção de propriedades.</p>
<?php
                }
                else {
                    $this->db->getMysqli()->rollback();
                    $this->rollbackNewProp ();
?>
                    <p>Não foi possível inserir uma nova propriedade.</p>
<?php
                    goBack();
                }
                
            }
	}

    }
    
    /**
     * This method rollbacks the already inserted properties if anything goes wrong
     */
    private function rollbackNewProp () {
        foreach($_SESSION['newProp'] as $id) {
            $removeProp = "DELETE FROM property WHERE id = ".$id;
            $this->db->runQuery($removeProp);
        }
    }

    /**
     * This method does the PHP-side validation of the form
     * @return boolean (true if all the data is in correct format)
     */
    private function validarDados()
    {
        if (empty($_REQUEST["nome"]))
        {
?>
            <p>Por favor introduza o nome da propriedade.</p><br>
<?php
            goBack();
            return false;
        }
        if (empty($_REQUEST["tipoValor"]))
        {
?>
            <p>Por favor selecione um tipo de valor para a sua entidade.</p><br>
<?php
            goBack();
            return false;
        }
        if (empty($_REQUEST["tipoCampo"]))
        {
?>
            <p>Por favor selecione um tipo do campo do formulário.</p><br>
<?php
            goBack();
            return false;
        }
        if (empty($_REQUEST["obrigatorio"]) && strlen($_REQUEST['obrigatorio']) == 0)
        {
?>
            <p>Por favor indique se esta propriedade deve ou não ser obrigatória.</p><br>
<?php
            goBack();;
            return false;
        }
        if(!is_numeric($_REQUEST["ordem"]) || empty($_REQUEST["ordem"]))
	{
?>
            <p>ERRO! O valor introduzido no campo Ordem do campo no formulário não é numérico!</p><br>
<?php
            goBack();
            return false;
	}
	else if($_REQUEST["ordem"] < 1)
	{
?>
            <p>ERRO! O valor introduzido no campo Ordem do campo no formulário deve ser superior a 0!</p><br>
<?php
            goBack();
            return false;
	}
	if(($_REQUEST["tipoCampo"] === "text") && (!is_numeric($_REQUEST["tamanho"]) || empty($_REQUEST["tamanho"])))
	{
?>
            <p>ERRO! O campo Tamanho do campo no formulário deve ser preenchido com valores numéricos
                uma vez que indicou que o Tipo do campo do formulário era text</p><br>
<?php
            goBack();
            return false;
	}
        // preg_match serve para verificar se o valor introduzido está no formato aaxbb onde aa e bb são números de 0 a 9
	if(($_REQUEST["tipoCampo"] === "textbox") && ((preg_match("/[0-9]{2}x[0-9]{2}/", $_REQUEST["tamanho"]) === 0) || empty($_REQUEST["tamanho"])))
	{
?>
            <p>ERRO! O campo Tamanho do campo no formulário deve ser preenchido com o seguinte formato
                aaxbb em que aa é o número de colunas e bb o número de linhas da caixa de texto</p><br>
<?php
            goBack();
            return false;
        }
	return true;
    }
    
    /**
     * This method checks if the user did any changes in the state editar
     * @return boolean  
     */
    private function checkforChanges ($propId) {
        
        $getProp = "SELECT * FROM property WHERE id = ".$propId;
        $getProp = $this->db->runQuery($getProp)->fetch_assoc();
        if ($_REQUEST['nome_'.$propId] != $getProp["name"]) {
            return true;
        }
        else if ($_REQUEST['tipoValor_'.$propId] != $getProp["value_type"]) {
            return true;
        }
        else if ($_REQUEST['tipoCampo_'.$propId] != $getProp["form_field_type"]) {
            return true;
        }
        else  if ((empty($getProp["unit_type"]) && $_REQUEST['tipoUnidade_'.$propId] != 'NULL') || (isset($getProp["unit_type"]) && $_REQUEST['tipoUnidade_'.$propId] != $getProp["unit_type"])) {
            return true;
        }
        else if ($_REQUEST['ordem_'.$propId] != $getProp["form_field_order"]) {
            return true;
        }
        else if ($_REQUEST['tamanho_'.$propId] != $getProp["form_field_size"]) {
            return true;
        }
        else if ($_REQUEST['obrigatorio_'.$propId] != $getProp["mandatory"]) {
            return true;
        }
        else if (isset($_REQUEST['entidadeReferenciada_'.$propId]) && ((empty($getProp["fk_ent_type_id"]) && $_REQUEST['entidadeReferenciada_'.$propId] != 'NULL') || (isset($getProp["fk_ent_type_id"]) && $_REQUEST['entidadeReferenciada_'.$propId] != $getProp["fk_ent_type_id"]))) {
            return true;
        }
        else {
            return false;
        }
    }
    
    /**
     * This method validates if we could or not update the selected property with the values that are field in
     * @return boolean
     */
    private function validaEdicoes() {
        if (isset($_REQUEST["rel_id"])) {
            $queryProp = "SELECT * FROM property WHERE rel_type_id = ".$_REQUEST["rel_id"];
        }
        else {
            $queryProp = "SELECT * FROM property WHERE ent_type_id = ".$_REQUEST["ent_id"];
        }
        $queryProp = $this->db->runQuery($queryProp);
        $changes = array();
        while ($prop = $queryProp->fetch_assoc()) {
            if (empty($_REQUEST["nome_".$prop['id']]))
            {
?>
                <p>Por favor introduza o nome da propriedade.</p><br>
<?php
                goBack();
                return false;
            }
            if (empty($_REQUEST["tipoValor_".$prop['id']]))
            {
?>
                <p>Por favor selecione um tipo de valor para a sua entidade.</p><br>
<?php
                goBack();
                return false;
            }
            if (empty($_REQUEST["tipoCampo_".$prop['id']]))
            {
?>
                <p>Por favor selecione um tipo do campo do formulário.</p><br>
<?php
                goBack();
                return false;
            }
            if (empty($_REQUEST["obrigatorio_".$prop['id']]) && strlen($_REQUEST['obrigatorio_'.$prop['id']]) == 0)
            {
?>
                <p>Por favor indique se esta propriedade deve ou não ser obrigatória.</p><br>
<?php
                goBack();;
                return false;
            }
            if(!is_numeric($_REQUEST["ordem_".$prop['id']]) || empty($_REQUEST["ordem_".$prop['id']]))
            {
?>
                <p>ERRO! O valor introduzido no campo Ordem do campo no formulário não é numérico!</p><br>
<?php
                goBack();
                return false;
            }
            else if($_REQUEST["ordem_".$prop['id']] < 1)
            {
?>
                <p>ERRO! O valor introduzido no campo Ordem do campo no formulário deve ser superior a 0!</p><br>
<?php
                goBack();
                return false;
            }
            if(($_REQUEST["tipoCampo_".$prop['id']] === "text") && (!is_numeric($_REQUEST["tamanho_".$prop['id']]) || empty($_REQUEST["tamanho_".$prop['id']])))
            {
?>
                <p>ERRO! O campo Tamanho do campo no formulário deve ser preenchido com valores numéricos
                    uma vez que indicou que o Tipo do campo do formulário era text</p><br>
<?php
                goBack();
                return false;
            }
            // preg_match serve para verificar se o valor introduzido está no formato aaxbb onde aa e bb são números de 0 a 9
            if(($_REQUEST["tipoCampo_".$prop['id']] === "textbox") && ((preg_match("/[0-9]{2}x[0-9]{2}/", $_REQUEST["tamanho_".$prop['id']]) === 0) || empty($_REQUEST["tamanho_".$prop['id']])))
            {
?>
                <p>ERRO! O campo Tamanho do campo no formulário deve ser preenchido com o seguinte formato
                    aaxbb em que aa é o número de colunas e bb o número de linhas da caixa de texto</p><br>
<?php
                goBack();
                return false;
            }
            if (!$this->checkforChanges($prop['id'])) {
              array_push($changes,false);
            }
            else {
              array_push($changes,true);
            }
        }
        foreach ($changes as $key => $value) {
            if ($value == true) {
                return true;   
            }
        }

?>
            <p>Não pode efetuar a atualização pretendida uma vez que não foram efetuadas quaisquer mudanças às propriedades.</p>
<?php
            goBack();
        return false;
        
    }
    
    /**
     * This method controls the flow of the state ativar and desativar that is responsable 
     * to ativate and desactivate the select property on the table presented in states entity and relation
     */
    private function estadoAtivarDesativar() {
        $data = date("Y-m-d H:i:s",time());
        $avanca = false;
        $querySelNome = "SELECT name FROM property WHERE id = ".$_REQUEST['prop_id'];
        if ($this->gereHist->atualizaHistorico($this->db,$data,$_REQUEST['prop_id'],true) == false) {
?>
            <p>Não foi possível desativar/ativar a propriedade pretendida.</p>
<?php 
            goBack();
        }
        else {
            $queryUpdate = "UPDATE property SET state=";
            if ($_REQUEST["estado"] === "desativar") {
                $queryUpdate .= "'inactive'";
                $estado = "desativada";
                $avanca = true;
            }
            else {
                $queryUpdate .= "'active'";
                $estado = "ativada";
                $avanca = true;

            }
            if ($avanca) {
                $queryUpdate .= ",updated_on ='".$data."' WHERE id =".$_REQUEST['prop_id'];
                $queryUpdate= $this->db->runQuery($queryUpdate);
                if ($queryUpdate) {
                    $nome = $this->db->runQuery($querySelNome)->fetch_assoc()["name"];
                    $this->db->getMysqli()->commit();
?>
                    <p>A propriedade <?php echo $nome ?> foi <?php echo $estado ?></p>
                    <br>
                    <p>Clique em <a href="/gestao-de-propriedades"/>Continuar</a> para avançar</p>
<?php   
                }
                else {
                    $this->db->getMysqli()->rollback;
                }
            }
        }
        
    }

    
    /**
     * This method presents the form that users must fill to update properties.
     * This form is pre-filled with the values that already exists in DB
     */
    private function estadoEditar() {
?>
        <h3> Gestão de propriedades - Edição </h3>
        <form id="editProp" method="POST">
<?php
        if (isset($_REQUEST["rel_id"])) {
            $queryProp = "SELECT * FROM property WHERE rel_type_id = ".$_REQUEST["rel_id"];
        }
        else {
            $queryProp = "SELECT * FROM property WHERE ent_type_id = ".$_REQUEST["ent_id"];
        }
        $queryProp = $this->db->runQuery($queryProp);
        while ($prop = $queryProp->fetch_assoc()) {
            if(is_null($prop["ent_type_id"]))
            {
                $tipo = "relation";
                $rel_type_id = $prop["rel_type_id"];
                $queryRel = "SELECT * FROM rel_type WHERE id = ".$rel_type_id;
                $nomeRelEnt = $this->db->runQuery($queryRel)->fetch_assoc()["name"];
            }
            else
            {
                $tipo = "entity";
                $ent_type_id = $prop["ent_type_id"];
                $queryEnt = "SELECT name FROM ent_type WHERE id = ".$ent_type_id;
                $nomeRelEnt = $this->db->runQuery($queryEnt)->fetch_assoc()["name"];
                $fk_ent_type_id = $prop["fk_ent_type_id"];
                if (is_null($fk_ent_type_id))
                {
                    $fk_ent_type_id = 0;
                }
                $queryEntRef = "SELECT name FROM ent_type WHERE id = ".$fk_ent_type_id;
                $nomeEntRef = $this->db->runQuery($queryEntRef)->fetch_assoc()["name"];
            }
            $nome = $prop["name"];
            $value_type = $prop["value_type"];
            $form_field_type = $prop["form_field_type"];
            $unit_type_id = $prop["unit_type_id"];
            if (is_null($unit_type_id))
            {
                $unit_type_id = 0;
            }
            $form_field_order = $prop["form_field_order"];
            $form_field_size = $prop["form_field_size"];

            $queryUnit = "SELECT name FROM prop_unit_type WHERE id = ".$unit_type_id;
            $unit = $this->db->runQuery($queryUnit)->fetch_assoc()["name"];

            $mandatory = $prop["mandatory"];
?>
            <h3> Propriedade <?php echo $nome?> - Edição </h3>
                <label>Nome da Propriedade:</label><br>
                    <input id="nome" type="text" name="nome_<?php echo $prop['id'];?>" value="<?php echo $nome?>">
                <br><label class="error" for="nome_<?php echo $prop['id'];?>"></label>
                <br>
                <label>Tipo de valor:</label><br>
<?php
                    $field = 'value_type';
                    $table = 'property';
                    $array =$this->db->getEnumValues($table, $field);                    
                    foreach($array as $values)
                    {
                        if ($values === $value_type)
                        {
?>
                            <input id="tipoValor" type="radio" name="tipoValor_<?php echo $prop['id'];?>" value="<?php echo $values;?>" checked="checked"><?php echo $values;?><br>
<?php
                        }
                        else
                        {
?>
                            <input id="tipoValor" type="radio" name="tipoValor_<?php echo $prop['id'];?>" value="<?php echo $values;?>"><?php echo $values;?><br>
<?php
                        }                      
                    }
?>
                    <label class="error" for="tipoValor_<?php echo $prop['id'];?>"></label>
                    <br>
                    <label>Tipo do campo do formulário</label><br>
                    <?php
                        $field = 'form_field_type';
                        $table = 'property';
                        $array = $this->db->getEnumValues($table, $field);
                        foreach($array as $values)
                        {
                            if ($values === $form_field_type)
                            {
?>
                                <input id="formType" type="radio" name="tipoCampo_<?php echo $prop['id'];?>" value="<?php echo $values;?>" checked="checked"><?php echo $values;?><br>
<?php
                            }
                            else
                            {
?>
                                <input id="formType" type="radio" name="tipoCampo_<?php echo $prop['id'];?>" value="<?php echo $values;?>"><?php echo $values;?><br>
<?php
                            }
                        }
?>
            <label class="error" for="tipoCampo_<?php echo $prop['id'];?>"></label>
            <br>
            <label>Tipo de unidade</label><br>
            <select id="tipoUnidade" name="tipoUnidade_<?php echo $prop['id'];?>">
                <option value="NULL"></option>';
                    <?php
                        $selecionaTipoUnidade = "SELECT name, id FROM prop_unit_type";
                        $result = $this->db->runQuery($selecionaTipoUnidade);
                        while($guardaTipoUnidade = $result->fetch_assoc())
                        {
                            if ($guardaTipoUnidade["id"] === $unit_type_id)
                            {
?>
                                <option value="<?php echo $unit_type_id["id"];?>" selected><?php echo $unit;?></option>
<?php
                            }
                            else 
                            {
?>
                                <option value="<?php echo $guardaTipoUnidade["id"]?>"><?php echo $guardaTipoUnidade["name"];?></option>
<?php
                            }
                            
                        }
?>
            </select>
            <br>
            <label class="error" for="tipoUnidade_<?php echo $prop['id'];?>"></label><br>
            <label>Ordem do campo no formulário</label><br>
            <input id="ordem" type="text" name="ordem_<?php echo $prop['id'];?>" min="1" value="<?php echo $form_field_order;?>"><br>
            <label class="error" for="ordem_<?php echo $prop['id'];?>"></label><br>
            <label>Tamanho do campo no formulário</label><br>
            <input type="text" name="tamanho_<?php echo $prop['id'];?>" value="<?php echo $form_field_size;?>"><br>
            <label>Obrigatório</label><br>
<?php
                if ($mandatory)
                {
?>       
                    <input id="mandatory" type="radio" name="obrigatorio_<?php echo $prop['id'];?>" value="1" checked>Sim
                    <br>
                    <input id="mandatory" type="radio" name="obrigatorio_<?php echo $prop['id'];?>" value="0">Não
                    <br>
                    <label class="error" for="obrigatorio_<?php echo $prop['id'];?>"></label><br>
<?php
                }
                else
                {
?>       
                    <input id="obrigatorio" type="radio" name="obrigatorio_<?php echo $prop['id'];?>" value="1">Sim
                    <br>
                    <input id="obrigatorio" type="radio" name="obrigatorio_<?php echo $prop['id'];?>" value="0" checked>Não
                    <br>
                    <label class="error" for="obrigatorio_<?php echo $prop['id'];?>"></label><br>
<?php   
                }
                if ($tipo ==="entity")
                {
?>
                    <label>Entidade referenciada por esta propriedade</label><br>
                    <select id="entidadeReferenciada" name="entidadeReferenciada_<?php echo $prop['id'];?>">
                    <option value="NULL"></option>
<?php                 
                    $selecionaEntidades= "SELECT id, name FROM ent_type";
                    $result = $this->db->runQuery($selecionaEntidades);
                    while($guardaEntidade = $result->fetch_assoc())
                    {
                        if ($guardaEntidade["id"] === $fk_ent_type_id)
                        {
?>
                            <option value="<?php echo $guardaEntidade["id"];?>" selected><?php echo $guardaEntidade["name"];?></option>
<?php
                        }
                        else
                        {
?>
                            <option value="<?php echo $guardaEntidade["id"];?>"><?php echo $guardaEntidade["name"];?></option>
<?php
                        }
                    }                  
?>
                    </select><br>
<?php
                }
?>
                <label class="error" for="entidadeReferenciada_<?php echo $prop['id'];?>"></label><br>
 <?php          
                if (isset($_REQUEST["rel_id"])) {
?>
                    <input type="hidden" name="relacaoPertence_<?php echo $prop['id'];?>" value="<?php echo $_REQUEST["rel_id"];?>"><br>
<?php
                }
                else {
?>
                    <input type="hidden" name="entidadePertence_<?php echo $prop['id'];?>" value="<?php echo $_REQUEST["ent_id"];?>"><br>
<?php
                }     
        }

?>
                <input type="hidden" name="estado" value="update"><br>
                <input type="submit" value="Editar propriedades">
            </form>
<?php
    }
    
    /**
     * This method executes the necessary update's query to update the values inserted in the database
     */
    private function estadoUpdate() {
        $data = date("Y-m-d H:i:s",time());
        $erro = false;
        echo '<h3>Gestão de propriedades - Atualização</h3>';
        if (isset($_REQUEST["rel_id"])) {
            $queryProp = "SELECT * FROM property WHERE rel_type_id = ".$_REQUEST["rel_id"];
            $this->gereHist->createNewRel($_REQUEST["rel_id"], $this->db, $data);
        }
        else {
            $queryProp = "SELECT * FROM property WHERE ent_type_id = ".$_REQUEST["ent_id"];
            $this->gereHist->createNewEnt($_REQUEST["ent_id"], $this->db, $data);
        }
        $queryProp = $this->db->runQuery($queryProp);
        $numProp = $queryProp->num_rows;
        $contaProp = 1;
        while ($prop = $queryProp->fetch_assoc()) {
            if ($this->checkforChanges($prop['id'])) {
            if(!empty($_REQUEST["entidadePertence_".$prop['id']]))
            {
                $entRelQuery = 'SELECT name FROM ent_type WHERE id = '.$_REQUEST["entidadePertence_".$prop['id']];
            }
            else
            {
                $entRelQuery = "SELECT name FROM rel_type AS rel WHERE rel.id = ".$_REQUEST["relacaoPertence_".$prop['id']];
            }
            $entRelResult = $this->db->runQuery($entRelQuery);
            $entRelArray = $entRelResult->fetch_assoc();
            // contrução do form_field_name
            // obtém-se o nome da entidade a que corresponde a propriedade que queremos introduzir
            $entRel = $entRelArray["name"];
            // Obtemos as suas 3 primeiras letras
            $entRel = substr($entRel, 0 , 3);
            $traco = '-';
            $idProp = $prop['id'];
            // Garantimos que não há SQL injection através do campo nome
            $nome = $this->db->getMysqli()->real_escape_string($_REQUEST["nome_".$prop['id']]);
            // Substituimos todos os carateres por carateres ASCII
            $nomeField = preg_replace('/[^a-z0-9_ ]/i', '', $nome);
            // Substituimos todos pos espaços por underscore
            $nomeField = str_replace(' ', '_', $nomeField);
            $form_field_name = $entRel.$traco.$idProp.$traco.$nomeField;
            if ($this->gereHist->atualizaHistorico($this->db,$data,$prop['id'],false) == false) {
?>
                <p>Não foi possível atualizar a propriedade pretendida.</p>
<?php 
                goBack();
            }
            else {
                $queryUpdate = 'UPDATE property SET name=\''.$this->db->getMysqli()->real_escape_string($_REQUEST["nome_".$prop['id']]).'\',value_type=\''.$_REQUEST["tipoValor_".$prop['id']].'\',form_field_name=\''.$form_field_name.'\',form_field_type=\''.$_REQUEST["tipoCampo_".$prop['id']].'\',unit_type_id='.$_REQUEST["tipoUnidade_".$prop['id']];
                if(!empty($_REQUEST["tamanho_".$prop['id']]))
                {
                    $queryUpdate .= ',form_field_size="'.$this->db->getMysqli()->real_escape_string($_REQUEST["tamanho_".$prop['id']]).'"';
                }
                $queryUpdate .= ',form_field_order='.$this->db->getMysqli()->real_escape_string($_REQUEST["ordem_".$prop['id']]).',mandatory='.$_REQUEST["obrigatorio_".$prop['id']].',state="active"';

                if (!empty($_REQUEST["entidadeReferenciada_".$prop['id']]))
                {
                    $queryUpdate .= ',fk_ent_type_id='.$_REQUEST["entidadeReferenciada_".$prop['id']];
                }
                if (!empty($_REQUEST["entidadePertence_".$prop['id']]))
                {
                    $queryUpdate .= ',ent_type_id='.$_REQUEST["entidadePertence_".$prop['id']];
                }
                else
                {
                    $queryUpdate .= ',rel_type_id='.$_REQUEST["relacaoPertence_".$prop['id']];
                }
                $queryUpdate .= ",updated_on ='".$data."' WHERE id = ".$prop['id'];
                $update = $this->db->runQuery($queryUpdate);
                if (!$update){
                    $erro = true;
                    $this->db->getMysqli()->rollback();
?>
                    <p>Não foi possível atualizar a propriedade pretendida.</p>
<?php 
                    goBack();
                    break;
                }
            }
            }
            $contaProp++;
        }
        if (!$erro) {
            $this->db->getMysqli()->commit();
?>
            <p>Atualizou os dados da(s) propriedade(s) com sucesso.</p>
            <p>Clique em <a href="/gestao-de-propriedades/">Continuar</a> para avançar.</p>
<?php
        }
    }
}

class PropHist{
    /**
     * Constructor method
     */
    public function __construct(){
    }
    
    /**
     * This method is responsible for insert into the history a copy of the property
     * before being updated
     * @param type $db (object form the class Db_Op)
     * @param type $data (date of modification)
     * @param type $idProp (id of the property we want to create history)
     * @param boolean $last (indicates that it is the last property inserted on the history if so we need to create a new ent_type version )
     */
    public function atualizaHistorico ($db, $data,$idProp, $last) {
        $db->getMysqli()->autocommit(false);
        $db->getMysqli()->begin_transaction();
        $selectAtributos = "SELECT * FROM property WHERE id = ".$idProp;
        $selectAtributos = $db->runQuery($selectAtributos);
        $atributos = $selectAtributos->fetch_assoc();
        $attr = $val = "";
        $isEntity = false;
        foreach ($atributos as $atributo => $valor) {
            if ($atributo == "updated_on") {
                $atributo = "active_on";
            }
            if ($atributo == "state") {
                $atributo = "state_backup";
            }
            if ($atributo != "id" && !is_null($valor)) {
                $attr .= "`".$atributo."`,";
                $val .= "'".$valor."',"; 
            }
            if ($atributo == "ent_type_id" && !is_null($valor)) {
               $isEntity = true; 
            }
        }
        $updateHist = "INSERT INTO `hist_property`(".$attr." inactive_on, property_id, state) "
                . "VALUES (".$val."'".$data."',".$idProp.",'inactive')";
        $updateHist =$db->runQuery($updateHist);
        if ($updateHist) {
            if ($last && $isEntity && $this->createNewEnt($atributos["ent_type_id"], $db, $data) == false) {
                $db->getMysqli()->rollback();
                return false;
            }
            else if ($last && !$isEntity && $this->createNewRel($atributos["rel_type_id"], $db, $data) == false) {
                $db->getMysqli()->rollback();
                return false;
            }
            else {
                return true;
            }
        }
        else {
            $db->getMysqli()->rollback();
            return false;
        }
    }
    
    /**
     * This method controls the excution flow when the state is Voltar
     * Basicly he does all the necessary queries to reverse a property to an old version
     * saved in the history
     * @param type $db (object form the class Db_Op)
     */
    public function estadoVoltar ($db) {
        $data = date("Y-m-d H:i:s",time());
        $this->atualizaHistorico($db,$data,$_REQUEST['prop_id'],true);
        $selectAtributos = "SELECT * FROM hist_property WHERE id = ".$_REQUEST['hist'];
        $selectAtributos = $db->runQuery($selectAtributos);
        $atributos = $selectAtributos->fetch_assoc();
        $updateHist = "UPDATE property SET ";
        foreach ($atributos as $atributo => $valor) {
            if ($atributo != "id" && $atributo != "inactive_on" && $atributo != "active_on" && $atributo != "property_id" && !is_null($valor)) {
                $updateHist .= $atributo." = '".$valor."',"; 
            }
        }
        $updateHist .= " updated_on = '".$data."' WHERE id = ".$_REQUEST['prop_id'];
        echo $updateHist;
        $updateHist =$db->runQuery($updateHist);
        if ($updateHist) {
            $db->getMysqli()->commit();
?>
            <p>Atualizou a propriedade com sucesso para uma versão anterior.</p>
            <p>Clique em <a href="/gestao-de-propriedades/">Continuar</a> para avançar.</p>
<?php
        }
        else {
?>
            <p>Não foi possível reverter a propriedade para a versão selecionada</p>
<?php
            $db->getMysqli()->rollback();
            goBack();
        }
    }
    
    /**
     * Create a new version of ent_type because the properties of it changed
     * @param type $idEnt (id of the ent_type we want to create a new version)
     * @param type $db (object form the class Db_Op)
     */
    public function createNewEnt ($idEnt, $db, $data) {
        $getEnt = "SELECT * FROM ent_type WHERE id = ".$idEnt;
        $getEnt =$db->runQuery($getEnt);
        $getEnt = $getEnt->fetch_assoc();
        $atributo = $valor = "";
        foreach ($getEnt as $attr => $val) {
            if ($attr == "updated_on") {
                $attr = "active_on";
            }
            if ($attr == "state") {
                $val = "inactive";
            }
            if ($attr != "id" && !is_null($val)) {
                $atributo .= "".$attr.", ";
                $valor .= "'".$val."', "; 
            }
        }
        $updateEntHist = "INSERT INTO hist_ent_type (".$atributo."inactive_on, ent_type_id) "
                . "VALUES (".$valor."'".$data."',".$idEnt.")";
        $updateEntHist =$db->runQuery($updateEntHist);
        if (!$updateEntHist) {
            $db->getMysqli()->rollback();
            return false;
        }
        else {
            $updateEnt = "UPDATE ent_type SET updated_on = '".$data."'";
            $updateEnt =$db->runQuery($updateEnt);
            if (!$updateEnt) {
                $db->getMysqli()->rollback();
                return false;
            }
            else {
                return true;
            }
        }
    }
    
    /**
     * Create a new version of rel_type because the properties of it changed
     * @param type $idRel (id of the rel_type we want to create a new version)
     * @param type $db (object form the class Db_Op)
     */
    public function createNewRel ($idRel, $db, $data) {
        $getEnt = "SELECT * FROM rel_type WHERE id = ".$idRel;
        $getEnt =$db->runQuery($getEnt);
        $getEnt = $getEnt->fetch_assoc();
        $atributo = $valor = "";
        foreach ($getEnt as $attr => $val) {
            if ($attr == "updated_on") {
                $attr = "active_on";
            }
            if ($attr == "state") {
                $val = "inactive";
            }
            if ($attr != "id" && !is_null($val)) {
                $atributo .= "".$attr.", ";
                $valor .= "'".$val."', "; 
            }
        }
        $updateRelHist = "INSERT INTO hist_rel_type (".$atributo."inactive_on, rel_type_id) "
                . "VALUES (".$valor."'".$data."',".$idRel.")";
        $updateRelHist =$db->runQuery($updateRelHist);
        if (!$updateRelHist) {
            $db->getMysqli()->rollback();
            return false;
        }
        else {
            $updateRel = "UPDATE rel_type SET updated_on = '".$data."'";
            $updateRel =$db->runQuery($updateRel);
            if (!$updateRel) {
                $db->getMysqli()->rollback();
                return false;
            }
            else {
                return true;
            }
        }
    }
    
    /**
     * This method is responsible for the execution flow when the state is Histórico.
     * He starts by presenting a datepicker with options to do a kind of filter of 
     * all the history of the selected property.
     * After that he presents a table with all the versions presented in the history
     * @param type $db (object form the class Db_Op)
     */
    public function estadoHistorico ($db) {
        if (isset($_REQUEST["histAll"])) {
            $this->apresentaHistTodas($_REQUEST["tipo"], $db);
        }
        else {
        //meto um datepicker        
?>
        <form method="GET">
            Verificar histórico:<br>
            <input type="radio" name="controlDia" value="ate">até ao dia<br>
            <input type="radio" name="controlDia" value="aPartir">a partir do dia<br>
            <input type="radio" name="controlDia" value="dia">no dia<br>
            <input type="text" class="datepicker" name="data" placeholder="Introduza uma data">
            <input type="hidden" name="estado" value="historico">
            <input type="hidden" name="id" value="<?php echo $_REQUEST["id"]; ?>">
            <input type="submit" value="Apresentar histórico">
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>Data de Início</th>
                    <th>Data de Fim</th>
                    <th>Propriedade</th>
                    <th>Tipo de valor</th>
                    <th>Nome do campo no formulário</th>
                    <th>Tipo do campo no formulário</th>
                    <th>Tipo de unidade</th>
                    <th>Ordem do campo no formulário</th>
                    <th>Tamanho do campo no formulário</th>
                    <th>Obrigatório</th>
                    <th>Estado</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
<?php
        if (empty($_REQUEST["data"])) {
            $queryHistorico = "SELECT * FROM hist_property WHERE property_id = ".$_REQUEST["id"]." ORDER BY inactive_on DESC";
        }
        else {
            if (isset($_REQUEST["controlDia"]) && $_REQUEST["controlDia"] == "ate") {
                $queryHistorico = "SELECT * FROM hist_property WHERE property_id = ".$_REQUEST["id"]." AND inactive_on <= '".$_REQUEST["data"]."' ORDER BY inactive_on DESC";
            }
            else if (isset($_REQUEST["controlDia"]) && $_REQUEST["controlDia"] == "aPartir") {
                $queryHistorico = "SELECT * FROM hist_property WHERE property_id = ".$_REQUEST["id"]." AND inactive_on >= '".$_REQUEST["data"]."' ORDER BY inactive_on DESC";
            }
            else if (isset($_REQUEST["controlDia"]) && $_REQUEST["controlDia"] == "dia"){
                $queryHistorico = "SELECT * FROM hist_property WHERE property_id = ".$_REQUEST["id"]." AND inactive_on < '".date("Y-m-d",(strtotime($_REQUEST["data"]) + 86400))."' AND inactive_on >= '".$_REQUEST["data"]."' ORDER BY inactive_on DESC";
            }
            else {
                $queryHistorico = "SELECT * FROM hist_property WHERE property_id = ".$_REQUEST["id"]." AND inactive_on < '".date("Y-m-d",(strtotime($_REQUEST["data"]) + 86400))."' AND inactive_on >= '".$_REQUEST["data"]."' ORDER BY inactive_on DESC";
            }
        }
        $queryHistorico = $db->runQuery($queryHistorico);
        if ($queryHistorico->num_rows == 0) {
?>
            <tr>
                <td colspan="11">Não existe registo referente à propriedade selecionada no histórico</td>
                <td><?php goBack(); ?></td>
            </tr>
<?php
        }
        else {
            while ($hist = $queryHistorico->fetch_assoc()) {
?>
                <tr>
                    <td><?php echo $hist["active_on"];?></td>
                    <td><?php echo $hist["inactive_on"];?></td>
                    <td><?php echo $hist["name"];?></td>
                    <td><?php echo $hist["value_type"];?></td>
                    <td><?php echo $hist["form_field_name"];?></td>
                    <td><?php echo $hist["form_field_type"];?></td>
                    <td>
<?php
                        if (empty($hist["unit_type_id"]))
                        {
                            echo "-";
                        }
                        else
                        {
                            $queryUn = "SELECT name FROM prop_unit_type WHERE id =".$hist["unit_type_id"];
                            echo $db->runQuery($queryUn)->fetch_assoc()["name"];
                        }
?>
                    </td>
                    <td><?php echo $hist["form_field_order"];?></td>
                    <td><?php echo $hist["form_field_size"]; ?></td>
                    <td>
<?php
                        if ($hist["mandatory"] == 1)
                        {
                            echo "sim";
                        }
                        else
                        {
                            echo " não";
                        }
?>
                    </td>
                    <td>

<?php
                    if ($hist["state"] === "active")
                    {
                        echo 'Ativo';
                    }
                    else
                    {
                        echo 'Inativo';
                    }
?>
                    </td>
                    <td><a href ="?estado=voltar&hist=<?php echo $hist["id"];?>&prop_id=<?php echo $_REQUEST["id"];?>&tipoValor=<?php echo $hist["value_type"];?><?php if (isset($hist["ent_type_id"])) echo "&entidadePertence=".$hist["ent_type_id"];if (isset($hist["rel_type_id"])) echo "&relacaoPertence=".$hist["rel_type_id"];?>&tipoCampo=<?php echo $hist["form_field_type"];if (isset($hist["unit_type"])) echo "&tipoUnidade=".$hist["unit_type"];if (isset($hist["fk_ent_type_id"])) echo "&entidadeReferenciada=".$hist["fk_ent_type_id"];?>">
                            Voltar para esta versão
                        </a>
                    </td>
                </tr>
<?php
            }
        }
?>
            <tbody>
        </table>
<?php
        
    }
    }
    
    /**
     * This method creates a table with a view of all the properties in the selected day
     * @param type $tipo (indicates if we are working with relations or entities)
     * @param type $db (object form the class Db_Op)
     */
    private function apresentaHistTodas ($tipo, $db) {
?>
        <table class="table">
            <thead>
                <tr>
                <?php
                    if ($tipo === "entity")
                    {
                ?>
                    <th>Entidade</th>
                <?php
                    }
                    else
                    {
                ?>
                    <th>Relação</th>
                <?php
                    }
                ?>
                    <th>ID</th>
                    <th>Propriedade</th>
                    <th>Tipo de valor</th>
                    <th>Nome do campo no formulário</th>
                    <th>Tipo do campo no formulário</th>
                    <th>Tipo de unidade</th>
                    <th>Ordem do campo no formulário</th>
                    <th>Tamanho do campo no formulário</th>
                    <th>Obrigatório</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
<?php
                if ($tipo === "entity")
                {
                    $selecionaEntOrRel = "SELECT name, id FROM ent_type";
                    $resultSelEntOrRel = $db->runQuery($selecionaEntOrRel);
                }
                else
                {
                    $selecionaEntOrRel = "SELECT name, id FROM rel_type";
                    $resultSelEntOrRel = $db->runQuery($selecionaEntOrRel);
                }
                while ($resEntRel = $resultSelEntOrRel->fetch_assoc())
                {
                    $idEntRel = $resEntRel["id"];
                    if ($tipo === "entity")
                    {
                        $nome = $resEntRel["name"];
                        $selecionaHist = "SELECT * FROM hist_property WHERE (('".$_REQUEST["data"]."' > active_on AND '".$_REQUEST["data"]."' < inactive_on) OR ((active_on LIKE '".$_REQUEST["data"]."%' AND inactive_on < '".$_REQUEST["data"]."') OR inactive_on LIKE '".$_REQUEST["data"]."%')) AND ent_type_id = ".$idEntRel." GROUP BY property_id ORDER BY inactive_on DESC";
                        $selecionaProp = "SELECT * FROM property WHERE (updated_on <'".$_REQUEST["data"]."'OR updated_on LIKE '".$_REQUEST["data"]."%') AND ent_type_id = ".$idEntRel;
                    }
                    else
                    {
                        $nome = $resEntRel["name"];
                        $selecionaHist = "SELECT * FROM hist_property WHERE (('".$_REQUEST["data"]."' > active_on AND '".$_REQUEST["data"]."' < inactive_on) OR ((active_on LIKE '".$_REQUEST["data"]."%' AND inactive_on < '".$_REQUEST["data"]."') OR inactive_on LIKE '".$_REQUEST["data"]."%')) AND rel_type_id = ".$idEntRel." GROUP BY property_id ORDER BY inactive_on DESC";
                        $selecionaProp = "SELECT * FROM property WHERE (updated_on < '".$_REQUEST["data"]."'OR updated_on LIKE '".$_REQUEST["data"]."%') AND rel_type_id = ".$idEntRel;
                    }
                    $resultSelecionaProp = $db->runQuery($selecionaProp);
                    $resultSelecionaHist = $db->runQuery($selecionaHist);
                    $numLinhas = $resultSelecionaProp->num_rows + $resultSelecionaHist->num_rows;
                    
                    if ($numLinhas > 0) {
?>
                <tr>
                    <td rowspan="<?php echo $numLinhas; ?>"><?php echo $nome; ?></td>
<?php
                    $creatTempTable = "CREATE TEMPORARY TABLE temp_table (`id` INT UNSIGNED NOT NULL,
                            `name` VARCHAR(128) NOT NULL DEFAULT '',
                            `ent_type_id` INT UNSIGNED NULL,
                            `rel_type_id` INT NULL,
                            `value_type` ENUM('text', 'bool', 'int', 'double', 'enum', 'ent_ref') NOT NULL,
                            `form_field_name` VARCHAR(64) NOT NULL DEFAULT '',
                            `form_field_type` ENUM('text','textbox','radio','checkbox','selectbox') NOT NULL,
                            `unit_type_id` INT UNSIGNED NULL,
                            `form_field_order` INT UNSIGNED NOT NULL,
                            `mandatory` INT NOT NULL,
                            `state` ENUM('active','inactive') NOT NULL,
                            `fk_ent_type_id` INT UNSIGNED NULL,
                            `form_field_size` VARCHAR(64) NULL)";
                    $creatTempTable = $db->runQuery($creatTempTable);
                    while ($prop = $resultSelecionaProp->fetch_assoc()) {
                        if (empty($prop['ent_type_id'])) {
                            $ent_type = "NULL";
                        }
                        else {
                           $ent_type = $prop['ent_type_id'];
                        }
                        if (empty($prop['rel_type_id'])) {
                            $rel_type = "NULL";
                        }
                        else {
                           $rel_type = $prop['rel_type_id'];
                        }
                        if (empty($prop['unit_type_id'])) {
                            $unit = "NULL";
                        }
                        else {
                           $unit = $prop['unit_type_id'];
                        }
                        if (empty($prop['fk_ent_type_id'])) {
                            $fk = "NULL";
                        }
                        else {
                           $fk = $prop['fk_ent_type_id'];
                        }
                        $db->runQuery("INSERT INTO temp_table VALUES (".$prop['id'].",'".$prop['name']."',".$ent_type.",".$rel_type.",'".$prop['value_type']."','".$prop['form_field_name']."','".$prop['form_field_type']."',".$unit.",'".$prop['form_field_order']."',".$prop['mandatory'].",'".$prop['state']."',".$fk.",'".$prop['form_field_size']."')");
                    }
                    while ($hist = $resultSelecionaHist->fetch_assoc()) {
                        if (empty($hist['ent_type_id'])) {
                            $ent_type = "NULL";
                        }
                        else {
                           $ent_type = $hist['ent_type_id'];
                        }
                        if (empty($hist['rel_type_id'])) {
                            $rel_type = "NULL";
                        }
                        else {
                           $rel_type = $hist['rel_type_id'];
                        }
                        if (empty($hist['unit_type_id'])) {
                            $unit = "NULL";
                        }
                        else {
                           $unit = $hist['unit_type_id'];
                        }
                        if (empty($hist['fk_ent_type_id'])) {
                            $fk = "NULL";
                        }
                        else {
                           $fk = $hist['fk_ent_type_id'];
                        }
                        $db->runQuery("INSERT INTO temp_table VALUES (".$hist['property_id'].",'".$hist['name']."',".$ent_type.",".$rel_type.",'".$hist['value_type']."','".$hist['form_field_name']."','".$hist['form_field_type']."',".$unit.",'".$hist['form_field_order']."',".$hist['mandatory'].",'".$hist['state']."',".$fk.",'".$hist['form_field_size']."')");
                    }
                    
                    $resultSeleciona = $db->runQuery("SELECT * FROM temp_table GROUP BY id ORDER BY id ASC");
                    
                    while($arraySelec = $resultSeleciona->fetch_assoc())
                    {
?>
                        <td><?php echo $arraySelec["id"]; ?></td>
<?php
?>
                        <td><?php echo $arraySelec["name"]; ?></td>
                        <td><?php echo $arraySelec["value_type"]; ?></td>
                        <td><?php echo $arraySelec["form_field_name"]; ?></td>
                        <td><?php echo $arraySelec["form_field_type"]; ?></td>
                        <td>
<?php
                        if (empty($arraySelec["unit_type_id"]))
                        {
                            echo "-";
                        }
                        else
                        {
                            $queryUn = "SELECT name FROM prop_unit_type WHERE id =".$arraySelec["unit_type_id"];
                            echo $db->runQuery($queryUn)->fetch_assoc()["name"];
                        }
?>
                        </td>
                        <td><?php echo $arraySelec["form_field_order"];?></td>
                        <td><?php echo $arraySelec["form_field_size"]; ?></td>
                        <td>
<?php
                        if ($arraySelec["mandatory"] == 1)
                        {
                            echo "sim";
                        }
                        else
                        {
                            echo " não";
                        }
 ?>
                        </td>
                        <td>
<?php
                        if ($arraySelec["state"] === "active")
                        {
                            echo 'Ativo';
                        }
                        else
                        {
                            echo 'Inativo';
                        }
?>
                        </td>
                    </tr>
<?php
                    }
                    $db->runQuery("DROP TEMPORARY TABLE temp_table");
                }
                else {
?>
                <tr>
                    <td colspan="12">Não existe registo referente à entidade selecionada no histórico</td>
                </tr>
<?php
                }
                }
?>
            </tbody>
        </table>
<?php
    }
}

// instantiation of an object from the class PropertyManage. This instantiation is responsable to get the script work as expected.
new PropertyManage();

?>
