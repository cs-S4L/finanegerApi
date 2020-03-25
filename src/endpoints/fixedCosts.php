<?php
namespace src\endpoints;

use src\interfaces;
use src\classes as classes;

class FixedCosts extends Endpoint implements interfaces\iEndpoint
{

    protected $finances;

    public function __construct($data)
    {
        parent::__construct($data);

        $this->finances = new classes\Finances($this->encrypt());
    }

    public function set()
    {
        $this->checkSession();

        $this->checkData();

        $this->convertData($params);

        $insert['user_id'] = $this->userId;
        $insert['description'] = (isset($params['description'])) ? $params['description'] : '';
        $insert['type'] = (isset($params['type'])) ? $params['type'] : '';
        $insert['amount'] = (isset($params['amount'])) ? $params['amount'] : '';
        $insert['account'] = (isset($params['account'])) ? $params['account'] : '';
        $insert['note'] = (isset($params['note'])) ? $params['note'] : '';
        $insert['iteration'] = (isset($params['iteration'])) ? $params['iteration'] : '';
        $insert['lastValuation'] = (isset($params['lastValuation'])) ? $params['lastValuation'] : '';
        $insert['nextValuation'] = (isset($params['nextValuation'])) ? $params['nextValuation'] : '';

        $this->validate->escapeStrings(
            $insert['user_id'],
            $insert['description'],
            $insert['type'],
            $insert['amount'],
            $insert['account'],
            $insert['note'],
            $insert['iteration'],
            $insert['lastValuation'],
            $insert['nextValuation']
        );

        $this->validate->convertToEnglishNumberFormat($insert['amount']);
        $this->validate->convertDateToTimestamp($insert['nextValuation']);

        $this->encrypt()->encryptData(
            $insert['description'],
            $insert['note']
        );

        $this->validate->validate(
            'pastDate', $insert['nextValuation'], $errors, 'nextValuation'
        );

        if (!empty($errors)) {
            die(\json_encode(
                array(
                    'error' => $errors,
                )
            ));
        }

        if (!empty($insert['account'])) {
            $todayTimestamp = strtotime('00:00:00');

            if ($todayTimestamp == $insert['nextValuation']) {

                $insert['lastValuation'] = $insert['nextValuation'];
                if ($insert['iteration'] == "weekly") {
                    $insert['nextValuation'] = strtotime('00:00:00 +1 week');

                } else {
                    $insert['nextValuation'] = strtotime('00:00:00 +1 month');
                    // $insert['nextValuation'] = strtotime(date('Y-m-d', strtotime("+30 days")));
                }
            }
        }

        $test = '25.04.2020';
        $this->validate->convertDateToTimestamp($test);

        $return = $this->database->insertIntoDatabase(
            'app_fixedCosts',
            $insert,
            $lastInsertId
        );

        if (!empty($insert['account'])) {
            $this->finances->createFinance(
                $this->userId,
                date("d.m.Y") . ' Fixkostenpunkt: ' . $params['description'],
                $params['type'],
                $params['amount'],
                $params['account'],
                $params['nextValuation'],
                $params['note'],
                $lastInsertId
            );
        }
        die(json_encode(array('success' => $return)));
    }

    public function get()
    {
        $this->checkSession();

        $this->checkData();

        if (isset($this->data['id'])) {
            $return = array(
                'success' => array(),
            );

            $result = $this->database->readFromDatabase(
                'app_fixedCosts',
                "user_id = '$this->userId' AND id = '{$this->data['id']}'"
            );

            $empty = !empty($result);
            $isset = isset($result[0]);
            $countRes = count($result);
            $count = count($result) <= 1;

            if (!empty($result)
                && isset($result[0])
                && count($result) == 1
            ) {
                $return['success'] = $result[0];

                $this->encrypt()->decryptData(
                    $return['success']['description'],
                    $return['success']['note']

                );

                $this->validate->convertTimestampToDate($return['success']['lastValuation']);
                $this->validate->convertTimestampToDate($return['success']['nextValuation']);
                $this->validate->convertToGermanNumberFormat($return['success']['amount']);
            }

            die(\json_encode($return));

        } else {
            $offset = (isset($this->data['offset']) && !empty($this->data['offset'])) ? $this->data['offset'] : '';
            $limit = (isset($this->data['limit']) && !empty($this->data['limit'])) ? $this->data['limit'] : '';

            $this->validate->escapeStrings($offset, $limit);

            $result = $this->database->readFromDatabase(
                'app_fixedCosts',
                'user_id = \'' . $this->userId . '\'',
                '*',
                $limit,
                'createDate',
                'DESC',
                $offset
            );

            $return = array();

            if (!empty($result) && \is_array($result)) {
                foreach ($result as $key => $value) {
                    $this->encrypt()->decryptData(
                        $value['description'],
                        $value['note']
                    );

                    $this->validate->convertTimestampToDate($value['lastValuation']);
                    $this->validate->convertTimestampToDate($value['nextValuation']);
                    $this->validate->convertToGermanNumberFormat($value['amount']);
                    $return[$key] = $value;

                }
            }

        }

        die(json_encode($return));
    }

