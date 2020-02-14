<?php
namespace src\classes;

use src\database as db;

class Authentication {

	public function __construct() {}

	public static function get() {
        static $auth_object = null;

        if (is_null($auth_object)) $auth_object = new self();

        return $auth_object;
	}
	
	public function createKeys() {
		$return = array();

		$return['api_key'] = $this->createRandomKey();
		$return['auth_key'] = $this->createRandomKey();
		$return['timestamp'] = strtotime('+30 minutes');

		$result = db\Database::get()->insertIntoDatabase(
			'auth_keys',
			 array(
				'api_key' => $return['api_key'],
				'auth_key' => $return['auth_key'],
				'expireDate' => $return['timestamp']
			)
		);

		if ($result) {
			return $return;
		} else {
			return false;
		}
	}

	public function createSessionId($data) {
		$auth_key = db\Database::get()->readFromDatabase(
			'auth_keys',
			'api_key = "'.$data['api_key'].'"'
		);

		//wenn Auth Key nicht gefunden, mehrere gefunden oder abgelaufen
		if (empty($auth_key)
			|| count($auth_key) > 1	
			|| time() > $auth_key[0]['expireDate']
		) {
			return false;
		}
		$auth_key = $auth_key[0];

		$serverHash = md5($auth_key['api_key'].$auth_key['auth_key']);

		if ($serverHash != $data['hash']) {
			return false;
		}

		$userAuthentication = $this->authenticateUser($data['email'], $data['password']);

		if (!$userAuthentication) {
			return false;
		}

		$sessionId = $this->createUniqueSessionId($data['email'], $data['password']);
		if ($sessionId) {
			return $sessionId;
		} else {
			return false;
		}
	}

	public function deleteSessionId($data) {
		// die(json_encode($data));
		$result = db\Database::get()->deleteFromDatabase(
			'session_ids',
			'user_id = "'.$data['userId'].'" AND session_id = "'.$data['sessionId'].'"'
		);
		return true;
	}

	public function checkSessionId($userId, $sessionId) {
		$sessionIdDb = db\Database::get()->readFromDatabase(
			'session_ids',
			'user_id = "'.$userId.'" AND session_id = "'.$sessionId.'"'
		);

		if (empty($sessionIdDb)
			|| count($sessionIdDb) > 1	
			|| time() > $sessionIdDb[0]['expireDate']
		) {
			return false;
		} else {
			return true;
		}

	}

	private function authenticateUser($email, $password) {
		$userDb = db\Database::get()->readFromDatabase(
			'users',
			'email = "'.$email.'"'
		);

		if (
			empty($userDb)
			|| count($userDb) > 1
		) {
			return false;
		}
		$userDb = $userDb[0];

		
		$verify = \password_verify($password, $userDb['password']);
		
		if ($verify) {
			return true;
		} else {
			return false;
		}
	}

	private function createRandomKey() {
		return md5(bin2hex(random_bytes(64)).time());
	}

	private function createUniqueSessionId($email, $password) {
		
		$sessionId = \hash('sha256', $email.$password.time());

		$result = db\Database::get()->insertIntoDatabase(
			'session_ids',
			 array(
				'user_id' => $email,
				'session_id' => $sessionId,
				'expireDate' => strtotime('+1 week')
			)
		);

		if ($result) {
			return array(
				'sessionId' => $sessionId,
				'userId' => $email
			);
		} else {
			return false;
		}
	}
}