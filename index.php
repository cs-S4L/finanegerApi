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

$data = $_POST;
$data = null;
if (!empty($_POST)) {
    $data = array();
    foreach ($_POST as $key => $value) {
		if (!is_array($value)) {
			$safeVal = htmlspecialchars($value);
			$safeKey = htmlspecialchars($key);

			$data[$safeKey] = $safeVal;
		} else {
			$data[$key] = $value;
		}
        
    }
}

// if ($data)

switch ($endpoint) {
	case 'token':
		$endpointController = new src\endpoints\Token($action);
		break;
    case 'user':
        $endpointController = new src\endpoints\User($action);
        break;
    case 'Finances':
        $endpointController = new src\endpoints\Finances();
		break;
	case 'Bills':
        $endpointController = new src\endpoints\Bills();
		break;
	case 'Accounts':
        $endpointController = new src\endpoints\Accounts();
        break;
	case 'FixedCosts':
        $endpointController = new src\endpoints\FixedCosts();
        break;
    default:
        break;
}

if (isset($endpointController)) {
	// $endpointController->executeEndpoint($data);
	switch ($action) {
		case 'set':
			$endpointController->set($data);
			break;
		case 'get':
			$endpointController->get($data);
			break;
		case 'update':
			$endpointController->update($data);
			break;
		case 'delete':
			$endpointController->delete($data);
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