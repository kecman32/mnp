<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
header("Access-Control-Allow-Headers', 'Content-Type,Accept");

session_start();

require_once("../includes/tools.php");
require_once("../includes/jwt_helper.php");
require_once("../config/security.php");
require_once("../includes/min_provera.php");


if (!isset($_POST['token'])) {
	exit();
	die();
}
else {
	
	$token = JWT::decode($_POST['token'], ENC_KEY);
	if (empty($token)) {
		$rtn = array('status' => 0, 'msg' => 'pogresan token');
		exit(json_encode($rtn));
	}
	else if($token->exp < time()) {
		$rtn = array('status' => 0, 'msg' => 'istekao token');
		exit(json_encode($rtn));
	}
	else {
		//echo $token->Министарство->citanje;
	}
} 

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
	// $jezik = $_POST['jezik'];
	$sql = "SELECT osnovna.predmeti.predmet_id, osnovna.predmeti.naziv 
			FROM osnovna.predmeti, osnovna.predmeti_po_razredima, osnovna.razredi
			WHERE osnovna.predmeti.predmet_id = osnovna.predmeti_po_razredima.predmet_id
			AND predmeti_po_razredima.razred_id = osnovna.razredi.razred_id
			AND osnovna.razredi.razred_id = ?";
	$data = [$razred_id];
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
	if (!checkIzdavaci('citanje')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}
	$komplet_id = $_POST['kompleti_id'];

	$sql = "SELECT kompleti_id, naziv, resenje_ministarstva, izdavac_id, predmet_id, razred_id, jezik_id, status
			FROM izdanja.kompleti
			WHERE kompleti_id = ?;";
	$data = [$komplet_id];

	return exec_and_return($sql, $data);		
}


function getIzdanjaIzKompleta() {
	if (!checkIzdavaci('citanje')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}
	$komplet_id = $_POST['kompleti_id'];

	$sql = "SELECT idanja_id, izdanja.izdanja.naziv, autori, izdanja.izdanja.resenje_ministarstva, naziv_udzb_jedinice, godina_izdanja
			FROM izdanja.izdanja, izdanja.komplati_izdanja, izdanja.kompleti
			WHERE izdanja.izdanja.idanja_id = izdanja.komplati_izdanja.izdanje_id
			AND izdanja.komplati_izdanja.komplet_id = izdanja.kompleti.kompleti_id
			AND izdanja.kompleti.kompleti_id = ?;";
	$data = [$komplet_id];

	return exec_and_return($sql, $data);
}




function delKomplet() {
	if (!checkIzdavaci('brisanje')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}
	$komplet_id = $_POST['kompleti_id'];

	$sql = "DELETE FROM izdanja.kompleti
			WHERE izdanja.kompleti.kompleti_id = :komplet_id;";
	$data = ['komplet_id' => $komplet_id];

	require_once ('../includes/db_connection.php');

	$stmt = $pdo->prepare($sql);
	if ($stmt->execute($data)) {

		$rtn = array('status' => 1, 'msg' => 'Uspesno izbrisan komplet !');
	}
	else {
		$rtn = array('status' => 0, 'msg' => 'Problem sa bazom. Probajte ponovo !');
	}

	exit(json_encode($rtn));	
}


function setIzdavac() {
	if (!checkIzdavaci('izmena')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}
	if (!isset($_POST['naziv']) || !isset($_POST['pib']) || !isset($_POST['maticni_broj']) || !isset($_POST['grad_id']) || !isset($_POST['lozinka']) || !isset($_POST['adresa']) || !isset($_POST['email']) || !isset($_POST['telefon'])) {

		$rtn = array('status' => 0, 'msg' => 'Nisu poslata sva polja !');
		exit(json_encode($rtn));
	}
	else if (empty($_POST['naziv']) || empty($_POST['pib']) || empty($_POST['maticni_broj']) || empty($_POST['grad_id']) || empty($_POST['lozinka'])) {

		$rtn = array('status' => 0, 'msg' => 'Popunite sva polja !');
		exit(json_encode($rtn));
	}
	else {
		$naziv = $_POST['naziv'];
		$pib = $_POST['pib'];
		$maticni_broj = $_POST['maticni_broj'];
		$grad_id = $_POST['grad_id'];
		$lozinka = $_POST['lozinka'];
		$adresa = $_POST['adresa'];
		$email = $_POST['email'];
		$telefon = $_POST['telefon'];

		require_once ('../includes/db_connection.php');

		$sql = "SELECT izdavac_id FROM izdavaci.izdavaci WHERE maticni_broj = ? OR email = ?;";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([$maticni_broj, $email]);
		$count = $stmt->rowCount();

		if ($count > 0) {
			$rtn = array('status' => 0, 'msg' => 'Maticni broj i Email moraju biti jedinstveni !');
			exit(json_encode($rtn));
		}
		else {
			$sql = 'INSERT INTO izdavaci.izdavaci(naziv, "PIB", maticni_broj, grad_id, lozinka, adresa, email, telefon)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?);';
			$data = [$naziv, $pib, $maticni_broj, $grad_id, sm_encrypt($lozinka), $adresa, $email, $telefon];


			$stmt = $pdo->prepare($sql);
			if ($stmt->execute($data)) {
				
				$rtn = array('status' => 1, 'msg' => 'Uspesno unet izdavac !');
				exit(json_encode($rtn));
			}
			else {
				
				$rtn = array('status' => 0, 'msg' => 'Doslo je do greske !');
				exit(json_encode($rtn));
			}
		}

		

	}

}

