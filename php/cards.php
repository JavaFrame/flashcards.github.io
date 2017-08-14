<?php
	require_once __DIR__ . "\login-utils.php";

	if(!isset($_SESSION)) {
		session_start();
	}

	function search() {
		if(!isset($_GET['s'])) {
			die('
			{
				"success": false,
				"msg": "GET variable wasn\' defined."
			}');
		}
		if(!isset($_GET['p'])) {
			$page = 0;
		} else {
			$page = $_GET['p'];
		}

		if(!isset($_GET['p_size'])) {
			$p_size = 10;
		} else {
			$p_size = $_GET['p_size'];
		}

		$search_token = $_GET['s'];
		$db = new mysqli('localhost', 'root', '', 'flashcards');
		if($db->connect_errno > 0) {
			die('
			{
				"success": false,
				"msg": "Error: ' . $db->error . '."
			}');
		} 

		$sql = 'SELECT * FROM `cards` WHERE Name LIKE "%' . $search_token . '%" LIMIT ' . $page . ', '. $p_size . ';';
		if($search_result = $db->query($sql)) {
			$resultForUser = "[";
			if($search_result->num_rows > 0) {
				$row = $search_result->fetch_assoc();
				$resultForUser .= get_json_from_array($row);
				
				while($row = $search_result->fetch_assoc()) {
					$resultForUser .= "," . get_json_from_array($row);
				}
			}
			$resultForUser .= "]";
			echo $resultForUser;
		} else {
			die('
			{
				"success": false,
				"msg": "' . $db->error . '"
			}');
		}
	}

	function get_json_from_array($row) {
		$r = "{";
		$r .= '"name":"' . $row['Name'] . '",';
		$r .= '"subject":"' . $row['Subject'] . '",';
		$r .= '"language_from":"' . $row['LanguageFrom'] . '",';
		$r .= '"language_to":"' . $row['LanguageTo'] . '",';
		$r .= '"description":"' . $row['Description'] . '",';
		$r .= '"ownerId":"' . $row['OwnerId'] . '"';
		$r .= "}";
		return $r;
	}

	function getRecentCards() {

	}

	function learnNextCard() {
		if(!isset($_POST['c'])) {
			die('{"type":"error", "msg":"_POST[\'c\'] was not defined!"}');
		}
		if(!isset($_POST['user_id'])) {
			die('{"type":"error", "msg":"_POST[\'user_id\'] was not defined!"}');
		}
		$user_id = $_POST['user_id'];
		$cards_name = $_POST['c'];

		$db = new mysqli('localhost', 'root', '', 'flashcards'); //connects to the db

		if($db->connect_errno > 0){ //checks for error 
		    die('{"type":"error", "msg":"error:' . $db->error . '"}');
		}

		//checks if cards_name exist in table cards
		$stmt = $db->prepare("SELECT Name FROM cards WHERE Name=?");
		$stmt->bind_params("s", $cards_name);
		if(!$stmt->execute()) {
		    die('{"type":"error", "msg":"error:' . $db->error . '"}');
		}
		$result = $stmt->get_result();
		if($result->num_rows == 0) {//cards_name does not exist!
		    die('{"type":"error", "msg":"error:' . $db->error . '"}');
		}

		//checks if the user currently learns, if so the _SESSION['cards_name'] variable would be set to the cards name
		if(!isset($_SESSION['cards_name'])) {
			//checks if the user already has learned this cards. If he did, there should be an entry in the users table in the Leanred collumn array
			$learned_cards = get_learned_cards();

			if(!array_key_exists($cards_name, $learned_cards)){//checks if the cards_name is in the learned_cards
				//if not, it will be added
				//array_push($learned_cards, $cards_name); //pushs the cards_name on top of the learned_cards

				$learned_cards[$cards_name] = 
				array(
					get_cards_in_array(), //returns the cards in an array
					array(),
					array(),
					array(),
					array(),
					"settings" => array( //sets the default learning settings
						"shuffel" => false,
						"step_learning" => true
						),
					"step_learning_set" => array() //the current set which the step learning algorithm uses.
				);

				$serialized_str = serialize($learned_cards);

				//uploads the changes in to the data base
				$stmt = $db->prepare("UPDATE users SET Learned=? WHERE Id=?");
				$stmt->bind_params("ss", $serialized_str, $user_id);
				if(!$stmt->execute()) {
				    die('{"type":"error", "msg":"error:' . $db->error . '"}');
				}
			}

			startLearningCards();
		}

		$cards = get_learned_cards();
		$shuffel_cards = $cards['settings']['shuffel'];
		$step_learning = $cards['settings']['step_learning'];

		$next_card = array();

		if($step_learning) {
			if(shuffel_cards) {
				$step_learning_set = $cards['step_learning_set'];
				if(count($step_learning_set) == 0) {
					//refil step_learning_set

				}
				//$shuffeled_set = shuffle_assoc($step_learning_set);
				$shuffeled_keys = shuffle(array_keys($step_learning_set));
				$next_card = $step_learning_set[$shuffeled_keys[0]];
				unset($step_learning_set[$shuffeled_keys[0]]);
			} else {
				
				
			}
		} elseif ($shuffel_cards) {

		} else {

		}

	}	

	function start_learning_cards($cards_name) {
		$_SESSION['cards_name'] = $cards_name;
		$_SESSION['cards'] = get_learned_cards($user_id)[$cards_name];

	}

	function get_learned_cards($user_id) {
		$stmt = $db->prepare("SELECT Learned FROM users WHERE Id=?");
		$stmt->bind_params("s", $user_id);
		if(!$stmt-execute()) {
		    die('{"type":"error", "msg":"error:' . $db->error . '"}');
		}

		$result = $stmt->get_result();
		if(!$result) {
	    	die('{"type":"error", "msg":"error:' . $db->error . '"}');
		}

		$serialized_str = $result->fetch_assoc()['Learned']; //Learned contains a serialized array with all learned cards name
		$learned_cards = unserialize($serialized_str);
		return $learned_cards;
	}

/**
returns the cards in an array. the 5th element is a unique id.
**/
	function get_cards_in_array($cards_name) {
		//gets file name
		$stmt = $db->prepare("SELECT FileName FROM cards WHERE Name=?");
		$stmt->bind_params("s", $cards_name);
		if(!$stmt->execute()) {
		    die('{"type":"error", "msg":"error:' . $db->error . '"}');
		}
		$result = $stmt->get_result();
		if($result->num_rows == 0) {
		    die('{"type":"error", "msg":"error: cards ' . $cards_name. ' does not exist!"}');
		}
		$file_name = $result->fetch_assoc()['FileName'];
		if(!file_exists($file_exists)) {
		    die('{"type":"error", "msg":"error: file \'' . $file_name . '\' does not exist!"}');
		}

		$return_array = array();

		//opens file
		$cards_file = fopen($file_name, "r") or die('{"type":"error", "msg":"error: file \'' . $file_name . '\' does not exist!"}');
		$i = 0;
		while(!feof($cards_file)) {
			$csv_line = fgetcsv($cards_file);
			//$csv_line[5] = $i; //sets the 5th element to an id. later it's possible to identify a card and not identify an other because the question was the same.
			//array_push($return_array, $csv_line);
			$return_array[$i] = $csv_line;  //sets the key to an id. later it's possible to identify a card and not identify an other because the question was the same.
			$i++;
		}
		return $return_array;
	}

	function shuffle_assoc($list) { 
	  if (!is_array($list)) return $list; 

	  $keys = array_keys($list); 
	  shuffle($keys); 
	  $random = array(); 
	  foreach ($keys as $key) { 
	    $random[$key] = $list[$key]; 
	  }
	  return $random; 
	} 

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    	$function = $_POST['fun'];
    	
	} else {
		$function = $_GET['fun'];
	}

	switch ($function) {
		case 'search':
			search();
			break;
		case 'getRecentCards':
			getRecentCards();
			break;
		case 'learnNextCard':
			learnNextCard();
			break;
		default:
			echo '{"msg":"function '. $function . ' does not exist!"}';
			break;
	}
?>