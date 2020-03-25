<?php
namespace src\classes;

// use src\interfaces;
// use src\endpoints as endpoints;
use src\database as db;

class AppFunctions
{

    protected $database;
    protected $validate;
    protected $authentication;
    protected $encrypt;

    public function __construct($encrypt = null)
    {
        $this->database = db\Database::get();
        $this->validate = Validate::get();
        $this->authentication = Authentication::get();
        $this->encrypt = $encrypt;
    }

    protected function setEncrypt($encrypt)
    {
        $this->encrypt = $encrypt();
    }
}
