<?php if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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