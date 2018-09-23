<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
header("Access-Control-Allow-Headers', 'Content-Type,Accept");
session_start();

$_SESSION['izdavac_id'] = 1;


require_once("../includes/tools.php");

if (isset($_SESSION['izdavac_id'])) {
	$izdavac_id = $_SESSION['izdavac_id'];
}


function exec_and_return($sql, $data=[]) {
	require_once ('../includes/db_connection.php');

		$stmt = $pdo->prepare($sql);
		$stmt->execute($data);
		$result = $stmt->fetchAll();

		$pdo = null;
		return $result;
}

//login izdavaca
function login() {
	
/*	if (isset($_POST['username']) && isset($_POST['password']) && !empty($_POST['username']) && !empty($_POST['password']))
	{
		require_once ('../includes/db_connection.php');

		$sql = "SELECT izdavac_id FROM izdavaci.izdavaci WHERE izdavaci.maticni_broj = ? AND izdavaci.lozinka = ?";
		
		$stmt = $pdo->prepare($sql);
		$stmt->execute([$_POST['username'], sm_encrypt($_POST['password'])]);

    

$count = $stmt->rowCount();

		if ($count == 0)
		{
			$rtn = array('status' => 0,'msg' => 'Proverite vase podatke !');
		}
		else if ($count == 1)
		{
			$row = $stmt->fetch(PDO::FETCH_ASSOC);

			$_SESSION["izdavac_id"] = $row["izdavac_id"];
			
			$rtn = array('status' => 1, 'msg' => 'Uspesno logovanje');
		}
		else
		{
			$rtn = array('status' => 0, 'msg' => 'Greska u bazi');
			exit(json_encode($rtn));
		}
	}
	else
	{
		$rtn = array('status' => 0, 'msg' => 'Popunite sva polja !');
		exit(json_encode($rtn));
	}
*/
    
    $_SESSION["izdavac_id"] = 1;
   $rtn = array('status' => 1, 'msg' => 'Uspesno logovanje');
    
	$pdo = null;
	exit(json_encode($rtn));
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


function setIzdanje() {
	if (!isset($_POST['razred_id']) || !isset($_POST['jezik_id']) || !isset($_POST['predmet_id']) || !isset($_POST['naziv']) || !isset($_POST['autori']) || !isset($_POST['naziv_udzb_jedinice']) || !isset($_POST['resenje']) || !isset($_POST['godina']) || !isset($_POST['mediji_id'])  || !isset($_POST['formatizdanja_id'])) {

		$rtn = array('status' => 0, 'msg' => 'Nisu poslata sva polja !');
		
	}
	else if (empty($_POST['naziv']) || empty($_POST['autori']) || empty($_POST['naziv_udzb_jedinice']) || empty($_POST['resenje']) || empty($_POST['godina'])) {

		$rtn = array('status' => 0, 'msg' => 'Niste popunili sva potrebana polja !');
		
	}
	else if ($_POST['razred_id'] < 1 || $_POST['jezik_id'] < 1 || $_POST['predmet_id'] < 1) {

		$rtn = array('status' => 0, 'msg' => 'Morate izabrati razred, jezik nastave i predmet !');
		
	}
	else {

		global $izdavac_id;	
		$razred_id = $_POST['razred_id'];
		$jezik_id = $_POST['jezik_id'];
		$predmet_id = $_POST['predmet_id'];
		$naziv = $_POST['naziv'];
		$autori = $_POST['autori'];
		$naziv_udzb_jedinice = $_POST['naziv_udzb_jedinice'];
		$resenje = $_POST['resenje'];
		$godina = $_POST['godina'];
		$format_id = $_POST['formatizdanja_id'];
		$mediji_id = $_POST['mediji_id'];
		$br_strana = $_POST['br_strana'];
		

		require_once ('../includes/db_connection.php');
		$sql = "INSERT INTO izdanja.izdanja(naziv, autori, resenje_ministarstva, naziv_udzb_jedinice, formatizdanja_id, broj_strana, jezici_id, razredi_id, izdavac_id, mediji_id, predmet_id, godina_izdanja)
				VALUES(?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
		$stmt = $pdo->prepare($sql);
		
		if ($stmt->execute([$naziv, $autori, $resenje, $naziv_udzb_jedinice, $format_id, $br_strana, $jezik_id, $razred_id, $izdavac_id, $mediji_id, $predmet_id, $godina])) {

			$rtn = array('status' => 1, 'msg' => 'Uspesno uneseno izdanje !');
			
		}
		else {
			$rtn = array('status' => 0, 'msg' => 'Doslo je do problema sa bazom !');
			
		}
		

		$pdo = null;
	}
		exit(json_encode($rtn));
}

//Sva izdanja
function getAllIzdanja() {
	
	$_POST['razred_id'] > 0 ? $razred = $_POST['razred_id'] :	$razred = "%";
	$_POST['jezik_id'] > 0 ? $jezik_nastave = $_POST['jezik_id'] : $jezik_nastave = "%";
	$_POST['predmet_id'] > 0 ? $predmet = $_POST['predmet_id'] : $predmet = "%";
	global $izdavac_id;		
	$pretraga = "%".$_POST['pretraga']."%";

	$sql = "SELECT izdanja.izdanja.idanja_id, izdanja.izdanja.naziv, izdanja.izdanja.autori, izdanja.izdanja.resenje_ministarstva, izdanja.izdanja.naziv_udzb_jedinice, izdanja.izdanja.formatizdanja_id, pratece.formati_izdanja.naziv as format, izdanja.izdanja.broj_strana, jezici_id, izdanja.izdanja.godina_izdanja, razredi_id, izdavac_id, izdanja.izdanja.mediji_id, pratece.mediji.naziv as mediji, predmet_id
	FROM izdanja.izdanja, pratece.formati_izdanja, pratece.mediji
	WHERE izdanja.izdanja.formatizdanja_id = pratece.formati_izdanja.formatizdanja_id
	AND izdanja.izdanja.mediji_id = pratece.mediji.mediji_id
	AND izdanja.izdanja.izdavac_id = :izdavac_id
	AND izdanja.izdanja.razredi_id::text LIKE :razred_id
	AND izdanja.izdanja.jezici_id::text LIKE :jezik_id
	AND izdanja.izdanja.predmet_id::text LIKE :predmet_id
	AND (izdanja.izdanja.naziv LIKE :pretraga
		OR izdanja.izdanja.autori LIKE :pretraga
		OR izdanja.izdanja.resenje_ministarstva LIKE :pretraga
		OR izdanja.izdanja.naziv_udzb_jedinice LIKE :pretraga)
	ORDER BY izdanja.izdanja.idanja_id DESC;";

	$data = ['izdavac_id' => $izdavac_id, 'razred_id' => $razred, 'jezik_id' => $jezik_nastave, 'predmet_id' => $predmet, 'pretraga' => $pretraga];
	return exec_and_return($sql, $data);
}

function getIzdanje () {
	$izdanja_id = $_POST['izdanja_id'];
	$sql = "SELECT idanja_id, naziv, autori, resenje_ministarstva, naziv_udzb_jedinice, formatizdanja_id, broj_strana, jezici_id, godina_izdanja, razredi_id, izdavac_id, mediji_id, predmet_id
			FROM izdanja.izdanja
			WHERE idanja_id = ?;";
	$data = [$izdanja_id];
	return exec_and_return($sql, $data);
}


function delIzdanje () {
	$idanja_id = $_POST['idanja_id'];
	$sql = "DELETE FROM izdanja.izdanja
			WHERE idanja_id = ?;";

	require_once ('../includes/db_connection.php');

	$stmt = $pdo->prepare($sql);
	if ($stmt->execute([$idanja_id])) {
		$rtn = array('status' => 1, 'msg' => 'Izdanje je uspesno izbrisano !');
		exit(json_encode($rtn));
	}
	else {
		$rtn = array('status' => 0, 'msg' => 'Nije uspelo brisanje !');
		exit(json_encode($rtn));
	}		
}


function editIzdanje () {
	if (!isset($_POST['izdanja_id']) || !isset($_POST['razred_id']) || !isset($_POST['jezik_id']) || !isset($_POST['predmet_id']) || !isset($_POST['naziv']) || !isset($_POST['autori']) || !isset($_POST['naziv_udzb_jedinice']) || !isset($_POST['resenje']) || !isset($_POST['formatizdanja_id']) || !isset($_POST['mediji_id']) || !isset($_POST['br_strana']) || !isset($_POST['godina'])) {

		$rtn = array('status' => 0, 'msg' => 'Nisu poslata sva polja !');
		
	}
	else if (empty($_POST['naziv']) || empty($_POST['autori']) || empty($_POST['naziv_udzb_jedinice']) || empty($_POST['resenje']) || empty($_POST['godina'])) {

		$rtn = array('status' => 0, 'msg' => 'Niste popunili sva potrebana polja !');
		
	}
	else if ($_POST['razred_id'] < 1 || $_POST['jezik_id'] < 1 || $_POST['predmet_id'] < 1) {

		$rtn = array('status' => 0, 'msg' => 'Morate izabrati razred, jezik nastave i predmet !');
		
	}
	else {
		$izdanja_id = $_POST['izdanja_id'];
		$razred_id = $_POST['razred_id'];
		$jezik_id = $_POST['jezik_id'];
		$predmet_id = $_POST['predmet_id'];
		$naziv = $_POST['naziv'];
		$autori = $_POST['autori'];
		$naziv_udzb_jedinice = $_POST['naziv_udzb_jedinice'];
		$resenje = $_POST['resenje'];
		$format_id = $_POST['formatizdanja_id'];
		$mediji_id = $_POST['mediji_id'];
		$br_strana = $_POST['br_strana'];
		$godina = $_POST['godina'];
		global $izdavac_id;	

		require_once ('../includes/db_connection.php');

		$sql = "UPDATE izdanja.izdanja
				SET naziv = ?, autori = ?, resenje_ministarstva = ?, naziv_udzb_jedinice = ?, formatizdanja_id = ?, broj_strana = ?, jezici_id = ?, razredi_id = ?, mediji_id = ?, predmet_id = ?, godina_izdanja = ?
				WHERE idanja_id = ?;";
		$stmt = $pdo->prepare($sql);
		
		if ($stmt->execute([$naziv, $autori, $resenje, $naziv_udzb_jedinice, $format_id, $br_strana, $jezik_id, $razred_id, $mediji_id, $predmet_id, $godina, $izdanja_id])) {

			$rtn = array('status' => 1, 'msg' => 'Uspesno editovano izdanje !');
			
		}
		else {
			$rtn = array('status' => 0, 'msg' => 'Doslo je do problema sa bazom !');
			
		}
		

		$pdo = null;
	}
		exit(json_encode($rtn));
}

//Profil izdavaca
function getProfilIzdavaca() {
	global $izdavac_id;	

	$sql = 'SELECT * FROM izdavaci.izdavaci WHERE izdavac_id = ?';
	$data = [$izdavac_id];
	$temp = exec_and_return($sql, $data);
	$rtn = $temp[0];
	$rtn->lozinka = sm_decrypt($rtn->lozinka);

	return $rtn;
}


//Edit profila izdavaca
function editProfilIzdavaca() {

	if (!isset($_POST['naziv']) || !isset($_POST['pib']) || !isset($_POST['maticni_broj']) || !isset($_POST['grad_id']) || !isset($_POST['lozinka']) || !isset($_POST['potvrda_lozinke']) || !isset($_POST['adresa']) || !isset($_POST['email']) || !isset($_POST['telefon'])) {

		$rtn = array('status' => 0, 'msg' => 'Nisu poslata sva polja !');
		exit(json_encode($rtn));
	}
	else if (empty($_POST['naziv']) || empty($_POST['pib']) || empty($_POST['maticni_broj']) || empty($_POST['grad_id']) || empty($_POST['lozinka']) || !isset($_POST['potvrda_lozinke']) || empty($_POST['adresa']) || empty($_POST['email']) || empty($_POST['telefon'])) {

		$rtn = array('status' => 0, 'msg' => 'Popunite sva polja !');
		exit(json_encode($rtn));
	}
	else if (strlen($_POST['lozinka']) < 6) {

		$rtn = array('status' => 0, 'msg' => 'Lozinka mora biti minimum 6 karaktera !');
		exit(json_encode($rtn));
	}
	else if ($_POST['lozinka'] !== $_POST['potvrda_lozinke']) {
		$rtn = array('status' => 0, 'msg' => 'Lozinka se ne slaze sa potvrdom lozinke !');
		exit(json_encode($rtn));
	}
	else if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
		$rtn = array('status' => 0, 'msg' => 'Nepravilan format Email-a !');
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

		global $izdavac_id;	
		
		require_once ('../includes/db_connection.php');

		$sql = 'UPDATE izdavaci.izdavaci
				SET naziv=?, "PIB"=?, maticni_broj=?, grad_id=?, lozinka=?, adresa=?, email=?, telefon=?
				WHERE izdavac_id = ?;';
		$data = [$naziv, $pib, $maticni_broj, $grad_id, sm_encrypt($lozinka), $adresa, $email, $telefon, $izdavac_id];


		$stmt = $pdo->prepare($sql);
		if ($stmt->execute($data)) {
			
			$rtn = array('status' => 1, 'msg' => 'Uspesno editovan izdavac !');
			exit(json_encode($rtn));
		}
		else {
			
			$rtn = array('status' => 0, 'msg' => 'Doslo je do greske !');
			exit(json_encode($rtn));
		}

	}

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



function getIzdanjaZaKomplet() {
	$razred_id = $_POST['razred_id'];
	$jezik_id = $_POST['jezik_id'];
	$predmet_id = $_POST['predmet_id'];
	global $izdavac_id;	

	$sql = "SELECT idanja_id, naziv, autori, resenje_ministarstva, naziv_udzb_jedinice
			FROM izdanja.izdanja
			WHERE izdavac_id = ?
			AND razredi_id = ?
			AND jezici_id = ?
			AND predmet_id = ?;";
	$data = [$izdavac_id, $razred_id, $jezik_id, $predmet_id];

	return exec_and_return($sql, $data);
}


function setKomplet() {

	if (!isset($_POST['naziv']) || !isset($_POST['resenje']) || !isset($_POST['izdanja'])) {
		$rtn = array('status' => 0, 'msg' => 'Nisu poslata sva polja !');
	}
	else if (empty($_POST['naziv']) || empty($_POST['resenje']) || empty($_POST['izdanja'])) {
		$rtn = array('status' => 0, 'msg' => 'Sva polja moraju biti popunjena !');
	}
	else {
		$naziv_kompleta = $_POST['naziv'];
		$resenje = $_POST['resenje'];
		$izdanja = $_POST['izdanja'];
		$razred_id = $_POST['razred_id'];
		$jezik_id = $_POST['jezik_id'];
		$predmet_id = $_POST['predmet_id'];
		global $izdavac_id;	

		require_once ('../includes/db_connection.php');

		$sql = "SELECT kompleti_id FROM izdanja.kompleti WHERE naziv = ?";
		$data = [$naziv_kompleta];
		$stmt = $pdo->prepare($sql);
		$stmt->execute($data);
		$count = $stmt->rowCount();

		if ($count !== 0) {
			$rtn = array('status' => 0, 'msg' => 'Komplet sa tim imenom vec postoji!');
		}
		else {

			$sql = "INSERT INTO izdanja.kompleti(naziv, resenje_ministarstva, izdavac_id, predmet_id, razred_id, jezik_id)
					VALUES (?, ?, ?, ?, ?, ?) RETURNING kompleti_id;";
			$data = [$naziv_kompleta, $resenje, $izdavac_id, $predmet_id, $razred_id, $jezik_id];
			$stmt = $pdo->prepare($sql);
			$stmt->execute($data);
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			$komplet_id = $result['kompleti_id'];

			if ($komplet_id !== 0) {

				$greska = false;
				foreach ($izdanja as $izdanje) {
					$sql = "INSERT INTO izdanja.komplati_izdanja(komplet_id, izdanje_id)
							VALUES (?, ?);";
					$data = [$komplet_id, $izdanje];
					$stmt = $pdo->prepare($sql);
					if (!$stmt->execute($data)) {
						$greska = true;
					}
				}
				if (!$greska) {

					$rtn = array('status' => 1, 'msg' => 'Uspesno formiran komplet !', 'kompleti_id' => $komplet_id);
				}
				else {
					$sql = "DELETE FROM izdanja.kompleti
							WHERE kompleti_id = ?;";
					$data = [$komplet_id];
					$stmt = $pdo->prepare($sql);
					$stmt->execute($data);	
					$rtn = array('status' => 0, 'msg' => 'Doslo je do problema pri unosenju izdanja u komplet. Probajte ponovo!');
				}
			}
			else {
				$rtn = array('status' => 0, 'msg' => 'Problem sa bazom !');
			}
			
		}
		
	}

	exit(json_encode($rtn));	
}


function getKomplet() {
	$komplet_id = $_POST['kompleti_id'];

	$sql = "SELECT kompleti_id, naziv, resenje_ministarstva, izdavac_id, predmet_id, razred_id, jezik_id, status
			FROM izdanja.kompleti
			WHERE kompleti_id = ?;";
	$data = [$komplet_id];

	return exec_and_return($sql, $data);		
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


function getKompleti() {

	$_POST['razred_id'] > 0 ? $razred = $_POST['razred_id'] :	$razred = "%";
	$_POST['jezik_id'] > 0 ? $jezik_nastave = $_POST['jezik_id'] : $jezik_nastave = "%";
	$_POST['predmet_id'] > 0 ? $predmet = $_POST['predmet_id'] : $predmet = "%";
	global $izdavac_id;	
	$pretraga = "%".$_POST['pretraga']."%";

	$sql = "SELECT kompleti_id, naziv, resenje_ministarstva, izdavac_id, predmet_id, razred_id, jezik_id, status
			FROM izdanja.kompleti
			WHERE izdanja.kompleti.izdavac_id = :izdavac_id
			AND izdanja.kompleti.razred_id::text LIKE :razred_id
			AND izdanja.kompleti.jezik_id::text LIKE :jezik_id
			AND izdanja.kompleti.predmet_id::text LIKE :predmet_id
			AND (izdanja.kompleti.naziv LIKE :pretraga
				OR izdanja.kompleti.resenje_ministarstva LIKE :pretraga)
			ORDER BY izdanja.kompleti.kompleti_id DESC;";

	$data = ['izdavac_id' => $izdavac_id, 'razred_id' => $razred, 'jezik_id' => $jezik_nastave, 'predmet_id' => $predmet, 'pretraga' => $pretraga];
	return exec_and_return($sql, $data);

}


function delKomplet() {
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


switch ($_POST['funct']) {
	case 'login':
		login();
		break;

	case 'get-razredi':
		$result = getRazredi();
		exit(json_encode($result));
		break;

	case 'get-jezici':
		$result = getJezici();
		exit(json_encode($result));
		break;

	case 'get-predmeti':
	//razred_id
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

	case 'set-izdanje':
	// razred_id, jezik_id, predmet_id, naziv, autori, naziv_udzb_jedinice, resenje, godina, format_id, mediji_id, br_strana
		setIzdanje();
		break;

	case 'get-izdanja':
	//razred_id, jezik_id, predmet_id, pretraga
		$result = getAllIzdanja();
		exit(json_encode($result));
		break;

	case 'get-izdanje':
	//izdanja_id
		$result = getIzdanje();
		exit(json_encode($result));
		break;

	case 'del-izdanje':
	//izdanja_id
		delIzdanje();
		break;

	case 'edit-izdanje':
	//razred_id, jezik_id, predmet_id, naziv, autori, naziv_udzb_jedinice, resenje, godina, format_id, mediji_id, br_strana, izdanja_id
		editIzdanje();
		break;
	
	case 'get-profil-izdavaca':
		$result = getProfilIzdavaca();
		exit(json_encode($result));
		break;

	case 'edit-profil-izdavaca':
	//naziv, pib, maticni_broj, grad_id, lozinka, potvrda_lozinke, adresa, email
		editProfilIzdavaca();
		break;

	case 'get-gradovi':
		$result = getGradovi();
		exit(json_encode($result));
		break;

	case 'get-grad':
	//grad_id
		$result = getGrad();
		exit(json_encode($result));
		break;

	case 'get-izdanja-za-komplet':
	//razred_id, jezik_id, predmet_id
		$result = getIzdanjaZaKomplet();
		exit(json_encode($result));
		break;

	case 'set-komplet':
	//razred_id, jezik_id, predmet_id, naziv, resenje, izdanja (to je niz ID-jeva izdanja koja ulaze u komplet)
		setKomplet();
		break;

	case 'get-komplet':
	//komplet_id
		$result = getKomplet();
		exit(json_encode($result));
		break;

	case 'get-izdanja-iz-kompleta':
	//komplet_id
		$result = getIzdanjaIzKompleta();
		exit(json_encode($result));
		break;

	case 'get-kompleti':
	//razred_id, jezik_id, predmet_id, pretraga
		$result = getKompleti();
		exit(json_encode($result));
		break;

	case 'del-komplet':
	//kompleti_id
		delKomplet();
		break;

	default:
		$result = ['status' => 0, 'msg' => 'unknown function'];
		exit(json_encode($result));
		die();
		break;
}