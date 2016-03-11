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
            <ol>
                <li><a href="/gestao-de-propriedades?estado=relation">Relação</a></li>
                <li><a href="/gestao-de-propriedades?estado=entity">Entidade</a></li>
            </ol>
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
            $this->apresentaTabelaForm($tipo);
        }
    }

    private function apresentaTabelaForm($tipo)
    {
        if ($tipo === entity)
        {
    ?>
        <html>
            <table>
                <thead>
                    <tr>
                        <th>Entidade</th>
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
                            $selecionaEnt = "SELECT name, id FROM ent_type";
                            $resultSelEnt = $this->db->runQuery($selecionaEnt);
                            $nome = $resultSelEnt->fetch_assoc()["name"];
                            $idEnt = $resultSelEnt->fetch_assoc()["id"];
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
                        ?>
                    </tr>
                </tbody>
            </table>
        </html>
    <?php
        }
        else
        {

        }
    }
}

new PropertyManage();

?>