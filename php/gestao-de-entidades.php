<?php
	require_once("custom/php/common.php");

	$bd = new Db_Op();
	$bd->connectToDb();
	
	
	
	while($print = mysqli_fetch_assoc($bd->runQuery("SELECT * FROM wordpress")))
	{
		echo $print;
	}
	
 ?>
