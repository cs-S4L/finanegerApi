<?php
namespace src\classes;

// use src\interfaces;
// use src\endpoints as endpoints;
use src\database as db;

class Accounts
{

    protected $database;

    public function __construct()
    {
        $this->database = db\Database::get();
    }

    public static function get()
    {
        static $accountsObject = null;

        if (is_null($accountsObject)) {
            $accountsObject = self();
        }

        return $accountsObject;
    }
}
