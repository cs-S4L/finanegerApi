<?php
namespace src\endpoints;

use src\interfaces;
use src\classes as classes;
use src\database as db;

class User implements interfaces\iEndpoint {
	public function set($data) {
		if (
			isset($data['hash']) 
			&& isset($data['api_key'])
			&& isset($data['email'])
			&& isset($data['password'])
		) {
			/**
			* TODO validate
			*/
			$userData = array(
				'email' => $data['email'],
				'password' => password_hash($data['password'], PASSWORD_BCRYPT),
				'name' => $data['name'],
				'surname' => $data['surname'],
			);

			$result = db\Database::get()->insertIntoDatabase('users', $userData);
			
			$result = classes\Authentication::get()->createSessionId($data);

			if ($result === false) {
				die(json_encode(
					array(
						'error' => array(
							'err_LoginForm' =>'Fehlerhafte Eingabe'
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
		//TODO
		die('Endpoint not implemented');
	}

	public function update($data){
		//TODO
		die('Endpoint not implemented');
	}

	public function delete($data) {
		//TODO
		die('Endpoint not implemented');
	}
}
