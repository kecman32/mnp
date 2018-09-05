<?php
	require_once("../config/security.php");

	function sm_encrypt($data)
	{
		$encryption_key = base64_decode(ENC_KEY);
		$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
		$encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);

		return base64_encode($encrypted . '::' . $iv);
	}

	function sm_decrypt($data)
	{
		$encryption_key = base64_decode(ENC_KEY);
		list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);

		return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
	}
?>