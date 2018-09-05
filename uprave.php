<?php

require_once ('includes/db_connection.php');




// $sql = 'SELECT naziv_opsine, naziv_okruga FROM opstine, okruzi where okrug_id = id_okrug ORDER BY naziv_okruga';
// $stmt = $pdo->prepare($sql);
// $stmt->execute();
//$posts = $stmt->fetchAll();

//print_r($posts);
// $i = 1;
// while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
//     echo $i++." ".$row['naziv_okruga'] .' '.$row['naziv_opsine']. '<br>';
//   }


// $get = file_get_contents("http://opendata.mpn.gov.rs/get.php?dataset=opstine&lang=sr&term=json");

// $json = json_decode($get, true);

// echo "<pre>";
// print_r($json);

// foreach ($json as $uprava) {
// 	//echo $uprava['naziv_su'];
// 	$sql = 'INSERT INTO public.opstine(
// 	id_opsine, naziv_opsine, naziv_opsine_lat, okrug_id)
// 	VALUES (?, ?, ?, ?);';
// 	$stmt = $pdo->prepare($sql);
// 	$stmt->execute([$uprava['opstina_id'], $uprava['naziv_opstina'], $uprava['naziv_opstina_lat'], $uprava['okrug_id']]);
// }

// $sql = 'SELECT * FROM skolske_uprave';
// $stmt = $pdo->prepare($sql);
// $stmt->execute();

// while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
//     $uprave[$row['skolska_uprava']]= $row['id_skolske_uprave'];
// }

// print_r($uprave); 
// echo "<br>";

// $sql = 'SELECT * FROM okruzi';
// $stmt = $pdo->prepare($sql);
// $stmt->execute();

// while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
//     $okruzi[$row['naziv_okruga']]= $row['id_okrug'];
// }

// print_r($okruzi); 
// echo "<br>";

// $sql = 'SELECT * FROM opstine';
// $stmt = $pdo->prepare($sql);
// $stmt->execute();

// while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
//     $opstine[$row['naziv_opsine']]= $row['id_opsine'];
// }

// print_r($opstine); 
// echo "<br>";


// $get = file_get_contents("http://opendata.mpn.gov.rs/get.php?dataset=pk_osnovne15&lang=sr&term=json");

// $skole = json_decode($get, true);

// echo "<pre>";
// print_r($skole[0]); 
// $i=1;
// foreach ($skole as $skola) {
// 	$sql = 'INSERT INTO public.skole(id_skole, naziv, mat_br, adresa, telefon, sajt, uprava_id, okrug_id, opsina_id)
// 	VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);';
// 	$stmt = $pdo->prepare($sql);
// 	$stmt->execute([$skola['skola'], $skola['naziv'], $skola['mbr'], $skola['adresa'], $skola['tel'], $skola['www'], $uprave[$skola['skolska_uprada']], $okruzi[$skola['okrug']], $opstine[$skola['opstina']]]);

// 	//echo $uprave[$skola['skolska_uprada']]." ".$okruzi[$skola['okrug']]." ".$opstine[$skola['opstina']]."<br>" ;

// 	//echo $skola['skola']." ".
// }


// $slika = $row['img_name'];
// echo '<img src="'.$slika.'">';

// $sql = 'INSERT INTO pratece.mediji(mediji_id, naziv) VALUES (?, ?)';
// 	$stmt = $pdo->prepare($sql);
// 	$stmt->execute([1, 'stampani']);

echo $_SERVER['DOCUMENT_ROOT'];
?>


