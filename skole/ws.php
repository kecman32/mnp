<?php 


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
header("Access-Control-Allow-Headers', 'Content-Type,Accept");

session_start();

require_once("../includes/tools.php");
require_once("../includes/jwt_helper.php");
require_once("../config/security.php");
require_once("../includes/min_provera.php");

// if (!isset($_POST['token'])) {
// 	exit();
// 	die();
// }
// else {
	
// 	$token = JWT::decode($_POST['token'], ENC_KEY);
// 	if (empty($token)) {
// 		$rtn = array('status' => 0, 'msg' => 'pogresan token');
// 		exit(json_encode($rtn));
// 	}
// 	else if($token->exp < time()) {
// 		$rtn = array('status' => 0, 'msg' => 'istekao token');
// 		exit(json_encode($rtn));
// 	}
// 	else {
// 		$skola_id = $token->skola_id;
// 	}
// } 

$skola_id = 1;

function exec_and_return($sql, $data=[]) {
	require_once ('../includes/db_connection.php');

		$stmt = $pdo->prepare($sql);
		$stmt->execute($data);
		$result = $stmt->fetchAll();

		$pdo = null;
		return $result;
}


// Svi razredi osnovne skole
function getRazredi() {
	$sql = "SELECT * FROM osnovna.razredi ORDER BY razred_id ASC";
	return exec_and_return($sql);
}

// Svi jezici nastave osnovne skole
function getJezici() {
	$sql = "SELECT * FROM pratece.jezici ORDER BY jezik_id ASC";
	return exec_and_return($sql);
}

function getPredmeti() {
	$razred_id = $_POST['razred_id'];
	$jezik_id = $_POST['jezik_id'];
	$sql = "SELECT osnovna.predmeti.predmet_id, osnovna.predmeti.naziv 
			FROM osnovna.predmeti, osnovna.predmeti_po_razredima
			WHERE osnovna.predmeti.predmet_id = osnovna.predmeti_po_razredima.predmet_id
			AND predmeti_po_razredima.razred_id = ?
			AND predmeti_po_razredima.jezik_id = ?
			AND osnovna.predmeti.aktivan = 1;";
	$data = [$razred_id, $jezik_id];
	return exec_and_return($sql, $data);
}

//Svi mediji
function getMediji() {
	$sql = "SELECT * FROM pratece.mediji ORDER BY mediji_id ASC";
	return exec_and_return($sql);
}


//Svi formati
function getFormati() {
	$sql = "SELECT * FROM pratece.formati_izdanja ORDER BY formatizdanja_id ASC";
	return exec_and_return($sql);
}




function getGradovi() {
	$sql = "SELECT grad_id, naziv FROM pratece.gradovi;";
	return exec_and_return($sql);	
}


function getGrad() {
	$grad = $_POST['grad_id'];
	$sql = "SELECT grad_id, naziv 
			FROM pratece.gradovi
			WHERE grad_id = ?;";
	$data = [$grad];
	return exec_and_return($sql, $data);	
}


function getKomplet() {

	$komplet_id = $_POST['kompleti_id'];

	$sql = "SELECT kompleti_id, izdanja.kompleti.naziv, resenje_ministarstva, izdanja.kompleti.izdavac_id, izdavaci.izdavaci.naziv as izdavac_naziv, predmet_id, razred_id, jezik_id, status
			FROM izdanja.kompleti, izdavaci.izdavaci
			WHERE kompleti_id = ?
			AND izdanja.kompleti.izdavac_id = izdavaci.izdavaci.izdavac_id;";
	$data = [$komplet_id];

	return exec_and_return($sql, $data);		
}


