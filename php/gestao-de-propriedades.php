<?php
require_once("custom/php/common.php");

class PropertyManage
{
    private $db;
    private $capability;
   
    public function __construct(){
        $this->db = new Db_Op();
        $this->capability = "manage_properties";
        $this->executaScript();
    }
    
    public function executaScript()
    {
        //Verifica se algum utilizador está com sessão iniciada
        if ( is_user_logged_in() )
        {
            // Verifica se o utilziador atual tem a capability necessária para esta componente
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
            $this->estadoInserir();
        }
    }

    private function existePropriedade($tipo)
    {
        $querySelect = "SELECT * FROM property WHERE ";
        if ($tipo === "relation")
        {
            $querySelect.= "ent_type_id = NULL";
        }
        else
        {
            $querySelect.= "rel_type_id = NULL";
        }
        $resultSelect = $this->db->runQuery($querySelect);

        if ($resultSelect->num_rows == 0)
        {
    ?>
            <html>
                <p>Não existem propiedades especificadas para o tipo selecionado</p>
            </html>    
    <?php
            return false;
        }
        else
        {
            return true;
        }

    }

    private function estadoEntityRelation($tipo)
    {
        if($this->existePropriedade($tipo))
        {
            $this->apresentaTabela($tipo);
            
        }
        $this->apresentaForm($tipo);
    }

