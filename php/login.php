

<?php
	require_once __DIR__ . '\..\lib\vendor\autoload.php';
	require_once __DIR__ . '\login-utils.php';

	$g_client = new Google_Client(['client_id' => '838108663601-n67tgbqk1ips0febm60h5obsne3mn45t.apps.googleusercontent.com']);
	$g_client->setApplicationName("FlashCards");

	session_start();

	function signIn() {
		global $g_client;

		//check if post id_token is not null.
		if(!isset($_POST['id_token'])) {
			die('{"success":false, "msg":"Error: id_token is NULL!"}');
		}

		$id_token = $_POST['id_token'];

		$payload = getUserData($id_token);
		if($payload) {//checks if it was successful
			$db = new mysqli('localhost', 'root', '', 'flashcards'); //connects to the db

			if($db->connect_errno > 0){ //checks for error 
			    die('{"success":false, "msg":"' . $db->error .'"}');
			}

			$user_id = $payload['sub']; //extract the user_id 
			$user_name = $payload['name']; //extract the name

			if(!doesUserExist($user_id)) { //if num_rows == 0 then no result was returned and thus the user hasn't signed up.
				$stmt = $db->prepare("INSERT INTO users (Id, Name, Library) VALUES (?, ?, '[]')");
				if(!$stmt) {
					die('{"success":false, "msg": "errdfhgor:' . implode("," , $db->error_list) .'"}');
				}
				$stmt->bind_param("ss", $user_id, $user_name);
				$rc = $stmt->execute();

				//$insert_result = $db->query($sql);
				$insert_result = $stmt->get_result(); 
				if(!$rc) {
					die('{"success":false, "msg":"error: ' . $db->error .'"}');
				}
				$stmt->close(); 
			}

			echo '{"success":true, "msg":"Welcome back ' . $user_name .'!"}';
			
			$_SESSION['id_token'] = $id_token;

			$db->close();

		} else {
			die('{"success":false, "msg":"Failed to signIn with Google!"}');
		}
	}

	function signOut() {
		$_SESSION['id_token'] = NULL;
		session_destroy();
	}

	function checkSession() {
		if(isset($_SESSION) && isset($_SESSION['id_token'])) {
			echo '{"sessionStarted":true, "id_token":"' . $_SESSION['id_token'] . '"}';
		} else {
			echo '{"sessionStarted":false}';
		}
	}

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    	$function = $_POST['fun'];
    	
	} else {
		$function = $_GET['fun'];
	}

	switch ($function) {
		case 'signIn':
			signIn();
			break;
		case 'signOut':
			signOut();
			break;
		case 'checkSession':
			checkSession();
			break;
		default:
			echo '{"msg":"function '. $function . ' does not exist!"}';
			break;
	}

?>