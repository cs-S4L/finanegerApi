<?php
//this has to be changed in Production
header('Access-Control-Allow-Origin: *');

session_start();

require ('src/init.php');

if (empty($_GET) || empty($_GET['endpoint']) || empty($_GET['action'])) {
    //send no further information so potential Attackers can not scout information
	http_response_code(404);
} else {
	$endpoint = $_GET['endpoint'];
	$action = $_GET['action'];
}

switch ($endpoint) {
	case 'token':
		$endpointController = new src\endpoints\Token($_POST);
		break;
    case 'user':
        $endpointController = new src\endpoints\User($_POST);
        break;
    // case 'Finances':
    //     $endpointController = new src\endpoints\Finances($_POST);
	// 	break;
	// case 'Bills':
    //     $endpointController = new src\endpoints\Bills($_POST);
	// 	break;
	case 'Accounts':
        $endpointController = new src\endpoints\Accounts($_POST);
        break;
	// case 'FixedCosts':
    //     $endpointController = new src\endpoints\FixedCosts($_POST);
    //     break;
    default:
        break;
}

if (isset($endpointController)) {
	switch ($action) {
		case 'set':
			$endpointController->set();
			break;
		case 'get':
			$endpointController->get();
			break;
		case 'update':
			$endpointController->update();
			break;
		case 'delete':
			$endpointController->delete();
			break;
		default:
			echo('Unkown Action! '.$action.'not Found!');
			break;
	}
} else {
    http_response_code(404);
}
exit();
?>