<?php 

session_start();

require_once("../includes/tools.php");
require_once("../includes/jwt_helper.php");
require_once("../config/security.php");
require_once("../includes/min_provera.php");
require_once ('../includes/db_connection.php');

if (!isset($_POST['key'])){
	exit();
	die();
}

if ($_POST['key'] == 'razredi'){
	$sql = "SELECT * FROM osnovna.razredi ORDER BY razred_id ASC";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$result = $stmt->fetchAll();
	exit(json_encode($result));
}


if ($_POST['key'] == 'jezici'){
	$sql = "SELECT * FROM pratece.jezici ORDER BY jezik_id ASC";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$result = $stmt->fetchAll();
	exit(json_encode($result));
}


if ($_POST['key'] == 'predmeti'){
	$sql = "SELECT * FROM osnovna.predmeti WHERE aktivan = 1 ORDER BY predmet_id ASC";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$result = $stmt->fetchAll();
	exit(json_encode($result));
}


if ($_POST['key'] == 'izabrani-predmeti'){
	$razred_id = $_POST['razred_id'];
	$jezik_id = $_POST['jezik_id'];

	$sql = "SELECT osnovna.predmeti.predmet_id, osnovna.predmeti.naziv 
			FROM osnovna.predmeti, osnovna.predmeti_po_razredima
			WHERE osnovna.predmeti.predmet_id = osnovna.predmeti_po_razredima.predmet_id
			AND predmeti_po_razredima.razred_id = ?
			AND predmeti_po_razredima.jezik_id = ?
			AND osnovna.predmeti.aktivan = 1;";
	$stmt = $pdo->prepare($sql);
	$data = [$razred_id, $jezik_id];
	$stmt->execute($data);
	$result = $stmt->fetchAll();
	exit(json_encode($result));
}

if ($_POST['key'] == 'upis') {
	$razred_id = $_POST['razred_id'];
	$jezik_id = $_POST['jezik_id'];
	$niz_novi = $_POST['nizz'];
	
	$sql = "SELECT osnovna.predmeti.predmet_id, osnovna.predmeti.naziv 
			FROM osnovna.predmeti, osnovna.predmeti_po_razredima
			WHERE osnovna.predmeti.predmet_id = osnovna.predmeti_po_razredima.predmet_id
			AND predmeti_po_razredima.razred_id = ?
			AND predmeti_po_razredima.jezik_id = ?
			AND osnovna.predmeti.aktivan = 1
			ORDER BY osnovna.predmeti.predmet_id ASC;";
	$data = [$razred_id, $jezik_id];
	$stmt = $pdo->prepare($sql);
	$stmt->execute($data);
	$result = $stmt->fetchAll();
	$niz_stari = [];
	foreach ($result as $predmet) {
		$niz_stari[] = $predmet->predmet_id;
	}

	
	$dodati = array_values(array_diff($niz_novi, $niz_stari));
	$izbrisati = array_values(array_diff($niz_stari, $niz_novi));
	// print_r($dodati);
	// print_r($izbrisati);


	foreach ($dodati as $predmet_id) {
		$sql = "INSERT INTO osnovna.predmeti_po_razredima(razred_id, jezik_id, predmet_id)
				VALUES (?, ?, ?);";
		$data = [$razred_id, $jezik_id, $predmet_id];
		$stmt = $pdo->prepare($sql);
		$stmt->execute($data);

	}

	foreach ($izbrisati as $predmet_id) {
		$sql = "DELETE FROM osnovna.predmeti_po_razredima
				WHERE razred_id = ?
				AND jezik_id = ?
				AND predmet_id = ?;";
		$data = [$razred_id, $jezik_id, $predmet_id];
		$stmt = $pdo->prepare($sql);
		$stmt->execute($data);
	}
	$rtn = array('status' => 1, 'msg' => 'Uspesno azurirana baza !');
	exit(json_encode($rtn));
}


if ($_POST['key'] == 'novi_predmet') {
	$novi_predmet = $_POST['novi_predmet'];
	if (empty($novi_predmet)) {
		$rtn = array('status' => 0, 'msg' => 'Morate popuniti naziv predmeta!');
	}
	else {
		$sql = "SELECT predmet_id, naziv, aktivan FROM osnovna.predmeti
				WHERE naziv = ?;";
		$data = [$novi_predmet];
		$stmt = $pdo->prepare($sql);
		$stmt->execute($data);
		$count = $stmt->rowCount();
		if ($count > 0) {
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($row['aktivan'] == 1) {
				$rtn = array('status' => 0, 'msg' => 'Vec postoji predmet sa tim nazivom!');
			}
			else {
				$sql = "UPDATE osnovna.predmeti	SET aktivan = 1	WHERE naziv = ?;";
				$stmt = $pdo->prepare($sql);
				$stmt->execute($data);
				$rtn = array('status' => 1, 'msg' => 'Uspesno reaktiviran predmet!');
			}
		}
		else {
			
			$sql = "INSERT INTO osnovna.predmeti(naziv)	VALUES (?);";
			$stmt = $pdo->prepare($sql);
			$stmt->execute($data);
			$rtn = array('status' => 1, 'msg' => 'Uspesno unesen novi predmet!');
		}
				
	}
	exit(json_encode($rtn));
}


if ($_POST['key'] == 'del_predmet') {
	$del_predmet = $_POST['del_predmet'];
	if (empty($del_predmet)) {
		$rtn = array('status' => 0, 'msg' => 'Morate popuniti naziv predmeta!');
	}
	else {
		$sql = "UPDATE osnovna.predmeti	SET aktivan = 0	WHERE predmet_id = ?;";
		$data = [$del_predmet];
		$stmt = $pdo->prepare($sql);
		$stmt->execute($data);
		$rtn = array('status' => 1, 'msg' => 'Uspesno deaktiviran predmet!');
	}
	exit(json_encode($rtn));
}
