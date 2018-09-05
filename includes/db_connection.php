<?php 

require_once ('../config/db.php');
$dsn = 'pgsql:host='. $host .';port='. $port .';dbname='. $dbname;

try{
    $pdo = new PDO($dsn, $user, $password);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
	$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


   // echo 'uspesna konekcija';                
   
}
catch(PDOException $ex){
    echo 'greska: '. $ex->getMessage();
}
