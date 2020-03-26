<?php
namespace src\endpoints;

use src\interfaces;
use src\appfunctions as appfunctions;

class Bills extends Endpoint implements interfaces\iEndpoint
{

    protected $bills;

    public function __construct($data)
    {
        parent::__construct($data);

        $this->bills = new appfunctions\Bills($this->userId);
    }

    public function set()
    {
        $this->checkSession();

        $this->checkData();

        $this->convertData($params);

        $this->validate->escapeStrings(
            $params['description'],
            $params['dueDate'],
            $params['amount'],
            $params['account'],
            $params['payed'],
            $params['note']
        );

        $success = $this->bills->createBill(
            $params['description'],
            $params['dueDate'],
            $params['amount'],
            $params['account'],
            $params['payed'],
            $params['note']
        );

        die(json_encode(array('success' => $success)));
    }

    public function get()
    {
        $this->checkSession();

        $this->checkData();

        // wenn id gesetzt ist, einzelnen Eintrag zurück geben
        if (isset($this->data['id'])) {
            $this->validate->escapeStrings(
                $this->data['id']
            );

            $return = $this->bills->getBill($this->data['id']);
        } else {
            $this->validate->escapeStrings(
                $this->data['offset'],
                $this->data['limit']
            );

            $return = $this->bills->getBills(
                $this->data['offset'],
                $this->data['limit']
            );

        } //if (isset($this->data['id'])) {

        die(json_encode($return));
    }

    public function update()
    {
        $this->checkSession();

        $this->checkData();

        $this->convertData($params);

        $this->validate->escapeStrings(
            $params['description'],
            $params['dueDate'],
            $params['amount'],
            $params['account'],
            $params['payed'],
            $params['note']
        );

        $return = $this->bills->updateBill(
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
            $params['id']
        );

        $return = $this->bills->deleteBill($params['id']);

        if ($return) {
            die(\json_encode(array('success' => true)));
        } else {
            die(\json_encode(
                array(
                    'error' => array('Error' => 'Something went wrong!'),
                )
            ));

        }
    }

}
