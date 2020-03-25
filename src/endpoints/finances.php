<?php
namespace src\endpoints;

use src\interfaces;
use src\classes as classes;
use src\database as db;

class Finances extends Endpoint implements interfaces\iEndpoint
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

        $success = $this->finances->createFinance(
            $this->userId,
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
    } //get()

    public function update()
    {
        $this->checkSession();

        $this->checkData();

        $this->convertData($params);

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
