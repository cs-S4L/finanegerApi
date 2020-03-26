<?php
namespace src\appfunctions;

class Finances extends AppFunctions
{

    public function getFinances($offset = '', $limit = '')
    {
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

        return $return;
    }

    public function getFinance($id)
    {
        $return = array(
            'success' => array(),
        );

        $result = $this->database->readFromDatabase(
            'app_finances',
            "user_id = '$this->userId' AND id = '{$id}'"
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

        return $return;

    }

    public function createFinance(
        $description = '',
        $type = '',
        $amount = '',
        $account = '',
        $date = '',
        $note = '',
        $fixedCost = ''
    ) {
        $this->validate->convertToEnglishNumberFormat($amount);
        $this->validate->convertDateToTimestamp($date);

        $this->encrypt()->encryptData(
            $description,
            $note
        );

        $success = $this->database->insertIntoDatabase(
            'app_finances',
            array(
                'user_id' => $this->userId,
                'description' => $description,
                'type' => $type,
                'amount' => $amount,
                'account' => $account,
                'date' => $date,
                'note' => $note,
                'fixedCost' => $fixedCost,
            )
        );

        if ($success
            && !empty($account)
        ) {
            if ($date <= time()) {
                $success = $this->database->addToValueInTable(
                    'app_accounts',
                    'balance',
                    $amount,
                    "user_id = {$this->userId} AND id = $account",
                    ($type == 'income') ? '+' : '-'
                );
            }
        }

        return $success;
    }

    public function updateFinance($userId, $params)
    {
        $insert = array();
        $insert['description'] = (isset($params['description'])) ? $params['description'] : '';
        $insert['type'] = (isset($params['type'])) ? $params['type'] : '';
        $insert['amount'] = (isset($params['amount'])) ? $params['amount'] : '';
        $insert['account'] = (isset($params['account'])) ? $params['account'] : '';
        $insert['date'] = (isset($params['date'])) ? $params['date'] : '';
        $insert['note'] = (isset($params['note'])) ? $params['note'] : '';

        $this->validate->convertToEnglishNumberFormat($insert['amount']);
        $this->validate->convertDateToTimestamp($insert['date']);

        $this->encrypt()->encryptData(
            $insert['description'],
            $insert['note']
        );

        $dbDataset = $this->database->readFromDatabase(
            'app_finances',
            "user_id = '$userId' AND id = '{$params['id']}'"
        );

        $dbDataset = $dbDataset[0];

        if (!empty($insert['account'])
            && $dbDataset['amount'] != $insert['amount']
        ) {
            $amount = null;
            $operator = null;
            if ($insert['type'] == 'spending') {
                if ($dbDataset['amount'] < $insert['amount']) {
                    $operator = '-';
                    $amount = $insert['amount'] - $dbDataset['amount'];
                } else {
                    $operator = '+';
                    $amount = $dbDataset['amount'] - $insert['amount'];
                }
            } else {
                if ($dbDataset['amount'] < $insert['amount']) {
                    $operator = '+';
                    $amount = $insert['amount'] - $dbDataset['amount'];

                } else {
                    $operator = '-';
                    $amount = $dbDataset['amount'] - $insert['amount'];
                }
            } // if ($insert['type'] == 'spending') {

            $sucess = $this->database->addToValueInTable(
                'app_accounts',
                'balance',
                $amount,
                "user_id = $userId AND id = {$insert['account']}",
                $operator
            );

        } // if (!empty($insert['account'])

        $return = $this->database->updateDatabase(
            'app_finances',
            $insert,
            array(
                'user_id' => $userId,
                'id' => $params['id'],
            )
        );
        return $return;
    }

    public function deleteFinance($userId, $params)
    {
        $return = $this->database->deleteFromDatabase(
            'app_finances',
            "user_id = {$userId} AND id = {$params['id']}"
        );

        if ($return && !empty($params['account'])) {
            $this->validate->convertToEnglishNumberFormat($params['amount']);

            $return = $this->database->addToValueInTable(
                'app_accounts',
                'balance',
                $params['amount'],
                "user_id = $userId AND id = {$params['account']}",
                ($params['type'] == 'income') ? '-' : '+'
            );
        }

        return $return;
    }
}