    public function update()
    {
        $this->checkSession();

        $this->checkData();

        $this->convertData($params);

        $insert['description'] = (isset($params['description'])) ? $params['description'] : '';
        $insert['type'] = (isset($params['type'])) ? $params['type'] : '';
        $insert['amount'] = (isset($params['amount'])) ? $params['amount'] : '';
        $insert['account'] = (isset($params['account'])) ? $params['account'] : '';
        $insert['note'] = (isset($params['note'])) ? $params['note'] : '';
        $insert['iteration'] = (isset($params['iteration'])) ? $params['iteration'] : '';
        // $insert['lastValuation'] = (isset($params['lastValuation'])) ? $params['lastValuation'] : '';
        $insert['nextValuation'] = (isset($params['nextValuation'])) ? $params['nextValuation'] : '';

        $this->validate->escapeStrings(
            $insert['description'],
            $insert['type'],
            $insert['amount'],
            $insert['account'],
            $insert['note'],
            $insert['iteration']
        );

        $this->validate->convertToEnglishNumberFormat($insert['amount']);
        $this->validate->convertDateToTimestamp($insert['nextValuation']);

        $this->encrypt()->encryptData(
            $insert['description'],
            $insert['note']
        );

        $this->validate->validate(
            'pastDate', $insert['nextValuation'], $errors, 'nextValuation'
        );

        if (!empty($errors)) {
            die(\json_encode(
                array(
                    'error' => $errors,
                )
            ));
        }

        $dbDataset = $this->database->readFromDatabase(
            'app_fixedCosts',
            "user_id = '$this->userId' AND id = '{$params['id']}'"
        );

        $dbDataset = $dbDataset[0];

        //wiederholrate wurde geändert
        if ($dbDataset['iteration'] != $insert['iteration']) {
            //Nächste Wertstellung wurde nicht geändert
            if ($dbDataset['nextValuation'] == $insert['nextValuation']) {
                if ($insert['iteration'] == "weekly") {
                    $insert['nextValuation'] = strtotime('00:00:00 +1 week');
                } else {
                    $insert['nextValuation'] = strtotime('00:00:00 +1 month');
                }
                //Nächste Wertstellung wurde geändert
            } else {
                //prüfen ob neue Wertstellung heute ist, Wenn ja passende Finance anlegen
                $todayTimestamp = strtotime('00:00:00');

                if ($todayTimestamp == $insert['nextValuation']) {

                    $insert['lastValuation'] = $insert['nextValuation'];
                    if ($insert['iteration'] == "weekly") {
                        $insert['nextValuation'] = strtotime('00:00:00 +1 week');
                    } else {
                        $insert['nextValuation'] = strtotime('00:00:00 +1 month');
                    }

                    $this->finances->createFinance(
                        $this->userId,
                        date("d.m.Y") . ' Fixkostenpunkt: ' . $params['description'],
                        $params['type'],
                        $params['amount'],
                        $params['account'],
                        $params['nextValuation'],
                        $params['note'],
                        $params['id']
                    );

                }
            } //else
        } else {
            //Nächste Wertstellung wurde geändert
            if ($dbDataset['nextValuation'] != $insert['nextValuation']) {
                //prüfen ob neue Wertstellung heute ist, Wenn ja passende Finance anlegen6
                $todayTimestamp = strtotime('00:00:00');

                if ($todayTimestamp == $insert['nextValuation']) {

                    $insert['lastValuation'] = $insert['nextValuation'];
                    if ($insert['iteration'] == "weekly") {
                        $insert['nextValuation'] = strtotime('+1 week');
                    } else {
                        $insert['nextValuation'] = strtotime('+1 month');
                    }

                    $this->finances->createFinance(
                        $this->userId,
                        date("d.m.Y") . ' Fixkostenpunkt: ' . $params['description'],
                        $params['type'],
                        $params['amount'],
                        $params['account'],
                        $params['nextValuation'],
                        $params['note'],
                        $params['id']
                    );

                } // if ($todayTimestamp == $insert['nextValuation']) {

            }
        } //if ($dbDataset['iteration'] != $insert['iteration']) {

        $return = $this->database->updateDatabase(
            'app_fixedCosts',
            $insert,
            array(
                'user_id' => $this->userId,
                'id' => $params['id'],
            )
        );

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

        $return = $this->database->deleteFromDatabase(
            'app_fixedCosts',
            "user_id = {$this->userId} AND id = {$params['id']}"
        );

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
