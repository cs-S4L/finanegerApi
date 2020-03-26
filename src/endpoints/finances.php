<?php
namespace src\endpoints;

use src\interfaces;
use src\appfunctions as appfunctions;

class Finances extends Endpoint implements interfaces\iEndpoint
{

    protected $finances;

    public function __construct($data)
    {
        parent::__construct($data);

        $this->finances = new appfunctions\Finances($this->userId);
    }

    public function set()
    {
        $this->checkSession();

        $this->checkData();

        $this->convertData($params);

        $this->validate->escapeStrings(
            $params['description'],
            $params['type'],
            $params['amount'],
            $params['account'],
            $params['date'],
            $params['note']
        );

        $success = $this->finances->createFinance(
            $params['description'],
            $params['type'],
            $params['amount'],
            $params['account'],
            $params['date'],
            $params['note']
        );

        die(json_encode(array('success' => $success)));

    } //set

    public function get()
    {
        $this->checkSession();

        $this->checkData();

        // wenn id gesetzt ist, einzelnen Eintrag zurück geben
        if (isset($this->data['id'])) {
            $this->validate->escapeStrings(
                $this->data['id']
            );

            $return = $this->finances->getFinance($this->data['id']);
        } else {
            $this->validate->escapeStrings(
                $this->data['offset'],
                $this->data['limit']
            );

            $return = $this->finances->getFinances(
                $this->data['offset'],
                $this->data['limit']
            );

        } //if (isset($this->data['id'])) {

        die(json_encode($return));
    } //get()

    public function update()
    {
        $this->checkSession();

        $this->checkData();

        $this->convertData($params);

        $this->validate->escapeStrings(
            $params['description'],
            $params['type'],
            $params['amount'],
            $params['account'],
            $params['date'],
            $params['note']
        );

        $return = $this->finances->updateFinance(
            $this->userId,
            $params
        );

        die(json_encode(array('success' => true)));

    } //update

    public function delete()
    {
        $this->checkSession();

        $this->checkData();

        $this->convertData($params);

        $this->validate->escapeStrings(
            $params['id'],
            $params['description'],
            $params['type'],
            $params['amount'],
            $params['account'],
            $params['date'],
            $params['note']
        );

        $return = $this->finances->deleteFinance($this->userId, $params);

        if ($return) {
            die(\json_encode(array('success' => true)));
        } else {
            die(\json_encode(
                array(
                    'error' => array('Error' => 'Something went wrong!'),
                )
            ));

        }
    } //delete
}