function getKompleti() {

	if (!isset($_POST['razred_id']) || !isset($_POST['jezik_id']) || !isset($_POST['predmet_id'])) {
		$rtn = array('status' => 0, 'msg' => 'Nisu poslata sva polja !');
		exit(json_encode($rtn));
	}
	else {

		$_POST['razred_id'] > 0 ? $razred = $_POST['razred_id'] :	$razred = "%";
		$_POST['jezik_id'] > 0 ? $jezik_id = $_POST['jezik_id'] : $jezik_id = "%";
		$_POST['predmet_id'] > 0 ? $predmet_id = $_POST['predmet_id'] : $predmet_id = "%";
		

		$sql = "SELECT kompleti_id, izdanja.kompleti.naziv, resenje_ministarstva, izdanja.kompleti.izdavac_id, izdavaci.izdavaci.naziv as izdavac_naziv, predmet_id, razred_id, jezik_id, status
				FROM izdanja.kompleti, izdavaci.izdavaci
				WHERE izdanja.kompleti.izdavac_id = izdavaci.izdavaci.izdavac_id
				AND status = 1
				AND razred_id::text LIKE :razred_id
				AND jezik_id::text LIKE :jezik_id
				AND predmet_id::text LIKE :predmet_id
				ORDER BY izdavaci.izdavaci.naziv DESC;";

		$data = ['razred_id' => $razred, 'jezik_id' => $jezik_id, 'predmet_id' => $predmet_id];
		return exec_and_return($sql, $data);
	}
	

}


function getIzdanjaIzKompleta() {
	
	$komplet_id = $_POST['kompleti_id'];

	$sql = "SELECT idanja_id, izdanja.izdanja.naziv, autori, izdanja.izdanja.resenje_ministarstva, naziv_udzb_jedinice, godina_izdanja
			FROM izdanja.izdanja, izdanja.komplati_izdanja, izdanja.kompleti
			WHERE izdanja.izdanja.idanja_id = izdanja.komplati_izdanja.izdanje_id
			AND izdanja.komplati_izdanja.komplet_id = izdanja.kompleti.kompleti_id
			AND izdanja.kompleti.kompleti_id = ?;";
	$data = [$komplet_id];

	return exec_and_return($sql, $data);
}


function setIzbor() {

	if (!isset($_POST['kompleti_id']) || !isset($_POST['izdanja'])) {
		$rtn = array('status' => 0, 'msg' => 'Nisu poslata sva polja !');
		exit(json_encode($rtn));
	}
	else {
		$komplet_id = $_POST['kompleti_id'];
		$izdanja = json_decode($_POST['izdanja']);

		$sql = "SELECT predmet_id, razred_id, jezik_id
				FROM izdanja.kompleti
				WHERE kompleti_id = ?
				AND status = 1;";
		$data = [$komplet_id];

		require_once ('../includes/db_connection.php');
		$stmt = $pdo->prepare($sql);
		$stmt->execute($data);
		$count = $stmt->rowCount();

		if ($count > 0) {
			
			$komplet_info = $stmt->fetch(PDO::FETCH_ASSOC);
			global $skola_id;
			$sql = "SELECT COUNT(izborkompleta_id) FROM skole.izborkompleta
					WHERE skola_id = ?
					AND komplet_id =?;";
			$data = [$skola_id, $komplet_id];
			$stmt = $pdo->prepare($sql);
			$stmt->execute($data);
			$res = $stmt->fetch(PDO::FETCH_ASSOC);
			$count = $res['count'];
			//echo $count;
			if ($count > 0) {
				$rtn = array('status' => 0, 'msg' => 'Ovaj komplet je vec izabran !');
				exit(json_encode($rtn));
			}
			else {

				$sql = "SELECT COUNT(izborkompleta_id) FROM skole.izborkompleta
						WHERE skola_id = ?
						AND razred_id = ?
						AND jezik_id = ?
						AND predmet_id =?;";
				$data = [$skola_id, $komplet_info['razred_id'], $komplet_info['jezik_id'], $komplet_info['predmet_id']];
				$stmt = $pdo->prepare($sql);
				$stmt->execute($data);
				$count = $stmt->rowCount();

				if ($count >= 2) {
					$rtn = array('status' => 0, 'msg' => 'Vec je izabran maksimalan broj kompleta !');
					exit(json_encode($rtn));
				}
				else {

					$sql = "INSERT INTO skole.izborkompleta(skola_id, komplet_id, razred_id, jezik_id, predmet_id)
							VALUES (?, ?, ?, ?, ?) RETURNING izborkompleta_id;";
					$data = [$skola_id, $komplet_id, $komplet_info['razred_id'], $komplet_info['jezik_id'], $komplet_info['predmet_id']];
					$stmt = $pdo->prepare($sql);
					$stmt->execute($data);
					$result = $stmt->fetch(PDO::FETCH_ASSOC);
					$izborkompleta_id = $result['izborkompleta_id'];

					foreach ($izdanja as $izdanje) {
						$sql = "INSERT INTO skole.izborizdanja(izborkompleta_id, izdanje_id)
								VALUES (?, ?);";
						$data = [$izborkompleta_id, $izdanje];
						$stmt = $pdo->prepare($sql);
						$stmt->execute($data);	
					}
					$rtn = array('status' => 1, 'msg' => 'Uspesno izabran komplet !');
					exit(json_encode($rtn));
					
				}
			}




		}
		else {
			$rtn = array('status' => 0, 'msg' => 'Doslo je do greska, probajte ponovo !');
			exit(json_encode($rtn));
		}


	}
}


