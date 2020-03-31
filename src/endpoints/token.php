<?php
namespace src\endpoints;

use src\interfaces;
use src\classes as classes;

class Token extends Endpoint implements interfaces\iEndpoint
{
    // return Session Id if Keys and Credentials are correct
    public function set()
    {
        if (
            isset($this->data['hash'])
            && isset($this->data['api_key'])
            && isset($this->data['email'])
            && isset($this->data['password'])
        ) {
            classes\Validate::get()->escapeStrings(
                $this->data['hash'],
                $this->data['api_key'],
                $this->data['email'],
                $this->data['password']
            );

            $validKeys = $this->authentication->validateKeys(
                $this->data['api_key'],
                $this->data['hash']
            );

            if (!$validKeys) {
                die(\json_encode('Invalid Keys'));
            }

            $result = $this->authentication->createSessionId($this->data);

            //If Login fails dont return further information so potential attackers cannot scount information
            if ($result === false) {
                die(json_encode(
                    array(
                        'error' => array(
                            'err_LoginForm' => 'Fehler bei Authentifizierung. Überprüfen Sie ihre Eingaben',
                        ),
                    )
                ));
            } else {
                die(\json_encode(
                    array(
                        'userToken' => $result,
                    )
                ));
            }
        } else {
            http_response_code(404);
        }
    }

    // return new Authentication keys
    public function get()
    {
        if (!empty($this->data)) {
            http_response_code(404);
        } else {
            $keys = $this->authentication->createKeys();
            die(json_encode($keys));
        }

    }

    public function update()
    {
        die('Endpoint not implemented');
    }

    //delete session Id
    public function delete()
    {
        if (!$this->validSession) {
            die(json_encode('Unauthenticated access'));
        }

        if (isset($this->data)
            && isset($this->data['userToken'])
            && isset($this->data['userToken']['sessionId'])
            && isset($this->data['userToken']['userId'])
        ) {
            classes\Validate::get()->escapeStrings(
                $this->data['userToken']['sessionId'],
                $this->data['userToken']['userId']
            );
            $result = $this->authentication->deleteSessionId($this->data['userToken']);

            die(json_encode(
                array(
                    'success' => $result,
                )
            ));

        } else {
            die('Error! Endpoint: token, action: delete');
        }
    }
}
