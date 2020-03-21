<?php
namespace src\endpoints;

use src\interfaces;
use src\classes as classes;
use src\database as db;

class Finances extends Endpoint implements interfaces\iEndpoint
{

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
        $insert['date'] = (isset($params['date'])) ? $params['date'] : '';
        $insert['note'] = (isset($params['note'])) ? $params['note'] : '';

        $this->validate->escapeStrings(
            $insert['user_id'],
            $insert['description'],
            $insert['type'],
            $insert['amount'],
            $insert['account'],
            $insert['date'],
            $insert['note']
        );

        $this->validate->convertToEnglishNumberFormat($insert['amount']);
        $this->validate->convertDateToTimestamp($insert['date']);

        $this->encrypt()->encryptData(
            $insert['description'],
            $insert['note']
        );

        $return = $this->database->insertIntoDatabase(
            'app_finances',
            $insert
        );

        if (!empty($insert['account'])) {
            $time = time();
            if ($insert['date'] <= $time) {
                $sucess = $this->database->addToValueInTable(
                    'app_accounts',
                    'balance',
                    $insert['amount'],
                    "user_id = $this->userId AND id = {$insert['account']}",
                    ($insert['type'] == 'income') ? '+' : '-'
                );
            }
        }

        die(json_encode(array('success' => $return)));

    }

    public function get()
    {
        $this->checkSession();

        $this->checkData();

        // wenn id gesetzt ist, einzelnen Eintrag zurück geben
        if (isset($this->data['id'])) {
            $return = array(
                'success' => array(),
            );

            $result = $this->database->readFromDatabase(
                'app_finances',
                // 'user_id = \'' . $this->userId . '\' AND id = \'' . $this->data['id'] . '\'',
                "user_id = '$this->userId' AND id = '{$this->data['id']}'"
            );

            if (!empty($result)
                && isset($result[0])
                && count($result)
            ) {
                $return['success'] = $result[0];

                $this->encrypt()->decryptData(
                    $return['success']['description'],
                    $return['success']['note']
                );

                $this->validate->convertTimestampToDate($return['success']['date']);
                $this->validate->convertToGermanNumberFormat($return['success']['amount']);

            }

            die(\json_encode($return));

        } else {
            $offset = (isset($this->data['offset']) && !empty($this->data['offset'])) ? $this->data['offset'] : '';
            $limit = (isset($this->data['limit']) && !empty($this->data['limit'])) ? $this->data['limit'] : '';

            $this->validate->escapeStrings($offset, $limit);

            $result = $this->database->readFromDatabase(
                'app_finances',
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

                    $this->validate->convertTimestampToDate($value['date']);
                    $this->validate->convertToGermanNumberFormat($value['amount']);
                    $return[$key] = $value;

                }
            }
        } //if (isset($this->data['id'])) {

        die(json_encode($return));
    }

    public function update()
    {

    }

    public function delete()
    {
        $this->checkSession();

        $this->checkData();

        $this->convertData($params);

        $this->validate->escapeStrings($params['id']);

        $return = $this->database->deleteFromDatabase(
            'app_finances',
            "user_id = {$this->userId} AND id = {$params['id']}"
            // "user_id = 910 AND id = {$params['id']}"
        );

        if ($return && !empty($params['account'])) {
            $this->validate->convertToEnglishNumberFormat($params['amount']);

            $return = $this->database->addToValueInTable(
                'app_accounts',
                'balance',
                $params['amount'],
                "user_id = $this->userId AND id = {$params['account']}",
                ($params['type'] == 'income') ? '-' : '+'
            );
        }

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