function getIzdavac() {
	if (!checkIzdavaci('citanje')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}
	if (!isset($_POST['izdavac_id']) || empty($_POST['izdavac_id'])) {
		$rtn = array('status' => 0, 'msg' => 'Nisu poslati svi podaci !');
		exit(json_encode($rtn));
	}
	else {
		$izdavac_id = $_POST['izdavac_id'];
		$sql = 'SELECT izdavac_id, izdavaci.izdavaci.naziv, "PIB", maticni_broj, grad_id, adresa, email, telefon
				FROM izdavaci.izdavaci
				WHERE izdavac_id = ?;';
		$data = [$izdavac_id];
		return exec_and_return($sql, $data);
	}
}


function editIzdavac() {
	if (!checkIzdavaci('izmena')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}
	if (!isset($_POST['naziv']) || !isset($_POST['pib']) || !isset($_POST['maticni_broj']) || !isset($_POST['grad_id']) || !isset($_POST['adresa']) || !isset($_POST['email']) || !isset($_POST['telefon']) || !isset($_POST['izdavac_id'])) {

		$rtn = array('status' => 0, 'msg' => 'Nisu poslata sva polja !');
		
	}
	else if (empty($_POST['naziv']) || empty($_POST['pib']) || empty($_POST['maticni_broj']) || empty($_POST['grad_id']) || empty($_POST['izdavac_id'])) {

		$rtn = array('status' => 0, 'msg' => 'Popunite sva polja !');
		
	}
	else {
		$naziv = $_POST['naziv'];
		$pib = $_POST['pib'];
		$maticni_broj = $_POST['maticni_broj'];
		$grad_id = $_POST['grad_id'];
		//$lozinka = $_POST['lozinka'];
		$adresa = $_POST['adresa'];
		$email = $_POST['email'];
		$telefon = $_POST['telefon'];
		$izdavac_id = $_POST['izdavac_id'];

		require_once ('../includes/db_connection.php');

		$sql = "SELECT izdavac_id 
				FROM izdavaci.izdavaci 
				WHERE (maticni_broj = ? AND izdavac_id <> ?)
				OR (email = ? AND izdavac_id <> ?);";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([$maticni_broj, $izdavac_id, $email, $izdavac_id]);
		$count = $stmt->rowCount();

		if ($count > 0) {
			$rtn = array('status' => 0, 'msg' => 'Vec postoji izdavac sa tim maticnim brojem ili email-om!');
			
		}
		else {
			$sql = 'UPDATE izdavaci.izdavaci
					SET naziv=?, "PIB"=?, maticni_broj=?, grad_id=?, adresa=?, email=?, telefon=?
					WHERE izdavac_id=?;';
			$data = [$naziv, $pib, $maticni_broj, $grad_id, $adresa, $email, $telefon, $izdavac_id];

			$stmt = $pdo->prepare($sql);
			if ($stmt->execute($data)) {
				
				$rtn = array('status' => 1, 'msg' => 'Uspesno editovan izdavac !');
			}
			else {
				
				$rtn = array('status' => 0, 'msg' => 'Doslo je do greske !');
			}
		}

		

	}
	exit(json_encode($rtn));
}