    private function apresentaTabela($tipo)
    {
    ?>
        <html>
            <table>
                <thead>
                    <tr>
                        <?php
                            if ($tipo === entity)
                            {
                        ?>
                                <th>Entidade</th>
                        <?php
                            }
                            else
                            {
                        ?>
                                <th>Relação</th>
                        <?
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
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
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
                                if ($tipo === "entity")
                                {
                                    $nome = $resEntRel["name"];
                                }
                                else
                                {
                                    $queryNome1 = "SELECT name FROM ent_type AS ent, rel_type AS rel WHERE rel.id =".$resEntRel["id"]." AND ent.id = rel.ent_type1_id";
                                    $queryNome2 = "SELECT name FROM ent_type AS ent, rel_type AS rel WHERE rel.id =".$resEntRel["id"]." AND ent.id = rel.ent_type2_id";
                                    $nome = $this->criaNomeRel($queryNome1,$queryNome2);
                                }
                                $idEnt = $resEntRel["id"];
                                $selecionaProp = "SELECT * FROM property WHERE ent_type_id =".$idEnt;
                                $resultSeleciona = $this->db->runQuery($selecionaProp);
                                $numLinhas = $resultSeleciona->num_rows();
                            ?>
                                <td rowspan="<?php echo $numLinhas; ?>"><?php echo $nome; ?></td>
                            <?php
                                while($resultSeleciona->fetch_assoc())
                                {
                            ?>
                                    <td><?php echo $resultSeleciona["id"]; ?></td>
                                    <td><?php echo $resultSeleciona["name"]; ?></td>
                                    <td><?php echo $resultSeleciona["value_type"]; ?></td>
                                    <td><?php echo $resultSeleciona["form_field_name"]; ?></td>
                                    <td><?php echo $resultSeleciona["form_field_type"]; ?></td>
                                    <td>
                                        <?php
                                            if (empty($resultSeleciona["unit_type_id"]))
                                            {
                                                echo "-";
                                            }
                                            else
                                            {
                                                $queryUn = "SELECT name FROM prop_unit_type WHERE id =".$resultSeleciona["unit_type_id"];
                                            }
                                         ?>
                                    </td>
                                    <td><?php echo $resultSeleciona["form_field_order"]; ?>                                </td>
                                    <td><?php echo $resultSeleciona["form_field_size"]; ?></td>
                                    <td>
                                        <?php 
                                            if ($resultSeleciona["mandatory"] === 1)
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
                                            if ($resultSeleciona["state"] === "true")
                                            {
                                                echo "ativo";
                                            }
                                            else
                                            {
                                                echo "inativo";
                                            }
                                        ?>
                                    </td>
                                    <td>[editar][desativar]</td>             
                            <?php
                                }
                            }
                        ?>
                    </tr>
                </tbody>
            </table>
        </html>
    <?php
        }
    }
    
    private function apresentaForm($tipo)
    {
        $existeEntRel = true;
        if($tipo == "entity")
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
        ?>
            <html>
                <form>
                <h3> Gestão de propriedades - introdução </h3>

                <form method="POST">
                    <label>Nome da Propriedade:</label><br>
                    <input type="text" name="nome" required>
                    <br><br>
                    <label>Tipo de valor:</label><br>
                    <?php
                    $field = 'value_type';
                    $table = 'property';
                    $array =$this->db->getEnumValues($table, $field);
                    foreach($array as $values)
                    {
                        echo' <input type="radio" name="tipoValor" value="'.$values.'" required>'.$values.'<br>';
                    }
                    ?>
                    <br>
                    <?php
                        if ($tipo === "entity")
                        {
                            echo'
                            <label>Entidade a que irá pertencer esta propriedade</label><br>
                            <select name="entidadePertence" required>';
                            $selecionaEntRel = "SELECT name, id FROM ent_type";
                        }
                        else
                        {
                            echo'
                            <label>Relação a que irá pertencer esta propriedade</label><br>
                            <select name="relacaoPertence" required>';
                            $selecionaEntRel = "SELECT id FROM rel_type";
                        }
                        $result = $this->db->runQuery($selecionaEntRel);
                        while($guardaEntRel= $result->fetch_assoc())
                        {
                            $queryNome1 = "SELECT name FROM ent_type AS ent, rel_type AS rel WHERE rel.id =".$guardaEntRel["id"]." AND ent.id = rel.ent_type1_id";
                            $queryNome2 = "SELECT name FROM ent_type AS ent, rel_type AS rel WHERE rel.id =".$guardaEntRel["id"]." AND ent.id = rel.ent_type2_id";
                            $this->criaNomeRel($queryNome1, $queryNome2);
                            echo '<option value="'.$guardaEntRel["id"].'">'.$guardaEntRel["name"].'</option>';    
                        }
                        echo '</select><br><br>';
                    ?>
                    <label>Tipo do campo do formulário</label><br>
                    <?php
                        $field = 'form_field_type';
                        $table = 'property';
                        $array = $this->db->getEnumValues($table, $field);
                        foreach($array as $values)
                        {
                            echo' <input type="radio" name="tipoCampo" value="'.$values.'" required>'.$values.'<br>';
                        }
                    ?>
                    <br>
                    <label>Tipo de unidade</label><br>
                    <select name="tipoUnidade">
                    <option value="NULL"></option>';
                    <?php
                        $selecionaTipoUnidade = "SELECT name, id FROM prop_unit_type";
                        $result = mysqli_query($link, $selecionaTipoUnidade);
                        while($guardaTipoUnidade = mysqli_fetch_assoc($result))
                        {
                            echo '<option value="'.$guardaTipoUnidade["id"].'">'.$guardaTipoUnidade["name"].'</option>';
                        }
                    ?>
                    </select><br><br>
                    <label>Ordem do campo no formulário</label><br>
                    <input type="text" name="ordem" min="1" required><br><br>
                    <label>Tamanho do campo no formulário</label><br>
                    <input type="text" name="tamanho"><br><br>
                    <label>Obrigatório</label><br>
                    <input type="radio" name="obrigatorio" value="1" required>Sim
                    <br>
                    <input type="radio" name="obrigatorio" value="2" required>Não
                    <br><br>
                    <?php
                        if ($tipo ==="entity")
                        {
                            echo '<label>Entidade referenciada por esta propriedade</label><br>
                            <select name="entidadeReferenciada">
                            <option value="NULL"></option>';
                            $selecionaEntidades= "SELECT id, name FROM ent_type";
                            $result = $this->db->runQuery($selecionaEntidades);
                            while($guardaEntidade = $result->fetch_assoc($result))
                            {
                                    echo '<option value="'.$guardaEntidade["id"].'">'.$guardaEntidade["name"].'</option>';
                            }
                            echo '</select><br><br>';
                        }
                    ?>
                    <input type="hidden" name="estado" value="inserir"><br>
                    <input type="submit" value="Inserir propriedade">
                </form>
            <html>
            <?php
            }
    }
    
    private function criaNomeRel($queryNome1, $queryNome2)
    {
        $nome1 = $this->db->runQuery($queryNome1)->fetch_assoc()["name"];
        $nome2 = $this->db->runQuery($queryNome2)->fetch_assoc()["name"];
        $nome = $nome1."-".$nome2;
        return $nome;
    }
    
    private function estadoInserir()
    {
        echo '<h3>Gestão de propriedades - inserção</h3>';
        if(!empty($_REQUEST["entidadePertence"]))
        {
            $entRelQuery = 'SELECT name FROM ent_type WHERE id = '.$_REQUEST["entidadePertence"];
            $entRelResult = $this->db->runQuery($entRelQuery);
            $entRelArray = $entRelResult->fetch_assoc();
            // contrução do form_field_name
            // obtém-se o nome da entidade a que corresponde a propriedade que queremos introduzir
            $entRel = $entRelArray["name"];
        }
        else
        {
            $queryNome1 = "SELECT name FROM ent_type AS ent, rel_type AS rel WHERE rel.rel_type = ".$_REQUEST["relacaoPertence"]." AND ent.id = rel.ent_type1_id";
            $queryNome2 = "SELECT name FROM ent_type AS ent, rel_type AS rel WHERE rel.rel_type = ".$_REQUEST["relacaoPertence"]." AND ent.id = rel.ent_type2_id";
            $entRel = criaNomeRel($queryNome1, $queryNome2);
        }	
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
	// Inicia uma tansação uma vez que, devido ao id no campo form_field_name vamos ter de atualizar esse atributo, após a inserção
	$this->db->getMysqli()->autocommit(false);
	$this->db->getMysqli()->begin_transaction();
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
        $queryInsere .=  '`form_field_order`, `mandatory`, `state`, `comp_fk_id`) VALUES (NULL,\''.$this->db->getMysqli()->real_escape_string($_REQUEST["nome"]).'\',';
        if(!empty($_REQUEST["entidadePertence"]))
        {
           $queryInsere .= $_REQUEST["componentePertence"]; 
        }
        else
        {
            $queryInsere .=  $_REQUEST["relacaoPertence"]; 
        }
        $queryInsere .= ',\''.$_REQUEST["tipoValor"].'\',\''.$form_field_name.'\',\''.$_REQUEST["tipoCampo"].'\','.$_REQUEST["tipoUnidade"];
        if(!empty($_REQUEST["tamanho"]))
	{
            $queryInsere = ',"'.$this->db->getMysqli()->real_escape_string($_REQUEST["tamanho"]).'"';
	}
        $queryInsere .= ','.$this->db->getMysqli()->real_escape_string($_REQUEST["ordem"]).','.$_REQUEST["obrigatorio"].',"active",'.$_REQUEST["componenteReferenciado"].')';
	$insere = $this->db->runQuery($queryInsere);
	if(!$insere)
	{
		$this->db->getMysqli()->rollback();
	}
	else
	{
		//obtem o último id que foi introduzido na BD
		$id = $this->db->getMysqli()->insert_id ();
		// constroi novamente o form_field_name agora com o id do tuplo que acabou de ser introduzido
		$form_field_name = $ent.$traco.$id.$traco.$nomeField;
		// atualiza esse atributo
		$atualiza = "UPDATE property SET form_field_name = '".$form_field_name."' WHERE property.id = ".$id;
		$atualiza = $this->db->runQuery($atualiza);
		if(!$atualiza)
		{
			$this->db->getMysqli()->rollback();
		}
		else
		{
			$this->db->getMysqli()->commit();
			echo 'Inseriu os dados de nova propriedade com sucesso.';
			echo 'Clique em <a href="/gestao-de-propriedades/">Continuar</a> para avançar.';
		}
	}
        
    }
    
    private function validarDados()
    {
        if(!is_numeric($_REQUEST["ordem"]) || empty($_REQUEST["ordem"]))
	{
		echo 'ERRO! O valor introduzido no campo Ordem do campo no formulário não é numérico!<br>';
		goBack();
		echo '<br>';
		return false;
	}
	else if($_REQUEST["ordem"] < 1)
	{
		echo 'ERRO! O valor introduzido no campo Ordem do campo no formulário deve ser superior a 0!<br>';
		goBack();
		echo '<br>';
		return false;
	}
	if(($_REQUEST["tipoCampo"] === "text") && (!is_numeric($_REQUEST["tamanho"]) || empty($_REQUEST["tamanho"])))
	{
		echo 'ERRO! O campo Tamanho do campo no formulário deve ser preenchido com valores numéricos 
			uma vez que indicou que o Tipo do campo do formulário era text<br>';
		goBack();
		echo '<br>';
		return false;
	}
        // preg_match serve para verificar se o valor introduzido está no formato aaxbb onde aa e bb são números de 0 a 9
	if(($_REQUEST["tipoCampo"] === "textbox") && ((preg_match("/[0-9]{2}x[0-9]{2}/", $_REQUEST["tamanho"]) === 0) || empty($_REQUEST["tamanho"])))
	{
		echo 'ERRO! O campo Tamanho do campo no formulário deve ser preenchido com o seguinte formato
		 aaxbb em que aa é o número de colunas e bb o número de linhas da caixa de texto<br>';
		goBack();
		echo '<br>';
		return false;
	}
	return true;
    }
}

new PropertyManage();

?>