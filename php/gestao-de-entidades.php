<?php
	require_once("custom/php/common.php");

	$bd = new Db_Op();
	//$bd->connectToDb();
	
	$result = $bd->runQuery("SELECT * FROM wordpress");
	
	while($print = mysqli_fetch_assoc($result))
	{
		echo $print;
	}
	
 ?>
