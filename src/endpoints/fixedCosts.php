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
        $insert['startDate'] = (isset($params['startDate'])) ? $params['startDate'] : '';
        $insert['iteration'] = (isset($params['iteration'])) ? $params['iteration'] : '';
        $insert['lastValuation'] = (isset($params['lastValuation'])) ? $params['lastValuation'] : '';

        $this->validate->escapeStrings(
            $insert['user_id'],
            $insert['description'],
            $insert['type'],
            $insert['amount'],
            $insert['account'],
            $insert['note'],
            $insert['startDate'],
            $insert['iteration'],
            $insert['lastValuation']
        );

        $this->validate->convertToEnglishNumberFormat($insert['amount']);
        $this->validate->convertDateToTimestamp($insert['startDate']);

        $this->encrypt()->encryptData(
            $insert['description'],
            $insert['note']
        );

        $this->validate->validate(
            'pastDate', $insert['startDate'], $errors, 'startDate'
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

            if ($todayTimestamp == $insert['startDate']) {

                $this->finances->createFinance(
                    $this->userId,
                    date("d.m.Y") . ' Fixkostenpunkt: ' . $params['description'],
                    $params['type'],
                    $params['amount'],
                    $params['account'],
                    $params['startDate'],
                    $params['note']
                );

                $insert['lastValuation'] = $insert['startDate'];
            }
        }

        $return = $this->database->insertIntoDatabase(
            'app_fixedCosts',
            $insert
        );

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

                $this->validate->convertTimestampToDate($return['success']['startDate']);
                $this->validate->convertTimestampToDate($return['success']['lastValuation']);
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

                    $this->validate->convertTimestampToDate($value['startDate']);
                    // $this->validate->convertTimestampToDate($value['createDate']);
                    $this->validate->convertTimestampToDate($value['lastValuation']);
                    $this->validate->convertToGermanNumberFormat($value['amount']);
                    $return[$key] = $value;

                }
            }

        }

        die(json_encode($return));
    }

    public function update()
    {

    }

    public function delete()
    {

    }

}
