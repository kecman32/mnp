<?php 

require_once("../includes/tools.php");
require_once ('../includes/db_connection.php');



$get = file_get_contents('ustanove.json');

$json = json_decode($get, true);
$skole = $json[2]['data'];

$get = file_get_contents('logins.json');

$json = json_decode($get, true);
$logins = $json[2]['data'];

echo "<pre>";
//print_r($skole);

// foreach ($skole as $skola) {
// 	echo $skola['SU']."<br>";
// }

$sql = 'SELECT skolskeuprave_id, naziv
		FROM pratece.skolske_uprave;';
$stmt = $pdo->prepare($sql);
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    $uprave[$row['naziv']]= $row['skolskeuprave_id'];
}

// print_r($uprave); 
// echo "<br>";

$sql = 'SELECT okrug_id, naziv
		FROM pratece.okruzi;';
$stmt = $pdo->prepare($sql);
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    $okruzi[$row['naziv']]= $row['okrug_id'];
}

$sql = 'SELECT opstina_id, naziv
		FROM pratece.opstine;';
$stmt = $pdo->prepare($sql);
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    $opstine[$row['naziv']]= $row['opstina_id'];
}


$sql = 'SELECT naselje_id, naziv
		FROM pratece.naselja;';
$stmt = $pdo->prepare($sql);
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    $naselja[$row['naziv']]= $row['naselje_id'];
}


// foreach ($skole as $skola) {

// 	$sql = "SELECT * FROM pratece.okruzi WHERE naziv = ?";
// 	$data = [$skola['okrug']];
// 	$stmt = $pdo->prepare($sql);
// 	$stmt->execute($data);
// 	$count = $stmt->rowCount();
// 	if ($count == 0) {

// 		$sql = "INSERT INTO pratece.okruzi(naziv, skolskauprava_id)
// 		VALUES (?, ?);";
// 		$data = [$skola['okrug'], $uprave[$skola['SU']]];
// 		$stmt = $pdo->prepare($sql);
// 		$stmt->execute($data);
		
// 	}
	
	
// }


// foreach ($skole as $skola) {

// 	$sql = "SELECT * FROM pratece.opstine WHERE naziv = ?";
// 	$data = [$skola['opstina']];
// 	$stmt = $pdo->prepare($sql);
// 	$stmt->execute($data);
// 	$count = $stmt->rowCount();
// 	if ($count == 0) {

// 		$sql = "INSERT INTO pratece.opstine(okrug_id, naziv)
// 				VALUES (?, ?);";
// 		$data = [$okruzi[$skola['okrug']], $skola['opstina']];
// 		$stmt = $pdo->prepare($sql);
// 		$stmt->execute($data);
		
// 	}
	
	
// }


// foreach ($skole as $skola) {

// 	$sql = "SELECT naziv FROM pratece.naselja WHERE naziv = ?";
// 	$data = [$skola['naselje']];
// 	$stmt = $pdo->prepare($sql);
// 	$stmt->execute($data);
// 	$count = $stmt->rowCount();
// 	if ($count == 0) {

// 		$sql = "INSERT INTO pratece.naselja(naziv, opstina_id)
// 				VALUES (?, ?);";
// 		$data = [$skola['naselje'], $opstine[$skola['opstina']]];
// 		$stmt = $pdo->prepare($sql);
// 		$stmt->execute($data);
		
// 	}
	
	
// }


// foreach ($skole as $skola) {

	

// 		$sql = "INSERT INTO skole.skole(skola_id, naziv, naselje_id)
// 				VALUES (?, ?, ?);";
// 		$data = [$skola['ustanova_id'], $skola['ustanova'], $naselja[$skola['naselje']]];
// 		$stmt = $pdo->prepare($sql);
// 		$stmt->execute($data);
		
		
	
// }



// foreach ($logins as $log) {
	
// 	$sql = "INSERT INTO skole.operateri(skola_id, osoba_id, username, lozinka)
// 	VALUES (?, ?, ?, ?);";

// 	$data = [$log['ustanova_id'], $log['osoba_id'], $log['username'], $log['lozinka']];
// 	$stmt = $pdo->prepare($sql);
// 	$stmt->execute($data);
// }


$jezici = ['Српски језик', 'Страни језик', 'Остало', 'Албански језик', 'Босански језик', 'Бугарски језик', 'Маћарски језик', 'Румунски језик', 'Русински језик', 'Словачки језик', 'Хрватски језик'];

