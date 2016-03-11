<?php

//This class will handle all the operations related to the database
class Db_Op
{
    //DB_HOST,DB_USER,DB_PASSWORD,DB_NAME these are contants present in the wordpress
    public $mysqli;
  
    //This method will make the database connection
    public function connectToDb()
    {
      $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
      if($mysqli->connect_errno)
      {
      	printf("Connect failed: %s\n", $mysqli->connect_error);
      	exit();
      }
    }
	
    //This method will receive a String(query) and will process it 
    //If the query received starts with 
    // SELECT, SHOW, DESCRIBE, EXPLAIN it will return a mysqli_result object
    // else it will return true and at last if something goes wrong after/during the query 
    // run the result that will be returned is false.
    public function runQuery($query)
    {
    	if(substr($query,0,5) == "SELECT" ||
    	   substr($query,0,3) == "SHOW"||
    	   substr($query,0,7) == "DESCRIBE"||
    	   substr($query,0,6) == "EXPLAIN")
    	{
    		$result = $mysqli->query($query);
    		if($result == false)					//erro na query
    		{
    			printf("".$query);
    			printf("Error: %s\n", $mysqli->error);
    			return $result;
    		}
    		else 									//retorna um objecto do tipo  mysqli_result 
    		{
    			return $result;
    		}
    	}
    	else
    	{
    		$result = $mysqli->query($query);
    		if($result == false)
    		{
    			printf("".$query);
    			printf("Error: %s\n", $mysqli->error);
    			return $result;
    		}
    		else
    		{
    			return true;
    		}
    	}
    }
    
    //This method will disconnect a database connection
    public function disconnectToDb()
    {
      $mysqli->close();
    }
}




















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
