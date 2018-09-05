<?php

session_start();


require_once("../includes/tools.php");



	
if (isset($_POST['username']) && isset($_POST['password']) && !empty($_POST['username']) && !empty($_POST['password']))
{
	require_once ('../includes/db_connection.php');

	$sql = "SELECT izdavac_id, lozinka FROM izdavaci.izdavaci WHERE izdavaci.maticni_broj = ?";
	
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

		if (sm_decrypt($row['lozinka']) == $_POST['password']) {

			$_SESSION["izdavac_id"] = $row["izdavac_id"];
			
			$rtn = array('status' => 1, 'msg' => 'Uspesno logovanje');
			
		}
		else {
			$rtn = array('status' => 0,'msg' => 'Proverite vase podatke !');
		}

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

$pdo = null;
exit(json_encode($rtn));
