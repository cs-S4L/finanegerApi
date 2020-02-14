<?php
namespace src\endpoints;

use src\interfaces;
use src\classes as classes;
use src\database as db;

class Accounts extends Endpoint implements interfaces\iEndpoint {

	public function set($data) {
		if (!$this->validSession) {
			die(json_encode('Unauthenticated access'));
		}

		if (!isset($data)) {
			die('No Data given');
		}

		if (is_array($data['data'])) {
			$params = $data['data'];
		} else {
			$params = array();
			parse_str($data['data'], $params);
		}
		// die(json_encode($params));

		$insert = array();
		$insert['user_id'] = $this->userId;
		$insert['type'] = (isset($params['type'])) ? $params['type'] : '';
		$insert['description'] = (isset($params['description'])) ? $params['description'] : '';
		$insert['bank'] = (isset($params['bank'])) ? $params['bank'] : '';
		$insert['balance'] = (isset($params['balance'])) ? $params['balance'] : '';
		$insert['owner'] = (isset($params['owner'])) ? $params['owner'] : '';

		$return = db\Database::get()->insertIntoDatabase(
			'app_accounts',
			$insert
		);

		die(json_encode(array('success'=>true)));
	}

	public function get($data) {
		if (!$this->validSession) {
			die(json_encode('Unauthenticated access'));
		}

		// wenn id gesetzt ist, einzelnen Eintrag zurück geben
		if (isset($data['id'])) {
			$result = db\Database::get()->readFromDatabase(
				'app_accounts',
				'user_id = \''.$this->userId.'\' AND id = '. $data['id']
			);

			$return = array(
				'item' => array()
			);

			if (!empty($result) 
				&& isset($result[0])
				&& count($result)
			) {
				$return['item'] = $result[0];

				switch ($return['item']['type']) {
					case 'checking':
						$return['item']['type'] = 'Girokonto';
						break;
					case 'saving':
						$return['item']['type'] = 'Sparkonto';
						break;
					default:
						$return['item']['type'] = '';
				}
			}
			die(\json_encode($return));
			
		} else {
			$offset = (isset($data['offset']) && !empty($data['offset'])) ? $data['offset'] : '';
			$limit = (isset($data['limit']) && !empty($data['limit'])) ? $data['limit'] : '';

			// $result = db\Database::get()->readFromDatabase(
			// 	'app_accounts',
			// 	'user_id = root',
			// 	'*'
			// );
			$result = db\Database::get()->readFromDatabase(
				'app_accounts',
				// 'user_id = "root"',
				'user_id = \''.$this->userId.'\'',
				'*',
				$limit,
				'createDate',
				'DESC',
				$offset
			);

			$return = array();
			if (!empty($result) && \is_array($result)) {
				foreach ($result as $key => $value) {
					switch ($value['type']) {
						case 'checking':
							$value['type'] = 'Girokonto';
							break;
						case 'saving':
							$value['type'] = 'Sparkonto';
							break;
						default:
							$value['type'] = '';
					}
					$return[$value['id']] = $value;
					
				}
			}

			die(\json_encode($return));
		}
	}
	
	public function update($data) {
		if (!$this->validSession) {
			die(json_encode('Unauthenticated access'));
		}

		if (!isset($data)) {
			die('No Data given');
		}

		if (is_array($data['data'])) {
			$params = $data['data'];
		} else {
			$params = array();
			parse_str($data['data'], $params);
		}

		$insert = array();
		$insert['type'] = (isset($params['type'])) ? $params['type'] : '';
		$insert['description'] = (isset($params['description'])) ? $params['description'] : '';
		$insert['bank'] = (isset($params['bank'])) ? $params['bank'] : '';
		$insert['balance'] = (isset($params['balance'])) ? $params['balance'] : '';
		$insert['owner'] = (isset($params['owner'])) ? $params['owner'] : '';

		$return = db\Database::get()->updateDatabase(
			'app_accounts',
			$insert,
			array(
				'user_id' => $this->userId,
				'id' => $params['id']
			)
		);

		die(json_encode(array('success'=>true)));
	}
	
	public function delete($data) {
		if (!$this->validSession) {
			die(json_encode('Unauthenticated access'));
		}

		if (!isset($data)) {
			die('No Data given');
		}

		if (is_array($data['data'])) {
			$params = $data['data'];
		} else {
			$params = array();
			parse_str($data['data'], $params);
		}

		$return = db\Database::get()->deleteFromDatabase(
			'app_accounts',
			'user_id = \''.$this->userId.'\' AND id = \''.$params['id'].'\''
		);

		die(\json_encode(array('success'=>true)));
	}

}

