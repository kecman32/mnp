<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>test</title>

</head>
<body>
	<?php 
	// echo $_SERVER['DOCUMENT_ROOT'];
	// echo dirname( __FILE__ );
	// echo "<br>";
	// include ($_SERVER['DOCUMENT_ROOT'].'/ministarstvo/izdavaci/test.php');
	// $hash = password_hash('123456', PASSWORD_BCRYPT);
	// echo $hash;
	?>

<script
  src="https://code.jquery.com/jquery-3.3.1.min.js"
  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
  crossorigin="anonymous"></script>

<script>

function getMediji() {
	//var izdanja = [1, 2, 3];
	$.post('../ministarstvo/ws.php',{
		funct: 'pregled-rola',
	// 	razred_id: 1,
	//	jezik_id: 1,
	// 	predmet_id: 1,
	// 	naziv: 'test naziv 3',
	// 	autori: 'test autorr, tesst autor 22',
	// 	naziv_udzb_jedinice: 'test udz jedinica',
	// 	resenje: '87654321aa',
	// 	godina: 2017,
	// 	format_id: 4,
	// 	mediji_id: 1,
	// 	br_strana: 54,
	//    pretraga: 'a',
	// 	izdanja_id: 3
	//	naziv: 'test izdavac',
	//	pib: '12345678',
	//	maticni_broj: '42345678',
	//	grad_id: 2,
	//	lozinka: '1234567',
		// potvrda_lozinke: '1234567',
	//	adresa: 'test adresa bb',
	//	email: 'test6@test.ts',
	//	telefon: '011222222',
	//  grad_id: 1,
	//	naziv: 'test naziv kompleta 9',
	//	resenje: 'test resenje 9',
	//	izdanja: izdanja,
	//	kompleti_id: 18,
	//	username: '1234567',
	//	password: '123457',
	//	izdavac_id: '6',
		pretraga: 'test',
	//	username: 'test',
	//	password: '123456',
		token: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJvcGVyYXRlcmlfaWQiOjEsImltZSI6InRlc3RpbWUiLCJwcmV6aW1lIjoidGVzdHByZXppbWUiLCJleHAiOjE1MzY0MjQ0NzgsIlx1MDQxY1x1MDQzOFx1MDQzZFx1MDQzOFx1MDQ0MVx1MDQ0Mlx1MDQzMFx1MDQ0MFx1MDQ0MVx1MDQ0Mlx1MDQzMlx1MDQzZSI6eyJjaXRhbmplIjp0cnVlLCJpem1lbmEiOnRydWUsImJyaXNhbmplIjp0cnVlfSwiXHUwNDI4XHUwNDNhXHUwNDNlXHUwNDNiXHUwNDM1Ijp7ImNpdGFuamUiOnRydWUsIml6bWVuYSI6dHJ1ZSwiYnJpc2FuamUiOnRydWV9LCJcdTA0MThcdTA0MzdcdTA0MzRcdTA0MzBcdTA0MzJcdTA0MzBcdTA0NDdcdTA0MzgiOnsiY2l0YW5qZSI6dHJ1ZSwiaXptZW5hIjp0cnVlLCJicmlzYW5qZSI6dHJ1ZX19.1rNJ3BLWSvpRXjE7kzSK5EFhiFWsRnT1Y71RIt7sy3c',
	//	token: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJvcGVyYXRlcmlfaWQiOjJ9.csGK-Af6BPGF3f745wM39qQLMdf82rMTdRuJzTvN5G0',
	//	korisnicko_ime: 'test6',
	//	lozinka: '654321',
	//	ime: 'testime3',
	//	prezime: 'testprezime3',
	//	email_adresa: 'test6@testt.com',
	//	rola_id: '2',
	//	operateri_id: '4',
	//	rola_id: '',
	//	sekcijeaplikacije_id: ''


	}, function(data){
		console.log(data);
	}, "json");
}



jQuery(document).ready(function($) {
	getMediji();
});
</script>

</body>
</html>