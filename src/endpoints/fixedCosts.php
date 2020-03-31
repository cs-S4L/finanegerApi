<?php
namespace src\endpoints;

use src\interfaces;
use src\appfunctions as appfunctions;
use src\classes as classes;

class FixedCosts extends Endpoint implements interfaces\iEndpoint
{

    protected $fixedCosts;

    public function __construct($data)
    {
        parent::__construct($data);

        $this->fixedCosts = new appfunctions\FixedCosts($this->userId);
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
            $params['note'],
            $params['iteration'],
            $params['lastValuation'],
            $params['nextValuation']
        );

        $return = $this->fixedCosts->createFixedCost(
            $params['description'],
            $params['type'],
            $params['amount'],
            $params['account'],
            $params['note'],
            $params['iteration'],
            $params['lastValuation'],
            $params['nextValuation']
        );

        die(json_encode(array('success' => $return)));
    }

    public function get()
    {
        $this->checkSession();

        $this->checkData();

        // if id is set return single entry
        if (isset($this->data['id'])) {
            $this->validate->escapeStrings($this->data['id']);

            $return = $this->fixedCosts->getFixedCost($this->data['id']);
        } else {
            $this->validate->escapeStrings(
                $this->data['offset'],
                $this->data['limit']
            );

            $return = $this->fixedCosts->getFixedCosts(
                $this->data['offset'],
                $this->data['limit']
            );
        }

        die(json_encode($return));
    }

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
            $params['note'],
            $params['iteration']
        );

        $return = $this->fixedCosts->updateFixedCost($params);

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

    public function delete()
    {
        $this->checkSession();

        $this->checkData();

        $this->convertData($params);

        $this->validate->escapeStrings($params['id']);

        $return = $this->fixedCosts->deleteFixedCost($params['id']);

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
