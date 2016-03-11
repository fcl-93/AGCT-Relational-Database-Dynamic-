<?php
require_once("custom/php/common.php");

class PropertyManage
{
    private $db;
    private $capability;
   
    public function __construct(){
        $db = new Db_Op();
        $capability = "manage_properties";
        executaScript();
    }
    
    public function executaScript()
    {
        //Verifica se algum utilizador está com sessão iniciada
        if ( is_user_logged_in() )
        {
            // Verifica se o utilziador atual tem a capability necessária para esta componente
            if(current_user_can($capability))
            {
                verificaEstado();
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
        print_r($_REQUEST);
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
            estadoEntityRelation("relation");
        }
        elseif ($_REQUEST["estado"] === "entity")
        {
            estadoEntityRelation("entity");
        }
        elseif ($_REQUEST["estado"] === "inserir")
        {
            estadoInserir();
        }
    }

    private function existePropriedade($tipo)
    {
        echo "entrei aqui";
        $querySelect = "SELECT * FROM PROPERTY WHERE ";
        if ($tipo === "relation")
        {
            $querySelect.= "ent_type_id = NULL";
        }
        else
        {
            $querySelect.= "rel_type_id = NULL";
        }

        $reusltSelect = $db->runQuery($querySelect);

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
        if(existePropriedade($tipo))
        {
            apresentaTabelaForm($tipo);
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
                            $selecionaProp = "SELECT * FROM property WHERE rel_type_id = NULL";
                            $resultSeleciona = $db->runQuery($selecionaProp);
                            $numLinhas = $resultSeleciona->num_rows();
                            while($resultSeleciona->fetch_assoc())
                            {
                                $ent_type_id = $resultSeleciona["ent_type_id"];
                                $idProp = $resultSeleciona["id"];
                                $nome = $resultSeleciona["ent_type_id"];
                                $value_type = $resultSeleciona["value_type"];
                                $form_field_name = $resultSeleciona["form_field_name"];
                                $form_field_type = $resultSeleciona["form_field_type"];
                                $unit_type_id = $resultSeleciona["unit_type_id"];
                                $form_field_order = $resultSeleciona["form_field_order"];
                                $form_field_size = $resultSeleciona["form_field_size"];
                                $mandatory = $resultSeleciona["mandatory"];
                                $state = $resultSeleciona["state"];                            
                            }
                        ?>
                        <td rowspan="<?php echo $numLinhas?>"></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
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