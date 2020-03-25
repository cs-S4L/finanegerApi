<?php
namespace src\classes;

// use src\interfaces;
// use src\endpoints as endpoints;
use src\database as db;

class Finances extends AppFunctions
{
    public function createFinance(
        $userId,
        $description = '',
        $type = '',
        $amount = '',
        $account = '',
        $date = '',
        $note = ''
    ) {
        $this->validate->escapeStrings(
            $userId,
            $description,
            $type,
            $amount,
            $account,
            $date,
            $note
        );

        $this->validate->convertToEnglishNumberFormat($amount);
        $this->validate->convertDateToTimestamp($date);

        $this->encrypt->encryptData(
            $description,
            $note
        );

        $success = $this->database->insertIntoDatabase(
            'app_finances',
            array(
                'user_id' => $userId,
                'description' => $description,
                'type' => $type,
                'amount' => $amount,
                'account' => $account,
                'date' => $date,
                'note' => $note,
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
                    "user_id = $userId AND id = $account",
                    ($type == 'income') ? '+' : '-'
                );
            }
        }

        return $success;
    }

    public function updateFinance($userId, $params)
    {
        $insert = array();
        // $insert['id'] = (isset($params['id'])) ? $params['id'] : '';
        $insert['description'] = (isset($params['description'])) ? $params['description'] : '';
        $insert['type'] = (isset($params['type'])) ? $params['type'] : '';
        $insert['amount'] = (isset($params['amount'])) ? $params['amount'] : '';
        $insert['account'] = (isset($params['account'])) ? $params['account'] : '';
        $insert['date'] = (isset($params['date'])) ? $params['date'] : '';
        $insert['note'] = (isset($params['note'])) ? $params['note'] : '';

        $this->validate->escapeStrings(
            // $insert['id'],
            $insert['description'],
            $insert['type'],
            $insert['amount'],
            $insert['account'],
            $insert['date'],
            $insert['note']
        );

        $this->validate->convertToEnglishNumberFormat($insert['amount']);
        $this->validate->convertDateToTimestamp($insert['date']);

        $this->encrypt->encryptData(
            $insert['description'],
            $insert['note']
        );

        $dbDataset = $this->database->readFromDatabase(
            'app_finances',
            // 'user_id = \'' . $this->userId . '\' AND id = \'' . $this->data['id'] . '\'',
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
        $this->validate->escapeStrings($params['id']);

        $return = $this->database->deleteFromDatabase(
            'app_finances',
            "user_id = {$userId} AND id = {$params['id']}"
            // "user_id = 910 AND id = {$params['id']}"
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
