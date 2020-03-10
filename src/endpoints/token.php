<?php
namespace src\endpoints;

use src\interfaces;
use src\classes as classes;

class Token extends Endpoint implements interfaces\iEndpoint
{
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

            $validKeys = classes\Authentication::get()->validateKeys(
                $this->data['api_key'],
                $this->data['hash']
            );

            if (!$validKeys) {
                die(\json_encode('Invalid Keys'));
            }

            $result = classes\Authentication::get()->createSessionId($this->data);

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

    public function get()
    {
        if (!empty($this->data)) {
            http_response_code(404);
        } else {
            $keys = classes\Authentication::get()->createKeys();
            die(json_encode($keys));
        }

    }

    public function update()
    {
        die('Endpoint not implemented');
    }

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
            $result = classes\Authentication::get()->deleteSessionId($this->data['userToken']);

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
