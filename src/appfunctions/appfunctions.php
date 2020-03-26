<?php
namespace src\appfunctions;

// use src\interfaces;
use src\classes as classes;
use src\database as db;

class AppFunctions
{
    protected $userId;

    protected $database;
    protected $validate;
    protected $authentication;

    public function __construct($userId = null)
    {
        $this->userId = $userId;
        $this->database = db\Database::get();
        $this->validate = classes\Validate::get();
        $this->authentication = classes\Authentication::get();
    }

    protected function encrypt()
    {
        static $encryptObject = null;

        if (is_null($encryptObject)) {
            $encryptObject = new classes\Encrypt($this->userId);
        }

        return $encryptObject;
    }
}
