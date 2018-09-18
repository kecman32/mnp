<?php

session_start();


include_once("../includes/tools.php");
include_once("../includes/jwt_helper.php");
include_once("../config/security.php");
include_once("../config/token.php");

	
if (isset($_POST['username']) && isset($_POST['password'])) {
	
	//exit('test');
	require_once ('../includes/db_connection.php');

	$sql = "SELECT operater_id, skola_id, osoba_id, username, lozinka
			FROM skole.operateri
			WHERE username = ?;";
	
	$stmt = $pdo->prepare($sql);
	$stmt->execute([$_POST['username']]);
	$count = $stmt->rowCount();

	if ($count == 0) {

		$rtn = array('status' => 0,'msg' => 'Proverite vase podatke !');
	}
	else {
		
		$res = $stmt->fetchAll();
		$token = [];
		foreach ($res as $user) {
			if (password_verify($_POST['password'], $user->lozinka)) {

				$token['operater_id'] = $user->operater_id;
				$token['exp'] = time() + (TOKEN_EXP*3600);
				$token['osoba_id'] = $user->osoba_id;
				$token['skola_id'] = $user->skola_id;
				$skola_id = $user->skola_id;
				$sql = "SELECT skole.skole.naziv as skola, pratece.naselja.naziv as naselje
						FROM skole.skole, pratece.naselja
						WHERE skole.skole.naselje_id = pratece.naselja.naselje_id
						AND skole.skole.skola_id = ?;";
				$data = [$skola_id];
				$stmt = $pdo->prepare($sql);
				$stmt->execute($data);
				$skola = $stmt->fetch(PDO::FETCH_ASSOC);
				$token['skola_naziv'] = $skola['skola'];
				$token['naselje'] = $skola['naselje'];
				//print_r($token);
				$rtn = array('status' => 1, 'msg' => 'Uspesno logovanje !', 'token' => JWT::encode($token, ENC_KEY));
				$pdo = null;
				exit(json_encode($rtn));
			}
		}

		$rtn = array('status' => 0,'msg' => 'Proverite vase podatke !');


	}
}
else {
	
	$rtn = array('status' => 0, 'msg' => 'Popunite sva polja !');
	
}

$pdo = null;
exit(json_encode($rtn));
