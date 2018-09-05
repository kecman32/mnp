<?php
	session_start();

	if (!(isset($_SESSION["izdavac_id"])))
	{
		header("Location: /izdavaci/login.php");
		exit();
	}
?>