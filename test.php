<?php

require_once("config/security.php");

function sm_encrypt($data)
{
	$encryption_key = base64_decode(ENC_KEY);
	$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
	$encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);

	return base64_encode($encrypted . '::' . $iv);
}

function sm_decrypt($data)
{
	$encryption_key = base64_decode(ENC_KEY);
	list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);

	return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
}

// $formati = ['A0 (841 x 1189)', 'A1 (594 × 841)', 'A2 (420 × 594)', 'A3 (297 × 420)', 'A4 (210 × 297)', 'A5 (148 × 210)', 'A6 (105 × 148)', 'A7 (74 × 105)', 'A8 (52 × 74)', 'A9 (37 × 52)', 'A10 (26 × 37)', 
// 			'B0 (1000 × 1414)', 'B1 (707 × 1000)', 'B2 (500 × 707)', 'B3 (353 × 500)', 'B4 (250 × 353)', 'B5 (176 × 250)', 'B6 (125 × 176)', 'B7 (88 × 125)', 'B8 (62 × 88)', 'B9 (44 × 62)', 'B10 (31 × 44)', 
// 			'C0 (917 × 1297)', 'C1 (648 × 917)', 'C2 (458 × 648)', 'C3 (324 × 458)', 'C4 (229 × 324)', 'C5 (162 × 229)', 'C6 (162 × 229)', 'C7 (81 × 114)', 'C8 (57 × 81)', 'C9 (40 × 57)', 'C10 (28 × 40)',
// 			'D0 (771 × 1090)', 'D1 (545 × 771)', 'D2 (385 × 545)', 'D3 (272 × 385)', 'D4 (192 × 272)', 'D5 (136 × 192)', 'D6 (96 × 136)', 'D7 (68 × 96)', 'D8 (48 × 68)'];

include ("config/db.php");
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
$i = 1;
// foreach ($formati as $format) {
	
// 	$sql = "INSERT INTO pratece.formati_izdanja(formatizdanja_id, naziv) VALUES (?, ?);";
// 	$stmt = $pdo->prepare($sql);
// 	$stmt->execute([$i++, $format]);

// }
// 	$pdo = null;

$predmeti_razredi = ['matematika' => ['1', '2', '3', '4', '5', '6', '7', '8'],
					'srpski jezik' => ['1', '2', '3', '4', '5', '6', '7', '8'],
					'svet oko nas' => ['1', '2'],
					'priroda i drustvo' => ['3', '4'],
					'likovna kultura' => ['1', '2', '3', '4', '5', '6', '7', '8'],
					'muzicka kultura' => ['1', '2', '3', '4', '5', '6', '7', '8'],
					'istorija' => ['5', '6', '7', '8'],
					'geografija' => ['5', '6', '7', '8'],
					'biologija' => ['5', '6', '7', '8'],
					'fizika' => ['6', '7', '8'],
					'hemija' => ['7', '8'],
					'tehnicko i informaticko obrazovanje' => ['5', '6', '7', '8'],
					'informatika i racunarstvo' => ['5', '6', '7', '8'],
					'lepo pisanje' => ['1'],
					'narodna tradicija' => ['1', '2', '3', '4'],
					'verska nastava - pravoslavna' => ['1', '2', '3', '4', '5', '6', '7', '8'],
					'verska nastava - katolicka' => ['1', '2', '3', '4', '5', '6', '7', '8'],
					'verska nastava - islam' => ['1', '2', '3', '4', '5', '6', '7', '8'],
					'srpski kao nematernji jezik' => ['1', '2', '3', '4', '5', '6', '7', '8'],
					

];
