<?php
namespace src\endpoints;

use src\interfaces;
use src\classes as classes;

class Token implements interfaces\iEndpoint {
	public function set($data) {
		if (
			isset($data['hash']) 
			&& isset($data['api_key'])
			&& isset($data['email'])
			&& isset($data['password'])
		) {
			$result = classes\Authentication::get()->createSessionId($data);
	
			if ($result === false) {
				die(json_encode(
					array(
						'error' => array(
							'err_LoginForm' =>'Fehler bei Authentifizierung. Überprüfen Sie ihre Eingaben'
						)
					)
				));
			} else {
				die(\json_encode(
					array(
						'userToken' => $result
					)
				));
			}
			die(\json_encode($data));
		} else {
			http_response_code(404);
		}
	}

	public function get($data) {
		if (!empty($data)) {
			http_response_code(404);
		} else {
			$keys = classes\Authentication::get()->createKeys();
			die(json_encode($keys));
		}

	}

	public function update($data){
		die('Endpoint not implemented');
	}

	public function delete($data) {
		if (isset($data)
			&& isset($data['userToken'])
			&& isset($data['userToken']['sessionId'])
			&& isset($data['userToken']['userId'])
		) {
			$result = classes\Authentication::get()->deleteSessionId($data['userToken']);

			die(json_encode(
				array(
					'success' => $result
				)
			));

		} else {
			die('Error! Endpoint: token, action: delete');
		}
	}
}