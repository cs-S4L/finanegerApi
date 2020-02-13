<?php
//this has to be changed in Production
header('Access-Control-Allow-Origin: *');

require ('src/init.php');

if (empty($_GET) || empty($_GET['endpoint'])) {
    //send no further information so potential Attackers can not scout information
    http_response_code(404);
} else {
    $endpoint = $_GET['endpoint'];
}

$data = null;
if (!empty($_POST)) {
    $data = array();
    foreach ($_POST as $key => $value) {
        $safeVal = htmlspecialchars($value);
        $safeKey = htmlspecialchars($key);
        
        $data[$safeKey] = $safeVal;
    }
}

switch ($endpoint) {
    case 'login':
        $endpointController = new src\endpoints\Login();
        break;
    case 'register':
        $endpointController = new src\endpoints\Register();
        break;
    case 'getFinances':
        $endpointController = new src\endpoints\listFinances();
		break;
	case 'getBills':
        $endpointController = new src\endpoints\listBills();
        break;
    default:
        break;
}

if (isset($endpointController)) {
    $endpointController->executeEndpoint($data);
} else {
    http_response_code(404);
}
exit();
?>