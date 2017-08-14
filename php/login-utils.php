

<?php
	require_once __DIR__ . '\..\lib\vendor\autoload.php';

	$g_client = new Google_Client(['client_id' => '838108663601-n67tgbqk1ips0febm60h5obsne3mn45t.apps.googleusercontent.com']);
	$g_client->setApplicationName("FlashCards");

	function getUserData($encryptedToken) {
		global $g_client;

		$payload = $g_client->verifyIdToken($encryptedToken); //decrypts the id_token into a useable format from which you can get the username, ...
		return $payload;
	}

	function doesUserExist($user_id) {
		$db = new mysqli('localhost', 'root', '', 'flashcards'); //connects to the db

		if($db->connect_errno > 0){ //checks for error 
		    die('{"success":false, "msg":"' . $db->error .'"}');
		}

		$sql = "SELECT Id FROM users WHERE Id='" . $user_id . "';"; //sql query for checking if the users already signedUp.
		$result_check_id = $db->query($sql);//sends the query
		if(!$result_check_id) { //checks if it was successful
			die('{"success":false, "msg":"' . $db->error .'"}');
		}

		if($result_check_id->num_rows == 0) { //if num_rows == 0 then no result was returned and thus the user hasn't signed up.
			return false;
		}

		return true;
	}
?>