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
	$rtn = array('status' => 0, 'msg' => 'niste ulogovani');
	exit(json_encode($rtn));
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
		//print_r($token);
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
	if (!checkIzdavaci('citanje')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}
	$komplet_id = $_POST['kompleti_id'];

	$sql = "SELECT kompleti_id, izdanja.kompleti.naziv, resenje_ministarstva, izdanja.kompleti.izdavac_id, izdavaci.izdavaci.naziv as izdavac_naziv, predmet_id, razred_id, jezik_id, status
			FROM izdanja.kompleti, izdavaci.izdavaci
			WHERE kompleti_id = ?
			AND izdanja.kompleti.izdavac_id = izdavaci.izdavaci.izdavac_id;";
	$data = [$komplet_id];

	return exec_and_return($sql, $data);		
}


function getKompleti() {
	if (!checkIzdavaci('citanje')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}
	if (!isset($_POST['razred_id']) || !isset($_POST['jezik_id']) || !isset($_POST['predmet_id']) || !isset($_POST['status'])) {
		$rtn = array('status' => 0, 'msg' => 'Nisu poslata sva polja !');
		exit(json_encode($rtn));
	}
	else {

		$_POST['razred_id'] > 0 ? $razred = $_POST['razred_id'] :	$razred = "%";
		$_POST['jezik_id'] > 0 ? $jezik_nastave = $_POST['jezik_id'] : $jezik_nastave = "%";
		$_POST['predmet_id'] > 0 ? $predmet = $_POST['predmet_id'] : $predmet = "%";
		$_POST['status'] < 0 ? $status = "%" : $status = $_POST['status'];

		$sql = "SELECT kompleti_id, izdanja.kompleti.naziv, resenje_ministarstva, izdanja.kompleti.izdavac_id, izdavaci.izdavaci.naziv as izdavac_naziv, predmet_id, razred_id, jezik_id, status
				FROM izdanja.kompleti, izdavaci.izdavaci
				WHERE izdanja.kompleti.izdavac_id = izdavaci.izdavaci.izdavac_id
				AND status::text LIKE :status
				AND razred_id::text LIKE :razred_id
				AND jezik_id::text LIKE :jezik_id
				AND predmet_id::text LIKE :predmet_id
				ORDER BY razred_id DESC;";

		$data = ['status' => $status, 'razred_id' => $razred, 'jezik_id' => $jezik_nastave, 'predmet_id' => $predmet];
		return exec_and_return($sql, $data);
	}
	

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
		$sql = 'SELECT izdavac_id, izdavaci.izdavaci.naziv, "PIB", maticni_broj, pratece.gradovi.naziv as grad, izdavaci.izdavaci.grad_id, adresa, email, telefon
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


function getAllIzdavaci() {
	if (!checkIzdavaci('citanje')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}
	
	$sql = 'SELECT izdavac_id, izdavaci.izdavaci.naziv, "PIB", maticni_broj, pratece.gradovi.naziv as grad, adresa, email, telefon
			FROM izdavaci.izdavaci, pratece.gradovi
			WHERE izdavaci.izdavaci.grad_id = pratece.gradovi.grad_id ORDER BY izdavaci.izdavaci.naziv;';
	
	return exec_and_return($sql);
	
}


function setRola() {
	if (!checkMinistarstvo('izmena')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}
	if (!isset($_POST['naziv']) || !isset($_POST['sekcije'])) {
		$rtn = array('status' => 0, 'msg' => 'Nisu poslati svi podaci !');
		
	}
	else if (empty($_POST['naziv'])) {
		$rtn = array('status' => 0, 'msg' => 'Morate uneti naziv uloge !');
	}
	else {
		$naziv = $_POST['naziv'];
		$sekcije = json_decode($_POST['sekcije']);
		// print_r($sekcije);
		// foreach ($sekcije as $sekcija_id => $prava) {
		// 	echo $sekcija_id."<br>";
		// 	print_r($prava);
					
		// 		}
		// exit();

		$sql = "SELECT rola_id
				FROM ministarstvo.role
				WHERE naziv = ?;";
		$data = [$naziv];

		require_once ('../includes/db_connection.php');
		$stmt = $pdo->prepare($sql);
		$stmt->execute($data);
		$count = $stmt->rowCount();

		if ($count > 0) {

			$rtn = array('status' => 0, 'msg' => 'Vec postoji rola sa tim imenom !');
		}
		else {
			$sql = "INSERT INTO ministarstvo.role(naziv)
					VALUES (?) RETURNING rola_id;";
			$stmt = $pdo->prepare($sql);
			if ($stmt->execute($data)) {
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				$rola_id = $result['rola_id'];
				foreach ($sekcije as $sekcija_id => $prava) {
					$citanje = 0;
					$izmena =  0;
					$brisanje = 0;

					if ($prava->brisanje) {
						$citanje = 1;
						$izmena =  1;
						$brisanje = 1;
					}
					else if ($prava->izmena) {
						$citanje = 1;
						$izmena = 1;
					}
					else if ($prava->citanje) {
						$citanje = 1;
					}
					$sql = "INSERT INTO ministarstvo.prava_pristupa(rola_id, sekcijaaplikacije_id, citanje, izmena, brisanje)
							VALUES (?, ?, ?, ?, ?);";
					$data = [$rola_id, $sekcija_id, $citanje, $izmena, $brisanje];
					$stmt = $pdo->prepare($sql);
					$stmt->execute($data);
					
				}
				$rtn = array('status' => 1, 'msg' => 'Uspesno uneta rola !');
			
			}
			else {
				
				$rtn = array('status' => 0, 'msg' => 'Doslo je do greske u bazi !');
			}
		}
	}
	exit(json_encode($rtn));

}


function editRola() {
	if (!checkMinistarstvo('izmena')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}
	if (!isset($_POST['naziv']) || !isset($_POST['sekcije']) || !isset($_POST['rola_id'])) {
		$rtn = array('status' => 0, 'msg' => 'Nisu poslati svi podaci !');
		
	}
	else if (empty($_POST['naziv'])) {
		$rtn = array('status' => 0, 'msg' => 'Morate uneti naziv uloge !');
	}
	else {
		$naziv = $_POST['naziv'];
		$sekcije = json_decode($_POST['sekcije']);
		$rola_id = $_POST['rola_id'];

		$sql = "SELECT rola_id
				FROM ministarstvo.role
				WHERE naziv = ? AND rola_id <> ?;";
		$data = [$naziv, $rola_id];

		require_once ('../includes/db_connection.php');
		$stmt = $pdo->prepare($sql);
		$stmt->execute($data);
		$count = $stmt->rowCount();

		if ($count > 0) {

			$rtn = array('status' => 0, 'msg' => 'Vec postoji rola sa tim imenom !');
		}
		else {
			$sql = "UPDATE ministarstvo.role
					SET naziv=?
					WHERE rola_id=?;";
			$stmt = $pdo->prepare($sql);
			if ($stmt->execute($data)) {
				
				foreach ($sekcije as $sekcija_id => $prava) {
					$citanje = 0;
					$izmena =  0;
					$brisanje = 0;

					if ($prava->brisanje) {
						$citanje = 1;
						$izmena =  1;
						$brisanje = 1;
					}
					else if ($prava->izmena) {
						$citanje = 1;
						$izmena = 1;
					}
					else if ($prava->citanje) {
						$citanje = 1;
					}
					$sql = "UPDATE ministarstvo.prava_pristupa
							SET citanje=?, izmena=?, brisanje=?
							WHERE rola_id=? AND sekcijaaplikacije_id=?;";
					$data = [$citanje, $izmena, $brisanje, $rola_id, $sekcija_id];
					$stmt = $pdo->prepare($sql);
					$stmt->execute($data);
					
				}
				$rtn = array('status' => 1, 'msg' => 'Uspesno izmenjena rola !');
			
			}
			else {
				
				$rtn = array('status' => 0, 'msg' => 'Doslo je do greske u bazi !');
			}
		}
	}
	exit(json_encode($rtn));

}


function delRola() {
	if (!checkMinistarstvo('brisanje')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}
	if (!isset($_POST['rola_id'])) {
		$rtn = array('status' => 0, 'msg' => 'Nisu poslata sva polja !');
	}
	else {
		$rola_id = $_POST['rola_id'];
		$sql = "SELECT operateri_id 
				FROM ministarstvo.operateri
				WHERE rola_id = ?";
		$data = [$rola_id];
		require_once ('../includes/db_connection.php');
		$stmt = $pdo->prepare($sql);
		$stmt->execute($data);
		$count = $stmt->rowCount();

		if ($count > 0) {

			$rtn = array('status' => 0, 'msg' => 'Rola ne moze biti obrisana jer postoje operateri sa ovom rolom !');
		}
		else {
			$sql = "DELETE FROM ministarstvo.prava_pristupa
					WHERE rola_id = ?;";
			$stmt = $pdo->prepare($sql);
			$stmt->execute($data);
			$sql = "DELETE FROM ministarstvo.role
					WHERE rola_id = ?;";
			$stmt = $pdo->prepare($sql);
			$stmt->execute($data);
			$rtn = array('status' => 1, 'msg' => 'Uspesno izbrisana rola !');
		}
	}
	exit(json_encode($rtn));
}


function getRola() {
	if (!checkMinistarstvo('citanje')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}
	if (!isset($_POST['rola_id'])) {
		$rtn = array('status' => 0, 'msg' => 'Nisu poslata sva polja !');
	}
	else {
		$rola_id = $_POST['rola_id'];
		$sql = "SELECT * FROM ministarstvo.role WHERE rola_id = ?";
		$data = [$rola_id];
		require_once ('../includes/db_connection.php');
		$stmt = $pdo->prepare($sql);
		$stmt->execute($data);
		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		$sql = "SELECT ministarstvo.prava_pristupa.sekcijaaplikacije_id, ministarstvo.sekcije_aplikacije.naziv, citanje, izmena, brisanje
				FROM ministarstvo.prava_pristupa, ministarstvo.sekcije_aplikacije
				WHERE rola_id = ?
				AND ministarstvo.prava_pristupa.sekcijaaplikacije_id = ministarstvo.sekcije_aplikacije.sekcijeaplikacije_id;";
		$stmt = $pdo->prepare($sql);
		$stmt->execute($data);
		$result['sekcije'] = $stmt->fetchAll();

		$rtn = $result;

	}
	exit(json_encode($rtn));
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

		$sql = "SELECT operateri_id, korisnicko_ime, ime, prezime, email_adresa, ministarstvo.role.naziv, ministarstvo.operateri.rola_id
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


function pregledRola() {

	if (!checkMinistarstvo('citanje')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}
	if (!isset($_POST['pretraga'])) {
		$rtn = array('status' => 0, 'msg' => 'Nisu poslata sva polja !');
	}
	else {

		$pretraga = "%".$_POST['pretraga']."%";

		$sql = "SELECT rola_id, naziv
				FROM ministarstvo.role
				WHERE naziv LIKE ?
				ORDER BY naziv;";
		$data = [$pretraga];
		require_once ('../includes/db_connection.php');
		
		$stmt = $pdo->prepare($sql);

		if ($stmt->execute($data)) {
			$role = $stmt->fetchAll();
			foreach ($role as $rola) {
				$sql = "SELECT COUNT(operateri_id) as br
						FROM ministarstvo.operateri
						WHERE rola_id = ?";
				$data = [$rola->rola_id];
				$stmt = $pdo->prepare($sql);
				$stmt->execute($data);
				$broj = $stmt->fetch(PDO::FETCH_ASSOC);
				$rola->broj_operatera = $broj['br'];

				$sql = "SELECT ministarstvo.prava_pristupa.sekcijaaplikacije_id, ministarstvo.sekcije_aplikacije.naziv, citanje, izmena, brisanje
				FROM ministarstvo.prava_pristupa, ministarstvo.sekcije_aplikacije
				WHERE rola_id = ?
				AND ministarstvo.prava_pristupa.sekcijaaplikacije_id = ministarstvo.sekcije_aplikacije.sekcijeaplikacije_id;";
				$stmt = $pdo->prepare($sql);
				$stmt->execute($data);
				$rola->sekcije = $stmt->fetchAll();
			}
			$rtn = array('status' => 1, 'msg' => 'uspesno', 'result' => $role);
		}
		else {
		$rtn = array('status' => 0, 'msg' => 'Problem sa bazom. Probajte ponovo !');
		}

		//print_r($role);

	}
	exit(json_encode($rtn));

}

function getProfil() {
	global $token;
	$operateri_id = $token->operateri_id;
	//echo $operateri_id;
	$sql = "SELECT operateri_id, korisnicko_ime, ime, prezime, email_adresa, ministarstvo.operateri.rola_id, ministarstvo.role.naziv as rola_naziv
			FROM ministarstvo.operateri, ministarstvo.role
			WHERE ministarstvo.operateri.rola_id = ministarstvo.role.rola_id
			AND operateri_id = ?;";
	$data = [$operateri_id];
	$res = exec_and_return($sql, $data);
	return $res[0];
}



function editStatusKompleta() {
	if (!checkIzdavaci('izmena')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}
	if (!isset($_POST['kompleti_id']) || !isset($_POST['status'])){
		$rtn = array('status' => 0, 'msg' => 'Nisu poslata sva polja !');
	}
	else {
		$komplet_id = $_POST['kompleti_id'];
		$_POST['status'] == 0 ? $status = 0 : $status = 1;

		$sql = "UPDATE izdanja.kompleti
				SET status = ?
				WHERE kompleti_id = ?;";
		$data = [$status, $komplet_id];
		require_once ('../includes/db_connection.php');
		$stmt = $pdo->prepare($sql);

		if ($stmt->execute($data)) {
			$rtn = array('status' => 1, 'msg' => 'Uspesno editovan status !');
		}
		else {
			$rtn = array('status' => 0, 'msg' => 'Doslo je do greske !');
		}

	}
	exit(json_encode($rtn));
}

function editPassword() {

}

// ministarstvo-skole

function getSU() {
	if (!checkSkole('citanje')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}
	$sql = "SELECT skolskeuprave_id, naziv FROM pratece.skolske_uprave;";
	return exec_and_return($sql);
}

function getOkruzi() {
	if (!checkSkole('citanje')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}
	if (isset($_POST['skolskeuprave_id'])){
		$su_id = $_POST['skolskeuprave_id'];
		$sql = "SELECT okrug_id, naziv, skolskauprava_id
				FROM pratece.okruzi
				WHERE skolskauprava_id = ? ORDER BY naziv;";
		$data = [$su_id];
		return exec_and_return($sql, $data);
	}
	else {
		$sql = "SELECT okrug_id, naziv, skolskauprava_id
				FROM pratece.okruzi;";
		return exec_and_return($sql);
	}
}

function getOpstine() {
	if (!checkSkole('citanje')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}
	if (isset($_POST['okrug_id'])){
		$okrug_id = $_POST['okrug_id'];
		$sql = "SELECT opstina_id, okrug_id, naziv
				FROM pratece.opstine
				WHERE okrug_id = ? ORDER BY naziv;";
		$data = [$okrug_id];
		return exec_and_return($sql, $data);
	}
	else {
		$sql = "SELECT opstina_id, okrug_id, naziv
				FROM pratece.opstine;";
		return exec_and_return($sql);
	}
}

function getNaselja() {
	if (!checkSkole('citanje')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}
	if (isset($_POST['opstina_id'])){
		$opstina_id = $_POST['opstina_id'];
		$sql = "SELECT naselje_id, naziv, opstina_id
				FROM pratece.naselja
				WHERE opstina_id = ? ORDER BY naziv;";
		$data = [$opstina_id];
		return exec_and_return($sql, $data);
	}
	else {
		$sql = "SELECT naselje_id, naziv, opstina_id
				FROM pratece.naselja;";
		return exec_and_return($sql);
	}
}

function getSkole() {
	if (!checkSkole('citanje')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}
	if (isset($_POST['naselje_id'])){
		$naselje_id = $_POST['naselje_id'];
		$sql = "SELECT skole.skole.skola_id, skole.skole.naziv, pratece.naselja.naziv as naselje, pratece.opstine.naziv as opstina, pratece.okruzi.naziv as okrug, pratece.skolske_uprave.naziv as SU
				FROM skole.skole, pratece.naselja, pratece.opstine, pratece.okruzi, pratece.skolske_uprave
				WHERE pratece.naselja.naselje_id = skole.skole.naselje_id
				AND pratece.opstine.opstina_id = pratece.naselja.opstina_id
				AND pratece.okruzi.okrug_id = pratece.opstine.okrug_id
				AND pratece.skolske_uprave.skolskeuprave_id = pratece.okruzi.skolskauprava_id
				AND skole.skole.naselje_id = ?;";
		$data = [$naselje_id];
		return exec_and_return($sql, $data);
	}
	else if (isset($_POST['opstina_id'])) {
		$opstina_id = $_POST['opstina_id'];
		$sql = "SELECT skole.skole.skola_id, skole.skole.naziv, pratece.naselja.naziv as naselje, pratece.opstine.naziv as opstina, pratece.okruzi.naziv as okrug, pratece.skolske_uprave.naziv as SU
				FROM skole.skole, pratece.naselja, pratece.opstine, pratece.okruzi, pratece.skolske_uprave
				WHERE pratece.naselja.naselje_id = skole.skole.naselje_id
				AND pratece.opstine.opstina_id = pratece.naselja.opstina_id
				AND pratece.okruzi.okrug_id = pratece.opstine.okrug_id
				AND pratece.skolske_uprave.skolskeuprave_id = pratece.okruzi.skolskauprava_id
				AND pratece.naselja.opstina_id = ?;";
		$data = [$opstina_id];
		return exec_and_return($sql, $data);
	}
	else if (isset($_POST['okrug_id'])) {
		$okrug_id = $_POST['okrug_id'];
		$sql = "SELECT skole.skole.skola_id, skole.skole.naziv, pratece.naselja.naziv as naselje, pratece.opstine.naziv as opstina, pratece.okruzi.naziv as okrug, pratece.skolske_uprave.naziv as SU
				FROM skole.skole, pratece.naselja, pratece.opstine, pratece.okruzi, pratece.skolske_uprave
				WHERE pratece.naselja.naselje_id = skole.skole.naselje_id
				AND pratece.opstine.opstina_id = pratece.naselja.opstina_id
				AND pratece.okruzi.okrug_id = pratece.opstine.okrug_id
				AND pratece.skolske_uprave.skolskeuprave_id = pratece.okruzi.skolskauprava_id
				AND pratece.opstine.okrug_id = ?;";
		$data = [$okrug_id];
		return exec_and_return($sql, $data);
	}
	else if (isset($_POST['skolskeuprave_id'])) {
		$skolskeuprave_id = $_POST['skolskeuprave_id'];
		$sql = "SELECT skole.skole.skola_id, skole.skole.naziv, pratece.naselja.naziv as naselje, pratece.opstine.naziv as opstina, pratece.okruzi.naziv as okrug, pratece.skolske_uprave.naziv as SU
				FROM skole.skole, pratece.naselja, pratece.opstine, pratece.okruzi, pratece.skolske_uprave
				WHERE pratece.naselja.naselje_id = skole.skole.naselje_id
				AND pratece.opstine.opstina_id = pratece.naselja.opstina_id
				AND pratece.okruzi.okrug_id = pratece.opstine.okrug_id
				AND pratece.skolske_uprave.skolskeuprave_id = pratece.okruzi.skolskauprava_id
				AND pratece.okruzi.skolskauprava_id = ?;";
		$data = [$skolskeuprave_id];
		return exec_and_return($sql, $data);
	}
	else {
		$sql = "SELECT skole.skole.skola_id, skole.skole.naziv, pratece.naselja.naziv as naselje, pratece.opstine.naziv as opstina, pratece.okruzi.naziv as okrug, pratece.skolske_uprave.naziv as SU
				FROM skole.skole, pratece.naselja, pratece.opstine, pratece.okruzi, pratece.skolske_uprave
				WHERE pratece.naselja.naselje_id = skole.skole.naselje_id
				AND pratece.opstine.opstina_id = pratece.naselja.opstina_id
				AND pratece.okruzi.okrug_id = pratece.opstine.okrug_id
				AND pratece.skolske_uprave.skolskeuprave_id = pratece.okruzi.skolskauprava_id;";
		
		return exec_and_return($sql);
	}
}


function getIzborKompleta() {
	if (!checkSkole('citanje')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}
	if (!isset($_POST['razred_id']) || !isset($_POST['jezik_id']) || !isset($_POST['predmet_id']) || !isset($_POST['skola_id'])) {
		$rtn = array('status' => 0, 'msg' => 'Nisu poslata sva polja !');
		exit(json_encode($rtn));
	}
	else {
		$_POST['razred_id'] > 0 ? $razred_id = $_POST['razred_id'] :	$razred_id = "%";
		$_POST['jezik_id'] > 0 ? $jezik_id = $_POST['jezik_id'] : $jezik_id = "%";
		$_POST['predmet_id'] > 0 ? $predmet_id = $_POST['predmet_id'] : $predmet_id = "%";
		$skola_id = $_POST['skola_id'];

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
	if (!checkSkole('citanje')) {
		$rtn = array('status' => 0, 'msg' => 'Nemate ovlascenje !');
		exit(json_encode($rtn));
	}
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

	case 'get-all-izdavaci':
		$result = getAllIzdavaci();
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

	case 'pregled-rola':
		pregledRola();
		break;

	case 'set-rola':
		setRola();
		break;

	case 'get-rola':
		getRola();
		break;

	case 'edit-rola':
		editRola();
		break;

	case 'del-rola':
		delRola();
		break;

	case 'edit-status-kompleta':
		editStatusKompleta();
		break;

	case 'get-profil':
		$result = getProfil();
		exit(json_encode($result));
		break;

	case 'get-SU':
		$result = getSU();
		exit(json_encode($result));
		break;

	case 'get-okruzi':
		$result = getOkruzi();
		exit(json_encode($result));
		break;

	case 'get-opstine':
		$result = getOpstine();
		exit(json_encode($result));
		break;

	case 'get-naselja':
		$result = getNaselja();
		exit(json_encode($result));
		break;

	case 'get-skole':
		$result = getSkole();
		exit(json_encode($result));
		break;

	case 'get-izbor-kompleta':
		$result = getIzborKompleta();
		exit(json_encode($result));
		break;

	case 'get-izbor-izdanja':
		$result = getIzborIzdanja();
		exit(json_encode($result));
		break;
	

	default:
		$result = ['status' => 0, 'msg' => 'unknown function'];
		exit(json_encode($result));
		die();
		break;
}