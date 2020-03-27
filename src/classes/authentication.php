<?php
namespace src\classes;

use src\database as db;

class Authentication
{
    protected $database;

    public function __construct()
    {
        $this->database = db\Database::get();
    }

    public static function get()
    {
        static $auth_object = null;

        if (is_null($auth_object)) {
            $auth_object = new self();
        }

        return $auth_object;
    }

    public function createKeys()
    {
        $return = array();

        $return['api_key'] = $this->createRandomKey();
        $return['auth_key'] = $this->createRandomKey();
        $return['timestamp'] = strtotime('+30 minutes');

        $result = $this->database->insertIntoDatabase(
            'auth_keys',
            array(
                'api_key' => $return['api_key'],
                'auth_key' => $return['auth_key'],
                'expireDate' => $return['timestamp'],
            )
        );

        if ($result) {
            return $return;
        } else {
            return false;
        }
    }

    public function validateKeys($api_key, $hash)
    {
        $this->clearKeys();

        $validKeys = true;
        $auth_key = $this->database->readFromDatabase(
            'auth_keys',
            'api_key = "' . $api_key . '"'
        );

        //wenn Auth Key nicht gefunden, mehrere gefunden oder abgelaufen
        if (empty($auth_key)) {
            return false;
        } else if (
            count($auth_key) > 1
            || time() > $auth_key[0]['expireDate']
        ) {
            $validKeys = false;
        }

        $auth_key = $auth_key[0];

        $serverHash = md5($auth_key['api_key'] . $auth_key['auth_key']);

        if ($serverHash != $hash) {
            $validKeys = false;
        }

        return $validKeys;
    }

    public function clearKeys()
    {
        $time = time() - 1;
        return $this->database->deleteFromDatabase(
            'auth_keys',
            "expireDate < $time"
        );
    }

    public function createSessionId($data)
    {
        $this->clearSessionIds();

        $userAuthentication = $this->authenticateUser($data['email'], $data['password']);

        if ($userAuthentication === false) {
            return false;
        }

        $sessionId = $this->createUniqueSessionId($userAuthentication);
        if ($sessionId) {
            return $sessionId;
        } else {
            return false;
        }
    }

    public function deleteSessionId($data)
    {
        $result = $this->database->deleteFromDatabase(
            'session_ids',
            'user_id = "' . $data['userId'] . '" AND session_id = "' . $data['sessionId'] . '"'
        );
        return true;
    }

    public function checkSessionId($userId, $sessionId)
    {
        $sessionIdDb = $this->database->readFromDatabase(
            'session_ids',
            'user_id = "' . $userId . '" AND session_id = "' . $sessionId . '"'
        );

        if (empty($sessionIdDb)) {
            return false;
        }
        if (count($sessionIdDb) > 1) {
            return false;
        }
        if (time() > $sessionIdDb[0]['expireDate']) {
            return false;
        }

        return true;
    }

    public function refreshSession($sessionId, $userId)
    {
        $result = $this->database->updateDatabase(
            'session_ids',
            array(
                'expireDate' => strtotime('+1 week'),
            ),
            array(
                'user_id' => $userId,
                'session_id' => $sessionId,
            )
        );
    }

    public function clearSessionIds()
    {
        $time = time() - 1;
        return $this->database->deleteFromDatabase(
            'session_ids',
            "expireDate < $time"
        );
    }

    private function authenticateUser($email, $password)
    {
        $userDb = $this->database->readFromDatabase(
            'users',
            'email = "' . $email . '"'
        );

        if (
            empty($userDb)
            || count($userDb) > 1
        ) {
            return false;
        }
        $userDb = $userDb[0];

        $verify = \password_verify($password, $userDb['password']);

        if ($verify) {
            return $userDb;
        } else {
            return false;
        }
    }

    private function createRandomKey()
    {
        return \hash('sha256', bin2hex(random_bytes(64)) . time());
    }

    private function createUniqueSessionId($user)
    {
        $sessionId = \hash('sha256', $user['email'] . $user['password'] . time());

        $result = $this->database->insertIntoDatabase(
            'session_ids',
            array(
                'user_id' => $user['id'],
                'session_id' => $sessionId,
                'expireDate' => strtotime('+1 week'),
            )
        );

        if ($result) {
            return array(
                'sessionId' => $sessionId,
                'userId' => $user['id'],
                'name' => $user['name'],
                'surname' => $user['surname'],
            );
        } else {
            return false;
        }
    }
}
