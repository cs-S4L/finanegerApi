<?php
namespace src\appfunctions;

class Bills extends AppFunctions
{

    protected $finances;

    public function __construct($userId = null)
    {
        parent::__construct($userId);

        $this->finances = new Finances($this->userId);
    }

    public function getBills($offset = '', $limit = '')
    {
        $result = $this->database->readFromDatabase(
            'app_bills',
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

                $this->validate->convertToGermanNumberFormat($value['amount']);
                $this->validate->convertTimestampToDate($value['dueDate']);
                if ($value['payed'] != 0) {
                    $this->validate->convertTimestampToDate($value['payed']);
                } else {
                    $value['payed'] = false;
                }
                $return[$key] = $value;

            }
        }

        return $return;
    }

    public function getBill($id)
    {
        $return = array(
            'success' => array(),
        );

        $result = $this->database->readFromDatabase(
            'app_bills',
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

            $this->validate->convertToGermanNumberFormat($return['success']['amount']);
            $this->validate->convertTimestampToDate($return['success']['dueDate']);

            if ($return['success']['payed'] != 0) {
                $this->validate->convertTimestampToDate($return['success']['payed']);
            } else {
                $return['success']['payed'] = false;
            }
        }

        return $return;

    }

    public function createBill(
        $description = '',
        $dueDate = '',
        $amount = '',
        $account = '',
        $payed = '',
        $note = ''
    ) {
        $insert = array(
            'user_id' => $this->userId,
            'description' => $description,
            'dueDate' => $dueDate,
            'amount' => $amount,
            'account' => (!empty($account)) ? $account : 0,
            'payed' => ($payed) ? strtotime('00:00:00') : 0,
            'note' => $note,
        );

        $this->validate->convertToEnglishNumberFormat($insert['amount']);
        $this->validate->convertDateToTimestamp($insert['dueDate']);

        $this->encrypt()->encryptData(
            $insert['description'],
            $insert['note']
        );

        $success = $this->database->insertIntoDatabase(
            'app_bills',
            $insert,
            $lastInsertId
        );

        if ($payed) {
            $this->finances->createFinance(
                date("d.m.Y") . ' RE: ' . $description,
                'spending',
                $amount,
                $account,
                strtotime('00:00:00'),
                $note,
                0,
                $lastInsertId
            );
        }

        return $success;
    }

    public function updateBill($params)
    {
        $insert = array();
        if (isset($params['description'])) {
            $insert['description'] = $params['description'];
            $this->encrypt()->encryptData($insert['description']);
        }
        if (isset($params['dueDate'])) {
            $insert['dueDate'] = $params['dueDate'];
            $this->validate->convertDateToTimestamp($insert['dueDate']);
        }
        if (isset($params['amount'])) {
            $insert['amount'] = $params['amount'];
            $this->validate->convertToEnglishNumberFormat($insert['amount']);
        }
        if (isset($params['account'])) {
            $insert['account'] = $params['account'];
            if (empty($insert['account'])) {
                $insert['account'] = 0;
            }
        }
        if (!empty($params['payed'])) {
            $insert['payed'] = $params['payed'];
        } else {
            $insert['payed'] = 0;
        }
        if (isset($params['note'])) {
            $insert['note'] = $params['note'];
            $this->encrypt()->encryptData($insert['note']);
        }

        $dbDataset = $this->database->readFromDatabase(
            'app_bills',
            "user_id = '$this->userId' AND id = '{$params['id']}'"
        );

        $dbDataset = $dbDataset[0];

        if (!empty($dbDataset['payed'])) {
            die(json_encode('Bezahlte Rechnungen können nicht mehr editiert werden.'));
        }

        $todayTimestamp = strtotime('00:00:00');

        if ($params['payed'] == "on") {
            if (isset($params['account'])) {
                $this->finances->createFinance(
                    date("d.m.Y") . ' RE: ' . $params['description'],
                    'spending',
                    $params['amount'],
                    $params['account'],
                    $todayTimestamp,
                    $params['note'],
                    0,
                    $params['id']
                );
            }

            $insert['payed'] = $todayTimestamp;
        }

        $return = $this->database->updateDatabase(
            'app_bills',
            $insert,
            array(
                'user_id' => $this->userId,
                'id' => $params['id'],
            )
        );

        return $return;
    }

    public function deleteBill($id)
    {
        $return = $this->database->deleteFromDatabase(
            'app_bills',
            "user_id = {$this->userId} AND id = {$id}"
        );

        return $return;

    }
}
