<?php
namespace src\endpoints;

use src\interfaces;
use src\classes as classes;
use src\database as db;

abstract class Endpoint {

	protected $validSession = false;
	protected $userId;
	protected $sessionId;
	protected $data;

	public function __construct($data) {
		$this->data = $data;

		if (isset($data['userToken'])) {
			$valid = classes\Authentication::get()->checkSessionId(
				$data['userToken']['userId'],
				$data['userToken']['sessionId']
			);
			if ($valid) {
				$this->validSession = true;
				$this->userId = $data['userToken']['userId'];
				$this->sessionId = $data['userToken']['sessionId'];
			} else {
				return;
			}
		} else {
			return;
		}
	}
}