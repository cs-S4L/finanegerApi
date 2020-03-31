<?php
namespace src\endpoints;

use src\classes as classes;
use src\database as db;
use src\interfaces;

class User extends Endpoint implements interfaces\iEndpoint
{
    //create new User and return according userToken
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
                $this->data['email'],
                $this->data['password'],
                $this->data['name'],
                $this->data['surname']
            );

            $validKeys = $this->authentication->validateKeys(
                $this->data['api_key'],
                $this->data['hash']
            );

            if (!$validKeys) {
                die(\json_encode('Invalid Keys'));
            }

            $errors = array();

            classes\Validate::get()->validate(
                'email', $this->data['email'], $errors, 'err_RegisterMail'
            );
            classes\Validate::get()->validate(
                'word', $this->data['name'], $errors, 'err_RegisterName'
            );
            classes\Validate::get()->validate(
                'word', $this->data['surname'], $errors, 'err_RegisterSurname'
            );
            classes\Validate::get()->validate(
                'password', $this->data['password'], $errors, 'err_RegisterPassword'
            );

            classes\Validate::get()->checkEmailExists($this->data['email'], $errors, 'err_RegisterMail');

            if (!empty($errors)) {
                die(\json_encode(
                    array(
                        'error' => $errors,
                    )
                ));
            }

            $encryptKey = classes\Encrypt::createUserEncryptKey(
                "{$this->data['email']}{$this->data['password']}{$this->data['name']}{$this->data['surname']}"
            );

            $userData = array(
                'email' => $this->data['email'],
                'password' => password_hash($this->data['password'], PASSWORD_BCRYPT),
                'name' => $this->data['name'],
                'surname' => $this->data['surname'],
                'encryptKey' => $encryptKey,
            );

            $result = db\Database::get()->insertIntoDatabase(
                'users',
                $userData
            );

            $result = $this->authentication->createSessionId($this->data);

            if ($result === false) {
                die(json_encode(
                    array(
                        'error' => array(
                            'err_LoginForm' => 'Fehler!',
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
            die(\json_encode($this->data));
        } else {
            http_response_code(404);
        }
    }

    public function get()
    {
        //TODO
        die('Endpoint not implemented');
    }

    public function update()
    {
        //TODO
        die('Endpoint not implemented');
    }

    public function delete()
    {
        //TODO
        die('Endpoint not implemented');
    }
}
