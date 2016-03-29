<?php
if(isset($_REQUEST["ent"])) {
    if ("nada selecionado") {
        $query = "SELECT * FROM entity WHERE ent_type_id = ".$_REQUEST["ent"];
    }
    else if ("selecionado 1ª tabela") {
        $query = "SELECT e.id, e.name FROM entity AS e, value AS v WHERE v.value 'operador selecionado' 'valor pretendido' AND  v.property_id = 'propriedade pretendida' AND v.entity_id = e.id";
    }
    else if ("selecionado 2ª tabela") {
        //1º seleciono a entidade que tem as carateristicas
        //2º seleciono a entidade que tem referencia à entidade selecionda anteriormente
        $query1 = "SELECT e.id, e.name FROM entity AS e, value AS v WHERE v.value 'operador selecionado' 'valor pretendido' AND  v.property_id = 'propriedade pretendida' AND v.entity_id = e.id";
        $query2 = "SELECT e.id, e.name FROM property AS p, entity AS e, value AS v WHERE v.value = 'resultado da query anterior' AND  v.property_id = 'propriedade do tipo ent_ref' AND v.entity_id = e.id  AND v.property_id = p.id";
    }
    else if ("selecionado 3ª tabela") {
        //1º seleciono a entidade que tem as carateristicas
        //2º seleciono a entidade que tem rleação com a entidade selecionda anteriormente
        $query1 = "SELECT e.id, e.name FROM entity AS e, value AS v WHERE v.value 'operador selecionado' 'valor pretendido' AND  v.property_id = 'propriedade pretendida' AND v.entity_id = e.id";
        $query2 = "SELECT e.id, e.name FROM entity AS e, relation AS r WHERE (r.entity1_id = 'resultado da query anterior' OR r.entity2_id = 'resultado da query anterior')  AND (e.id = r.entity1_id OR e.id = r.entity2_id)";
    }
    else if ("selecionado 1ª e 2ª tabela") {
        $query = "Possivelmente um dos tipos de JOIN utilizando as queries de cima para os casos de seleçãod a 1ª e 2ª tabelas";
    }
    else if ("selecionado 1ª e 3ª tabela") {
        
    }
    else if ("selecionado 2ª e 3ª tabela") {
        
    }
    
}
else {
    if ("nada selecionado") {
        $query = "SELECT * FROM relation WHERE rel_type_id = ".$_REQUEST["ent"];
    }
}