function getIzborKompleta() {
	if (!isset($_POST['razred_id']) || !isset($_POST['jezik_id']) || !isset($_POST['predmet_id'])) {
		$rtn = array('status' => 0, 'msg' => 'Nisu poslata sva polja !');
		exit(json_encode($rtn));
	}
	else {
		$_POST['razred_id'] > 0 ? $razred_id = $_POST['razred_id'] :	$razred_id = "%";
		$_POST['jezik_id'] > 0 ? $jezik_id = $_POST['jezik_id'] : $jezik_id = "%";
		$_POST['predmet_id'] > 0 ? $predmet_id = $_POST['predmet_id'] : $predmet_id = "%";
		global $skola_id;

		$sql = "SELECT skole.izborkompleta.izborkompleta_id,
						skole.izborkompleta.komplet_id,
						izdanja.kompleti.naziv as komplet_naziv,
						resenje_ministarstva,
						izdavaci.izdavaci.naziv as izdavac_naziv

				FROM skole.izborkompleta, izdanja.kompleti, izdavaci.izdavaci
				WHERE izdanja.kompleti.kompleti_id = skole.izborkompleta.komplet_id
				AND izdavaci.izdavaci.izdavac_id = izdanja.kompleti.izdavac_id
				AND skole.izborkompleta.razred_id::text LIKE :razred_id
				AND skole.izborkompleta.jezik_id::text LIKE :jezik_id
				AND skole.izborkompleta.predmet_id::text LIKE :predmet_id
				AND skole.izborkompleta.skola_id = :skola_id;";

		$data = ['razred_id' => $razred_id, 'jezik_id' => $jezik_id, 'predmet_id' => $predmet_id, 'skola_id' => $skola_id];
		return exec_and_return($sql, $data);


	}


}


function getIzborIzdanja() {
	if (!isset($_POST['izborkompleta_id'])) {
		$rtn = array('status' => 0, 'msg' => 'Nisu poslata sva polja !');
		exit(json_encode($rtn));
	}
	else {
		$izborkompleta_id = $_POST['izborkompleta_id'];
		$sql = "SELECT komplet_id FROM skole.izborkompleta WHERE izborkompleta_id = ?;";
		$data = [$izborkompleta_id];
		require_once ('../includes/db_connection.php');
		$stmt = $pdo->prepare($sql);
		$stmt->execute($data);
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		$komplet_id = $res['komplet_id'];

		$sql = "SELECT izdanja.izdanja.idanja_id, izdanja.izdanja.naziv, autori, izdanja.izdanja.resenje_ministarstva, naziv_udzb_jedinice, izdanja.izdanja.formatizdanja_id, pratece.formati_izdanja.naziv as format, broj_strana, godina_izdanja, izdanja.izdanja.mediji_id, pratece.mediji.naziv as mediji
				FROM izdanja.izdanja, pratece.formati_izdanja, pratece.mediji, izdanja.komplati_izdanja
				WHERE pratece.formati_izdanja.formatizdanja_id = izdanja.izdanja.formatizdanja_id
				AND pratece.mediji.mediji_id = izdanja.izdanja.mediji_id
				AND izdanja.komplati_izdanja.izdanje_id = izdanja.izdanja.idanja_id
				AND izdanja.komplati_izdanja.komplet_id = ?;";
		$data = [$komplet_id];
		$stmt = $pdo->prepare($sql);
		$stmt->execute($data);
		$sva_izdanja = $stmt->fetchAll();
		//print_r($sva_izdanja);

		foreach ($sva_izdanja as $izdanje) {
			$izdanja_id = $izdanje->idanja_id;
			$sql = "SELECT COUNT(*)	FROM skole.izborizdanja
					WHERE izborkompleta_id = ?
					AND izdanje_id = ?;";
			$data = [$izborkompleta_id, $izdanja_id];
			$stmt = $pdo->prepare($sql);
			$stmt->execute($data);
			$res = $stmt->fetch(PDO::FETCH_ASSOC);
			$count = $res['count'];
			if ($count > 0) {
				$izdanje->obavezno = 'Да';
			}
			else {
				$izdanje->obavezno = 'Не';
			}
		}
		return $sva_izdanja;


	}
}


