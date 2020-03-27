<?php
namespace src\endpoints;

use src\interfaces;
use src\classes as classes;
use src\database as db;

abstract class Endpoint
{

    protected $validSession = false;
    protected $userId;
    protected $sessionId;
    protected $data;

    protected $database;
    protected $validate;
    protected $authentication;

    public function __construct($data)
    {
        $this->data = $data;

        $this->database = db\Database::get();
        $this->validate = classes\Validate::get();
        $this->authentication = classes\Authentication::get();

        if (isset($data['userToken'])) {
            $this->validate->escapeStrings(
                $data['userToken']['userId'],
                $data['userToken']['sessionId']
            );

            $valid = $this->authentication->checkSessionId(
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

    protected function checkSession()
    {
        if (!$this->validSession) {
            die(json_encode('Unauthenticated access'));
        }

        $this->authentication->refreshSession($this->sessionId, $this->userId);
    }

    protected function checkData()
    {
        if (!isset($this->data)) {
            die(json_encode('No Data given'));
        }
    }

    //falls data nicht von String zu Array umgewandelt wurde
    protected function convertData(&$params)
    {
        if (is_array($this->data['data'])) {
            $params = $this->data['data'];
        } else {
            $params = array();
            parse_str($this->data['data'], $params);
        }
    }
}
