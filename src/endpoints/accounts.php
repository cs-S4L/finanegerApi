<?php
namespace src\endpoints;

use src\interfaces;

class Accounts extends Endpoint implements interfaces\iEndpoint
{

    public function set()
    {
        $this->checkSession();

        $this->checkData();

        $this->convertData($params);

        $insert = array();
        $insert['user_id'] = $this->userId;
        $insert['type'] = (isset($params['type'])) ? $params['type'] : '';
        $insert['description'] = (isset($params['description'])) ? $params['description'] : '';
        $insert['bank'] = (isset($params['bank'])) ? $params['bank'] : '';
        $insert['balance'] = (isset($params['balance'])) ? $params['balance'] : '';
        $insert['owner'] = (isset($params['owner'])) ? $params['owner'] : '';

        $this->validate->escapeStrings(
            $insert['user_id'],
            $insert['type'],
            $insert['description'],
            $insert['bank'],
            $insert['balance'],
            $insert['owner']
        );

        $this->validate->convertToEnglishNumberFormat($insert['balance']);

        $this->encrypt()->encryptData(
            $insert['description'],
            $insert['bank'],
            $insert['owner']
        );

        $return = $this->database->insertIntoDatabase(
            'app_accounts',
            $insert
        );

        die(json_encode(array('success' => true)));
    }

    public function get()
    {
        $this->checkSession();

        $this->checkData();

        // wenn id gesetzt ist, einzelnen Eintrag zurück geben
        if (isset($this->data['id'])) {
            $this->validate->escapeStrings($this->data['id']);

            $result = $this->database->readFromDatabase(
                'app_accounts',
                'user_id = \'' . $this->userId . '\' AND id = ' . $this->data['id']
            );

            $return = array(
                'item' => array(),
            );

            if (!empty($result)
                && isset($result[0])
                && count($result)
            ) {
                $return['item'] = $result[0];

                $this->encrypt()->decryptData(
                    $return['item']['description'],
                    $return['item']['bank'],
                    $return['item']['owner']
                );

                $this->validate->convertToGermanNumberFormat($return['item']['balance']);
            }
            die(\json_encode($return));

        } else {
            $offset = (isset($this->data['offset']) && !empty($this->data['offset'])) ? $this->data['offset'] : '';
            $limit = (isset($this->data['limit']) && !empty($this->data['limit'])) ? $this->data['limit'] : '';

            $this->validate->escapeStrings($offset, $limit);

            $result = $this->database->readFromDatabase(
                'app_accounts',
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
                    $this->validate->convertToGermanNumberFormat($value['balance']);

                    $this->encrypt()->decryptData(
                        $value['description'],
                        $value['bank'],
                        $value['owner']
                    );

                    $return[$key] = $value;

                }
            }

            // $return['balance'] = \str_replace('.', ',', $return['balance']);

            die(\json_encode($return));
        }
    }

    public function update()
    {
        $this->checkSession();

        $this->checkData();

        $this->convertData($params);

        $insert = array();
        $insert['type'] = (isset($params['type'])) ? $params['type'] : '';
        $insert['description'] = (isset($params['description'])) ? $params['description'] : '';
        $insert['bank'] = (isset($params['bank'])) ? $params['bank'] : '';
        $insert['balance'] = (isset($params['balance'])) ? $params['balance'] : '';
        $insert['owner'] = (isset($params['owner'])) ? $params['owner'] : '';

        $this->validate->escapeStrings(
            $insert['type'],
            $insert['description'],
            $insert['bank'],
            $insert['balance'],
            $insert['owner']
        );

        $this->validate->convertToEnglishNumberFormat($insert['balance']);

        $this->encrypt()->encryptData(
            $insert['description'],
            $insert['bank'],
            $insert['owner']
        );

        $return = $this->database->updateDatabase(
            'app_accounts',
            $insert,
            array(
                'user_id' => $this->userId,
                'id' => $params['id'],
            )
        );

        die(json_encode(array('success' => true)));
    }

    public function delete()
    {
        $this->checkSession();

        $this->checkData();

        $this->convertData($params);

        $this->validate->escapeStrings($params['id']);

        $return = $this->database->deleteFromDatabase(
            'app_accounts',
            'user_id = \'' . $this->userId . '\' AND id = \'' . $params['id'] . '\''
        );

        die(\json_encode(array('success' => true)));
    }

}