function delIzdavac() {
	if (!checkIzdavaci('brisanje')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}
	if (!isset($_POST['izdavac_id'])) {
		$rtn = array('status' => 0, 'msg' => 'Nisu poslati svi podaci !');
	}
	else if (empty($_POST['izdavac_id'])) {
		$rtn = array('status' => 0, 'msg' => 'Izaberite izdavaca za brisanje !');
	}
	else {
		$izdavac_id = $_POST['izdavac_id'];
		require_once ('../includes/db_connection.php');

		$sql = "DELETE FROM izdavaci.izdavaci
				WHERE izdavac_id = ?;";
		$data = [$izdavac_id];
		
		$stmt = $pdo->prepare($sql);
		if ($stmt->execute($data)) {
			
			$rtn = array('status' => 1, 'msg' => 'Uspesno izbrisan izdavac !');
			
		}
		else {
			
			$rtn = array('status' => 0, 'msg' => 'Doslo je do greske !');
			
		}
	}
	exit(json_encode($rtn));
}


function getIzdavaci() {
	if (!checkIzdavaci('citanje')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}
	if (!isset($_POST['pretraga'])) {
		$rtn = array('status' => 0, 'msg' => 'Nisu poslati svi podaci !');
		exit(json_encode($rtn));
	}
	else {
		
		$pretraga = "%".$_POST['pretraga']."%";
		$sql = 'SELECT izdavac_id, izdavaci.izdavaci.naziv, "PIB", maticni_broj, pratece.gradovi.naziv as grad, adresa, email, telefon
				FROM izdavaci.izdavaci, pratece.gradovi
				WHERE izdavaci.izdavaci.grad_id = pratece.gradovi.grad_id
				AND (izdavaci.izdavaci.naziv LIKE :pretraga
					OR pratece.gradovi.naziv LIKE :pretraga
					OR adresa LIKE :pretraga
					OR "PIB" LIKE :pretraga
					OR email LIKE :pretraga
					OR maticni_broj LIKE :pretraga);';
		$data = ['pretraga' => $pretraga];
		return exec_and_return($sql, $data);
	}
}


function setRola() {
	if (!checkMinistarstvo('izmena')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}


}

function getSekcije() {
	if (!checkMinistarstvo('citanje')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}

	$sql = "SELECT sekcijeaplikacije_id, naziv
			FROM ministarstvo.sekcije_aplikacije;";
	return exec_and_return($sql);
}


function getRole() {
	if (!checkMinistarstvo('citanje')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}

	$sql = "SELECT rola_id, naziv
			FROM ministarstvo.role;";
	return exec_and_return($sql);
}

function setOperater() {
	if (!checkMinistarstvo('izmena')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}

	if (!isset($_POST['korisnicko_ime']) || !isset($_POST['lozinka']) || !isset($_POST['ime']) || !isset($_POST['prezime']) || !isset($_POST['email_adresa']) || !isset($_POST['rola_id'])) {

		$rtn = array('status' => 0, 'msg' => 'Nisu poslata sva polja !');
	}
	else if (empty($_POST['korisnicko_ime']) || empty($_POST['lozinka']) || empty($_POST['ime']) || empty($_POST['prezime']) || empty($_POST['email_adresa']) || empty($_POST['rola_id'])) {

		$rtn = array('status' => 0, 'msg' => 'Morate popuniti sva polja !');
	}
	else if (strlen($_POST['lozinka']) < 6) {

		$rtn = array('status' => 0, 'msg' => 'Lozinka mora biti minimum 6 karaktera !');
	}
	else if (!filter_var($_POST['email_adresa'], FILTER_VALIDATE_EMAIL)) {
		
		$rtn = array('status' => 0, 'msg' => 'Nepravilan format Email-a !');
	}
	else {

		$korisnicko_ime = $_POST['korisnicko_ime'];
		$lozinka = $_POST['lozinka'];
		$hash = password_hash($lozinka, PASSWORD_BCRYPT);
		$ime = $_POST['ime'];
		$prezime = $_POST['prezime'];
		$email_adresa = $_POST['email_adresa'];
		$rola_id = $_POST['rola_id'];

		require_once ('../includes/db_connection.php');

		$sql = "SELECT operateri_id 
				FROM ministarstvo.operateri 
				WHERE korisnicko_ime = ?
				OR email_adresa = ?;";
		$data = [$korisnicko_ime, $email_adresa];
		
		$stmt = $pdo->prepare($sql);
		$stmt->execute($data);
		$count = $stmt->rowCount();

		if ($count > 0) {

			$rtn = array('status' => 0, 'msg' => 'Korisnicko ime i Email moraju biti jedinstveni !');
		}
		else {

			$sql = "INSERT INTO ministarstvo.operateri(korisnicko_ime, lozinka, ime, prezime, email_adresa, rola_id)
					VALUES (?, ?, ?, ?, ?, ?);";
			$data = [$korisnicko_ime, $hash, $ime, $prezime, $email_adresa, $rola_id];
		
			$stmt = $pdo->prepare($sql);

			if ($stmt->execute($data)) {
			
				$rtn = array('status' => 1, 'msg' => 'Uspesno unesen novi korisnik !');
				//exit(json_encode($rtn));
			}
			else {
				
				$rtn = array('status' => 0, 'msg' => 'Doslo je do greske !');
				//exit(json_encode($rtn));
			}
		}
	}

	exit(json_encode($rtn));
}


