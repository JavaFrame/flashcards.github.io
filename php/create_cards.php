<?php
	require __DIR__ . "./login-utils.php";

	function createCards() {

	}

	function saveCard() {

	}

	function deleteCard() {

	}

	function updateOneCard() {
		
	}
	

	function uploadCards() {
		$user_data = getUserData($_POST['id_token']);
		$user_id = $user_data['sub'];
		if(doesUserExist($user_id)) {

		} else {
			die('{"type":"error", "msg":"User doesn\'t exist!"');
		}

		if(!isset($_FILES['file'])){
			die('{"type":"error", "msg":"_FILES[\'file\'] does not exist. Error in the upload!""}');
		}

		if(!isset($_POST['ignore_first_line'])) {
			die('{"type":"error", "msg":"error: _POST[\'ignore_first_line\'] is not set!""}');
		}

		
		switch ($_FILES['file']['error']) {
	        case UPLOAD_ERR_OK:
	            break;
	        case UPLOAD_ERR_NO_FILE:
	            throw new RuntimeException('No file sent.');
	        case UPLOAD_ERR_INI_SIZE:
	        case UPLOAD_ERR_FORM_SIZE:
	            throw new RuntimeException('Exceeded filesize limit.');
	        default:
	            throw new RuntimeException('Unknown errors.');
    	}

    	if ($_FILES['file']['size'] > 1000000) {
        	//throw new RuntimeException('Exceeded filesize limit.');
        	die('"type":"error", "msg":"File is too large! (' . $_FILES['upfile']['size'] .' > 1\'000\'000 bytes)."}');
    	}

    	$file_content = file_get_contents($_FILES['file']['tmp_name']);
    	//$csv_file_content = str_getcsv($file_content);

    	$lines = explode(PHP_EOL, $file_content);
    	$csv_array = array();
    	foreach($lines as $l) {
    		$csv_array[] = str_getcsv($l);
    	}

    	$db = new mysqli('localhost', 'root', '', 'flashcards'); //connects to the db
		if($db->connect_errno > 0){ //checks for error 
		    die('{"type":"error", "msg":"' . $db->error .'"}');
		}


		if(!isset($_POST['name'])) {
			die('{"type":"error", "msg":"error: name was not defined in _POST"}');
		}
		if(!isset($_POST['description'])) {
			die('{"type":"error", "msg":"error: description was not defined in _POST"}');
		}
		if(!isset($_POST['subject'])) {
			die('{"type":"error", "msg":"error: subject was not defined in _POST"}');
		}
		if(!isset($_POST['language_from'])) {
			die('{"type":"error", "msg":"error: language-from was not defined in _POST"}');
		}
		if(!isset($_POST['language_to'])) {
			die('{"type":"error", "msg":"error: language-to was not defined in _POST"}');
		}

		$cards_name = $_POST['name'];
		$cards_description = $_POST['description'];
		$cards_subject = $_POST['subject'];
		$cards_language_from = $_POST['language_from'];
		$cards_language_to = $_POST['language_to'];
		$ignore_first_line = true;
		if($_POST['ignore_first_line'] == 'false')
			$ignore_first_line = false;

		//check if name is already taken
		$stmt = $db->prepare("SELECT Name FROM cards WHERE Name=?");
		$stmt->bind_param("s", $cards_name);
		if(!$stmt->execute()) {
			die('{"type":"error",  "msg":"error: '. $db->error .'"}');
			return;
		}
		if($stmt->get_result()->num_rows != 0) {
			die('{"type":"error",  "msg":"error: name \'' . $cards_name . '\' already exists!"}');
			return;
		}
		$stmt->close();

		$cards_path = __DIR__ . "/cards/";

		if(!file_exists($cards_path)) {
			mkdir($cards_path, 0777, true);
		}

		$error_occured = false;
		//finds file name for cvs file
    	$cards_file_name = $cards_path . random_string(64) . ".cards";
    	while(file_exists($cards_file_name)) {
    		$cards_file_name = $cards_path . random_string(64) . ".cards";
    	}

    	$cards_file = fopen($cards_file_name, "w") or die('{"type":"error", "msg":"Can\' create File \''. $cards_file_name . '\'!"}');

    	for($i = ($ignore_first_line? 1 : 0); $i < count($csv_array); $i++) {
    		$line = $csv_array[$i];
    		$question_type = $line[0];
    		if($question_type != "typed" || $question_type != "show")
    			$question_type = "typed";
    		$question = $line[1];
    		$answer = $line[2];
    		$help = $line[3];
    		if($question == "" || $answer == "") {
    			echo '{"type":"error", "msg":"(Line: ' . ($i + 1) . ') In Question \''. $question . '\' (Answer:\'' . $answer .'\') question or answer is not set!"}';
    			$error_occured = true;
    			break;
    		}

    		fwrite($cards_file, $question_type . "," . $question . "," . $answer . "," . $help . "\n");
    	}
    	fclose($cards_file);
    	if($error_occured) {
    		unlink($cards_file_name);
    		return;
    	}

    	//insert cards into cards table
		$stmt = $db->prepare("INSERT INTO cards (Name, Description, Subject, LanguageFrom, LanguageTo, OwnerId, FileName) VALUES (?, ?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("sssssss", $cards_name, $cards_description, $cards_subject, $cards_language_from, $cards_language_to, $user_id, $cards_file_name);
		
		if(!$stmt->execute()) {
			die('{"type":"error", "msg":"error: '. $db->error .'"}');
			unlink($cards_file_name);
		}
		$stmt->close();

    	//print_r($csv_array);
	}

	function random_string($length) {
		$key = '';
		$keys = array_merge(range(0, 9), range('a', 'z'));

		for ($i = 0; $i < $length; $i++) {
		    $key .= $keys[array_rand($keys)];
		}

		return $key;
	}

	//echo "files: " . implode(', ', $_FILES['file']);

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
		case 'uploadCards':
			uploadCards();
			break;
		default:
			//echo '{"msg":"function '. $function . ' does not exist!"}';
			break;
	}
?>