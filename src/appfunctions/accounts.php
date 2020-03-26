<?php
namespace src\appfunctions;

class Accounts extends AppFunctions
{

    public function getAccounts($offset = '', $limit = '')
    {
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

        return $return;
    } //getAccounts

    public function getAccount($id)
    {
        $result = $this->database->readFromDatabase(
            'app_accounts',
            'user_id = \'' . $this->userId . '\' AND id = ' . $id
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

        return $return;
    } //getAccount

    public function createAccount(
        $description = '',
        $type = '',
        $bank = '',
        $balance = '',
        $owner = ''
    ) {
        $this->validate->convertToEnglishNumberFormat($balance);

        $this->encrypt()->encryptData(
            $description,
            $bank,
            $owner
        );

        $return = $this->database->insertIntoDatabase(
            'app_accounts',
            array(
                'user_id' => $this->userId,
                'description' => $description,
                'type' => $type,
                'bank' => $bank,
                'balance' => $balance,
                'owner' => $owner,
            )
        );

        return $return;
    } //createAccount

    public function updateAccount(
        $id,
        $description = '',
        $type = '',
        $bank = '',
        $balance = '',
        $owner = ''
    ) {
        $this->validate->convertToEnglishNumberFormat($balance);

        $this->encrypt()->encryptData(
            $description,
            $bank,
            $owner
        );

        $return = $this->database->updateDatabase(
            'app_accounts',
            array(
                'description' => $description,
                'type' => $type,
                'bank' => $bank,
                'balance' => $balance,
                'owner' => $owner,
            ),
            array(
                'user_id' => $this->userId,
                'id' => $id,
            )
        );

        return $return;
    } //updateAccount

    public function deleteAccount($id)
    {
        return $this->database->deleteFromDatabase(
            'app_accounts',
            'user_id = \'' . $this->userId . '\' AND id = \'' . $id . '\''
        );

    }

}
