<?php
namespace src\endpoints;

use src\interfaces;
use src\appfunctions as appfunctions;

class Accounts extends Endpoint implements interfaces\iEndpoint
{
    protected $accounts;

    public function __construct($data)
    {
        parent::__construct($data);

        $this->accounts = new appfunctions\Accounts($this->userId);
    }

    public function set()
    {
        $this->checkSession();

        $this->checkData();

        $this->convertData($params);

        $this->validate->escapeStrings(
            $params['description'],
            $params['type'],
            $params['bank'],
            $params['balance'],
            $params['owner']
        );

        $this->accounts->createAccount(
            $params['description'],
            $params['type'],
            $params['bank'],
            $params['balance'],
            $params['owner']
        );

        die(json_encode(array('success' => true)));
    }

    public function get()
    {
        $this->checkSession();

        $this->checkData();

        // wenn id gesetzt ist, einzelnen Eintrag zurück geben
        if (isset($this->data['id'])) {
            $this->validate->escapeStrings($this->data['id']);

            $return = $this->accounts->getAccount($this->data['id']);

            die(\json_encode($return));
        } else {
            $this->validate->escapeStrings(
                $this->data['offset'],
                $this->data['limit']
            );

            $return = $this->accounts->getAccounts(
                $this->data['offset'],
                $this->data['limit']
            );

            die(\json_encode($return));
        }
    }

    public function update()
    {
        $this->checkSession();

        $this->checkData();

        $this->convertData($params);

        $this->validate->escapeStrings(
            $params['id'],
            $params['description'],
            $params['type'],
            $params['bank'],
            $params['balance'],
            $params['owner']
        );

        $return = $this->accounts->updateAccount(
            $params['id'],
            $params['description'],
            $params['type'],
            $params['bank'],
            $params['balance'],
            $params['owner']
        );

        die(json_encode(array('success' => true)));
    }

    public function delete()
    {
        $this->checkSession();

        $this->checkData();

        $this->convertData($params);

        $this->validate->escapeStrings($params['id']);

        $return = $this->database->deleteFromDatabase(
            'app_accounts',
            'user_id = \'' . $this->userId . '\' AND id = \'' . $params['id'] . '\''
        );

        die(\json_encode(array('success' => true)));
    }

}