function delIzborKompleta() {
	if (!isset($_POST['izborkompleta_id'])) {
		$rtn = array('status' => 0, 'msg' => 'Nisu poslata sva polja !');
		
	}
	else {
		$izborkompleta_id = $_POST['izborkompleta_id'];
		$sql = "DELETE FROM skole.izborizdanja
				WHERE izborkompleta_id = ?;";
		$data = [$izborkompleta_id];
		require_once ('../includes/db_connection.php');
		$stmt = $pdo->prepare($sql);
		if ($stmt->execute($data)) {
			$sql = "DELETE FROM skole.izborkompleta
					WHERE izborkompleta_id = ?;";
			$stmt = $pdo->prepare($sql);
			if ($stmt->execute($data)) {
				$rtn = array('status' => 1, 'msg' => 'Uspesno izbrisan izabrani komplet !');
			}
		}

	}
	exit(json_encode($rtn));
}



switch ($_POST['funct']) {
	
	case 'get-razredi':
		$result = getRazredi();
		exit(json_encode($result));
		break;

	case 'get-razredi-svi':
		$result = getRazredi();
		$arr = ["razred_id" => 0, "naziv" => "Svi"];
		$obj = (object) $arr;
		array_unshift($result,$obj);
		exit(json_encode($result));
		break;

	case 'get-jezici':
		$result = getJezici();
		exit(json_encode($result));
		break;

	case 'get-jezici-svi':
		$result = getJezici();
		$arr = ["jezik_id" => 0, "naziv" => "Svi"];
		$obj = (object) $arr;
		array_unshift($result,$obj);
		exit(json_encode($result));
		break;

	case 'get-predmeti':
		$result = getPredmeti();
		exit(json_encode($result));
		break;

	case 'get-predmeti-svi':
		$result = getPredmeti();
		$arr = ["predmet_id" => 0, "naziv" => "Svi"];
		$obj = (object) $arr;
		array_unshift($result,$obj);
		exit(json_encode($result));
		break;

	case 'get-mediji':
		$result = getMediji();
		exit(json_encode($result));
		break;

	case 'get-formati':
		$result = getFormati();
		exit(json_encode($result));
		break;

	case 'get-gradovi':
		$result = getGradovi();
		exit(json_encode($result));
		break;

	case 'get-grad':
		$result = getGrad();
		exit(json_encode($result));
		break;

	case 'get-komplet':
		$result = getKomplet();
		exit(json_encode($result));
		break;

	case 'get-kompleti':
		$result = getKompleti();
		exit(json_encode($result));
		break;


	case 'get-izdanja-iz-kompleta':
		$result = getIzdanjaIzKompleta();
		exit(json_encode($result));
		break;


	case 'set-izbor':
		setIzbor();
		break;


	case 'get-izbor-kompleta':
		$result = getIzborKompleta();
		exit(json_encode($result));
		break;

	
	case 'get-izbor-izdanja':
		$result = getIzborIzdanja();
		exit(json_encode($result));
		break;

	case 'del-izbor-kompleta':
		delIzborKompleta();
		break;

	default:
		$result = ['status' => 0, 'msg' => 'unknown function'];
		exit(json_encode($result));
		die();
		break;
}