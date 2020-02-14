<?php
namespace src\endpoints;

use src\interfaces;
use src\classes as classes;
use src\database as db;

class Accounts extends Endpoint implements interfaces\iEndpoint {

	public function set() {
		if (!$this->validSession) {
			die(json_encode('Unauthenticated access'));
		}

		if (!isset($this->data)) {
			die('No Data given');
		}

		if (is_array($this->data['data'])) {
			$params = $this->data['data'];
		} else {
			$params = array();
			parse_str($this->data['data'], $params);
		}
		// die(json_encode($params));

		$insert = array();
		$insert['user_id'] = $this->userId;
		$insert['type'] = (isset($params['type'])) ? $params['type'] : '';
		$insert['description'] = (isset($params['description'])) ? $params['description'] : '';
		$insert['bank'] = (isset($params['bank'])) ? $params['bank'] : '';
		$insert['balance'] = (isset($params['balance'])) ? $params['balance'] : '';
		$insert['owner'] = (isset($params['owner'])) ? $params['owner'] : '';

		classes\Validate::get()->escapeStrings(
			$insert['user_id'],
			$insert['type'],
			$insert['description'],
			$insert['bank'],
			$insert['balance'],
			$insert['owner']
		);

		$return = db\Database::get()->insertIntoDatabase(
			'app_accounts',
			$insert
		);

		die(json_encode(array('success'=>true)));
	}

	public function get() {
		if (!$this->validSession) {
			die(json_encode('Unauthenticated access'));
		}

		// wenn id gesetzt ist, einzelnen Eintrag zurück geben
		if (isset($this->data['id'])) {
			classes\Validate::get()->escapeStrings($this->data['id']);

			$result = db\Database::get()->readFromDatabase(
				'app_accounts',
				'user_id = \''.$this->userId.'\' AND id = '. $this->data['id']
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
			$offset = (isset($this->data['offset']) && !empty($this->data['offset'])) ? $this->data['offset'] : '';
			$limit = (isset($this->data['limit']) && !empty($this->data['limit'])) ? $this->data['limit'] : '';

			classes\Validate::get()->escapeStrings($offset, $limit);

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
	
	public function update() {
		if (!$this->validSession) {
			die(json_encode('Unauthenticated access'));
		}

		if (!isset($this->data)) {
			die('No Data given');
		}

		if (is_array($this->data['data'])) {
			$params = $this->data['data'];
		} else {
			$params = array();
			parse_str($this->data['data'], $params);
		}

		$insert = array();
		$insert['type'] = (isset($params['type'])) ? $params['type'] : '';
		$insert['description'] = (isset($params['description'])) ? $params['description'] : '';
		$insert['bank'] = (isset($params['bank'])) ? $params['bank'] : '';
		$insert['balance'] = (isset($params['balance'])) ? $params['balance'] : '';
		$insert['owner'] = (isset($params['owner'])) ? $params['owner'] : '';

		classes\Validate::get()->escapeStrings(
			$insert['type'],
			$insert['description'],
			$insert['bank'],
			$insert['balance'],
			$insert['owner']
		);

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
	
	public function delete() {
		if (!$this->validSession) {
			die(json_encode('Unauthenticated access'));
		}

		if (!isset($this->data)) {
			die('No Data given');
		}

		if (is_array($this->data['data'])) {
			$params = $this->data['data'];
		} else {
			$params = array();
			parse_str($this->data['data'], $params);
		}

		classes\Validate::get()->escapeStrings($params['id']);

		$return = db\Database::get()->deleteFromDatabase(
			'app_accounts',
			'user_id = \''.$this->userId.'\' AND id = \''.$params['id'].'\''
		);

		die(\json_encode(array('success'=>true)));
	}

}

