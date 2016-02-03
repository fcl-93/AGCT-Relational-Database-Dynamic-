<?php
    //Array para a componente pesquisa dinâmica.
 
   function operadores()
   {  
        $operadores = array(
            "menor"=>"<",
            "maior"=>">",
            "igual"=>"=",
            "diferente"=>"!="
            );
        return $operadores;
   }

	function goBack()
	{
		echo "<script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");</script>
		<noscript>
		<a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
		</noscript>";
	}


	function getEnumValues($table, $field, $link) {
    $enum_array = array();
    
    $query = 'SHOW COLUMNS FROM `' . $table . '` LIKE "' . $field . '"';
    $result = mysqli_query($link,$query);
    if(!$result)
    {
     printf("%s", mysqli_error($link));
    }
    else
    {
     $row = mysqli_fetch_row($result);
     //Trata a coluna onde está o enum e guarda o valor em enum_arry     
     preg_match_all("/'(.*?)'/", $row[1], $enum_array);

     if(!empty($enum_array[1])) 
     {
         // Shift array keys to match original enumerated index in MySQL (allows for use of index values instead of strings)

         foreach($enum_array[1] as $mkey => $mval)
         {
           $enum_fields[$mkey+1] = $mval;
         }
         return $enum_fields;

     }
     else
     {
      return array(); // Return an empty array to avoid possible errors/warnings if array is passed to foreach() without first being checked with !empty().
     } 
    }
}
?>