<?php

session_start();


include_once("../includes/tools.php");
include_once("../includes/jwt_helper.php");
include_once("../config/security.php");
include_once("../config/token.php");

	
if (isset($_POST['username']) && isset($_POST['password'])) {
	
	//exit('test');
	require_once ('../includes/db_connection.php');

	$sql = "SELECT operateri_id, korisnicko_ime, lozinka, ime, prezime, email_adresa, rola_id
			FROM ministarstvo.operateri 
			WHERE korisnicko_ime = ?;";
	
	$stmt = $pdo->prepare($sql);
	$stmt->execute([$_POST['username']]);
	$count = $stmt->rowCount();

	if ($count == 0)
	{
		$rtn = array('status' => 0,'msg' => 'Proverite vase podatke !');
	}
	else if ($count == 1)
	{
		$row = $stmt->fetch(PDO::FETCH_ASSOC);


		if (password_verify($_POST['password'], $row['lozinka'])) {

		// echo "testttt";
		// exit();
			$token = [];
			$token['operateri_id'] = $row['operateri_id'];
			$token['ime'] = $row['ime'];
			$token['prezime'] = $row['prezime'];
			$token['exp'] = time() + (TOKEN_EXP*3600);
			$rola = $row['rola_id'];

			$sql = "SELECT ministarstvo.sekcije_aplikacije.naziv, ministarstvo.prava_pristupa.citanje, ministarstvo.prava_pristupa.izmena, ministarstvo.prava_pristupa.brisanje
					FROM ministarstvo.prava_pristupa, ministarstvo.sekcije_aplikacije
					WHERE ministarstvo.prava_pristupa.sekcijaaplikacije_id = ministarstvo.sekcije_aplikacije.sekcijeaplikacije_id
					AND ministarstvo.prava_pristupa.rola_id = ?;";
			$data = [$rola];

			$stmt = $pdo->prepare($sql);
			$stmt->execute($data);
			$sekcije = $stmt->fetchAll();

			foreach ($sekcije as $sekcija) {
				$sek =(array) $sekcija;
				//echo $sek['naziv'];
				$token[$sek['naziv']] = ['citanje' => $sek['citanje'], 'izmena' => $sek['izmena'], 'brisanje' => $sek['brisanje']];
			}

			$rtn = array('status' => 1, 'msg' => 'Uspesno logovanje !', 'token' => JWT::encode($token, ENC_KEY));
			

			
			
			
		}
		else {
			$rtn = array('status' => 0,'msg' => 'Proverite vase podatke !');
			exit(json_encode($rtn));
		}

	}
	else
	{
		$rtn = array('status' => 0, 'msg' => 'Greska u bazi');
		exit(json_encode($rtn));
	}
}
else {
	
	$rtn = array('status' => 0, 'msg' => 'Popunite sva polja !');
	
}

$pdo = null;
exit(json_encode($rtn));
