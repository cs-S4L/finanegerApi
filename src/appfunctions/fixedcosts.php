<?php
namespace src\appfunctions;

class FixedCosts extends AppFunctions
{

    protected $finances;

    public function __construct($userId = null)
    {
        parent::__construct($userId);

        $this->finances = new Finances($this->userId);
    }

    public function getFixedCosts($offset, $limit)
    {
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

        return $return;
    }

    public function getFixedCost($id)
    {
        $return = array(
            'success' => array(),
        );

        $result = $this->database->readFromDatabase(
            'app_fixedCosts',
            "user_id = '$this->userId' AND id = '{$id}'"
        );

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

        return $return;
    }

    public function createFixedCost(
        $description,
        $type,
        $amount,
        $account,
        $note,
        $iteration,
        $lastValuation,
        $nextValuation
    ) {
        $insert['user_id'] = $this->userId;
        $insert['description'] = $description;
        $insert['type'] = $type;
        $insert['amount'] = $amount;
        $insert['account'] = $account;
        $insert['note'] = $note;
        $insert['iteration'] = $iteration;
        $insert['lastValuation'] = $lastValuation;
        $insert['nextValuation'] = $nextValuation;

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

        $return = $this->database->insertIntoDatabase(
            'app_fixedCosts',
            $insert,
            $lastInsertId
        );

        if (!empty($insert['account'])) {
            $this->finances->createFinance(
                date("d.m.Y") . ' Fixkostenpunkt: ' . $description,
                $type,
                $amount,
                $account,
                $nextValuation,
                $note,
                $lastInsertId
            );
        }

        return $return;
    }

    public function updateFixedCost($params)
    {
        $insert['description'] = (isset($params['description'])) ? $params['description'] : '';
        $insert['type'] = (isset($params['type'])) ? $params['type'] : '';
        $insert['amount'] = (isset($params['amount'])) ? $params['amount'] : '';
        $insert['account'] = (isset($params['account'])) ? $params['account'] : '';
        $insert['note'] = (isset($params['note'])) ? $params['note'] : '';
        $insert['iteration'] = (isset($params['iteration'])) ? $params['iteration'] : '';
        // $insert['lastValuation'] = (isset($params['lastValuation'])) ? $params['lastValuation'] : '';
        $insert['nextValuation'] = (isset($params['nextValuation'])) ? $params['nextValuation'] : '';

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

        return $return;
    }

    public function deleteFixedCost($id)
    {
        return $this->database->deleteFromDatabase(
            'app_fixedCosts',
            "user_id = {$this->userId} AND id = {$id}"
        );
    }
}