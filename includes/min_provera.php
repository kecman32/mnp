<?php 

function checkMinistarstvo($action) {

	global $token;

	if (isset($token->Министарство->$action)) {
		if ($token->Министарство->$action == 1 ) {
			
			return true;
		}
		else {
			return false;
		}
	}
	else {
		return false;
	}

}

function checkIzdavaci($action) {

	global $token;

	if (isset($token->Издавачи->$action)) {
		if ($token->Издавачи->$action == 1 ) {
			
			return true;
		}
		else {
			return false;
		}
	}
	else {
		return false;
	}

}

function checkSkole($action) {

	global $token;

	if (isset($token->Школе->$action)) {
		if ($token->Школе->$action == 1 ) {
			
			return true;
		}
		else {
			return false;
		}
	}
	else {
		return false;
	}

 }