function getOperater() {

	if (!checkMinistarstvo('citanje')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}
	if (!isset($_POST['operateri_id'])) {
		$rtn = array('status' => 0, 'msg' => 'Nisu poslata sva polja !');
		exit(json_encode($rtn));
	}
	else {
		$operateri_id = $_POST['operateri_id'];
		$sql = "SELECT operateri_id, korisnicko_ime, ime, prezime, email_adresa, rola_id
				FROM ministarstvo.operateri
				WHERE operateri_id = ?;";
		$data = [$operateri_id];

		return exec_and_return($sql, $data);
	}
}


function getOperateri() {
	
	if (!checkMinistarstvo('citanje')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}

	if (!isset($_POST['pretraga']) || !isset($_POST['rola_id']) || !isset($_POST['sekcijeaplikacije_id'])) {
		$rtn = array('status' => 0, 'msg' => 'Nisu poslata sva polja !');
		exit(json_encode($rtn));
	}
	else {
		$_POST['rola_id'] > 0 ? $rola_id = $_POST['rola_id'] : $rola_id = "%";
		$_POST['sekcijeaplikacije_id'] > 0 ? $sekcija = $_POST['sekcijeaplikacije_id'] : $sekcija = "%";
		$pretraga = "%".$_POST['pretraga']."%";

		require_once ('../includes/db_connection.php');
		$sql = "SELECT DISTINCT rola_id 
				FROM ministarstvo.prava_pristupa 
				WHERE sekcijaaplikacije_id::text LIKE ?;";
		$data = [$sekcija];
		$stmt = $pdo->prepare($sql);
		$stmt->execute($data);
		$result = $stmt->fetchAll();
		$role = [];
		foreach ($result as $rola) {
			$role[] = $rola->rola_id;
		}
		//print_r($role);
		$in  = str_repeat('?,', count($role) - 1) . '?';
		//echo $in;

		$sql = "SELECT operateri_id, korisnicko_ime, ime, prezime, email_adresa, ministarstvo.role.naziv
				FROM ministarstvo.operateri, ministarstvo.role
				WHERE ministarstvo.operateri.rola_id = ministarstvo.role.rola_id
				AND ministarstvo.operateri.rola_id::text LIKE ?
				AND ministarstvo.operateri.rola_id IN ($in)
				AND (korisnicko_ime LIKE ?
					OR ime LIKE ?
					OR prezime LIKE ?
					OR email_adresa LIKE ?);";
		$data = array_merge([$rola_id], $role, [$pretraga, $pretraga, $pretraga, $pretraga]);
		
		$stmt = $pdo->prepare($sql);
		$stmt->execute($data);
		$result = $stmt->fetchAll();
		return $result;
	}

}


function editOperater() {

	if (!checkMinistarstvo('izmena')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}

	if (!isset($_POST['korisnicko_ime']) || !isset($_POST['ime']) || !isset($_POST['prezime']) || !isset($_POST['email_adresa']) || !isset($_POST['rola_id']) || !isset($_POST['operateri_id'])) {

		$rtn = array('status' => 0, 'msg' => 'Nisu poslata sva polja !');
	}
	else if (empty($_POST['korisnicko_ime']) || empty($_POST['ime']) || empty($_POST['prezime']) || empty($_POST['email_adresa']) || empty($_POST['rola_id'])) {

		$rtn = array('status' => 0, 'msg' => 'Morate popuniti sva polja !');
	}
	else if (!filter_var($_POST['email_adresa'], FILTER_VALIDATE_EMAIL)) {
		
		$rtn = array('status' => 0, 'msg' => 'Nepravilan format Email-a !');
	}
	else {

		$korisnicko_ime = $_POST['korisnicko_ime'];
		$ime = $_POST['ime'];
		$prezime = $_POST['prezime'];
		$email_adresa = $_POST['email_adresa'];
		$rola_id = $_POST['rola_id'];
		$operateri_id = $_POST['operateri_id'];

		require_once ('../includes/db_connection.php');

		$sql = "SELECT operateri_id 
				FROM ministarstvo.operateri 
				WHERE (korisnicko_ime = ? AND operateri_id <> ?)
				OR (email_adresa = ? AND operateri_id <> ?);";
		$data = [$korisnicko_ime, $operateri_id, $email_adresa, $operateri_id];
		
		$stmt = $pdo->prepare($sql);
		$stmt->execute($data);
		$count = $stmt->rowCount();

		if ($count > 0) {

			$rtn = array('status' => 0, 'msg' => 'Korisnicko ime i Email moraju biti jedinstveni !');
		}
		else {

			$sql = "UPDATE ministarstvo.operateri
					SET korisnicko_ime=?, ime=?, prezime=?, email_adresa=?, rola_id=?
					WHERE operateri_id = ?;";
			$data = [$korisnicko_ime, $ime, $prezime, $email_adresa, $rola_id, $operateri_id];
		
			$stmt = $pdo->prepare($sql);

			if ($stmt->execute($data)) {
			
				$rtn = array('status' => 1, 'msg' => 'Uspesno editovan korisnik !');
				//exit(json_encode($rtn));
			}
			else {
				
				$rtn = array('status' => 0, 'msg' => 'Doslo je do greske !');
				//exit(json_encode($rtn));
			}
		}
	}

	exit(json_encode($rtn));
}


function delOperater() {
	if (!checkMinistarstvo('brisanje')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}
	if (!isset($_POST['operateri_id'])) {
		$rtn = array('status' => 0, 'msg' => 'Nisu poslata sva polja !');
	}
	else {
		$operateri_id = $_POST['operateri_id'];
		require_once ('../includes/db_connection.php');
		$sql = "DELETE FROM ministarstvo.operateri
				WHERE operateri_id = ? RETURNING operateri_id;";
		$data = [$operateri_id];
		$stmt = $pdo->prepare($sql);

		if ($stmt->execute($data)) {
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			if (empty($result['operateri_id'])) {
				$rtn = array('status' => 0, 'msg' => 'Doslo je do greske pri brisanju!');
			}
			else {

				$rtn = array('status' => 1, 'msg' => 'Uspesno izbrisan korisnik !');
			}
			//exit(json_encode($rtn));
		}
		else {
			
			$rtn = array('status' => 0, 'msg' => 'Doslo je do greske u bazi !');
			//exit(json_encode($rtn));
		}
	}
	exit(json_encode($rtn));
}



switch ($_POST['funct']) {
	
	case 'get-razredi':
		$result = getRazredi();
		exit(json_encode($result));
		break;

	case 'get-jezici':
		$result = getJezici();
		exit(json_encode($result));
		break;

	case 'get-predmeti':
		$result = getPredmeti();
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

	case 'get-izdanja-iz-kompleta':
		$result = getIzdanjaIzKompleta();
		exit(json_encode($result));
		break;

	case 'del-komplet':
		delKomplet();
		break;

	case 'set-izdavac':
		setIzdavac();
		break;

	case 'edit-izdavac':
		editIzdavac();
		break;

	case 'del-izdavac':
		delIzdavac();
		break;

	case 'get-izdavac':
		$result = getIzdavac();
		exit(json_encode($result));
		break;

	case 'get-izdavaci':
		$result = getIzdavaci();
		exit(json_encode($result));
		break;

	case 'get-sekcije':
		$result = getSekcije();
		exit(json_encode($result));
		break;

	case 'get-role':
		$result = getRole();
		exit(json_encode($result));
		break;

	case 'set-operater':
		setOperater();
		break;

	case 'get-operater':
		$result = getOperater();
		exit(json_encode($result));
		break;

	case 'get-operateri':
		$result = getOperateri();
		exit(json_encode($result));
		break;

	case 'edit-operater':
		editOperater();
		break;

	case 'del-operater':
		delOperater();
		break;

	default:
		$result = ['status' => 0, 'msg' => 'unknown function'];
		exit(json_encode($result));
		die();
		break;
}