<?php

namespace src\classes;

use src\database as db;

class Validate
{

    private $regexes = array(
        'email' => "/.{2,}@.{2,}\..+/",
        'word' => "/^[a-zA-ZöÖüÜäÄ]+$/",
        'digits' => "/^\d+$/",
    );

    public static function get()
    {
        static $validatorObject = null;

        if (is_null($validatorObject)) {
            $validatorObject = new self();
        }

        return $validatorObject;
    }

    public function escapeStrings(&...$args)
    {
        foreach ($args as $key => $value) {
            $args[$key] = htmlspecialchars(trim($value));
        }
    }

    public function validate($type, $value, &$errors, $errorKey)
    {
        switch ($type) {
            case 'email':
                if (empty($value)) {
                    $this->addError(
                        $errors,
                        $errorKey,
                        'Leere E-Mailadresse'
                    );
                    return;
                }
                if (!$this->checkRegex($value, $this->regexes['email'])) {
                    $this->addError(
                        $errors,
                        $errorKey,
                        'Invalide E-Mailadresse'
                    );
                }
                break;
            case 'word':
                if (empty($value)) {
                    $this->addError(
                        $errors,
                        $errorKey,
                        'Leeres Feld'
                    );
                    return;
                }
                if (!$this->checkRegex($value, $this->regexes['word'])) {
                    $this->addError(
                        $errors,
                        $errorKey,
                        'Invalide Zeichen. Nur Buchstaben erlaubt'
                    );
                }
                break;
            case 'digit':
                if (empty($value)) {
                    $this->addError(
                        $errors,
                        $errorKey,
                        'Leeres Feld'
                    );
                    return;
                }
                if (!$this->checkRegex($value, $this->regexes['digit'])) {
                    $this->addError(
                        $errors,
                        $errorKey,
                        'Invalide Zeichen. Nur Zahlen erlaubt'
                    );
                }
                break;
            case 'password':
                if (empty($value)) {
                    $this->addError(
                        $errors,
                        $errorKey,
                        'Leeres Passwort'
                    );
                    return;
                }
                if (\strlen($value) < 8) {
                    $this->addError(
                        $errors,
                        $errorKey,
                        'Passwort zu kurz! Das Passwort muss mindestens 8 Zeichen haben.'
                    );
                }
                break;
            default:
                if (empty($value)) {
                    $this->addError(
                        $errors,
                        $errorKey,
                        'Leeres Feld'
                    );
                    return;
                }
                break;
        }
    }

    public function checkEmailExists($email, &$errors, $errorKey)
    {
        $result = db\Database::get()->readFromDatabase(
            'users',
            'email = "' . $email . '"'
        );

        if (empty($result)) {
            return false;
        }

        $this->addError($errors, $errorKey, 'E-Mail existiert bereits');
        return;
    }

    private function addError(&$errors, $errorKey, $error)
    {
        $errors[$errorKey] = $error;
    }

    private function checkRegex($value, $regex)
    {
        if (
            \preg_match($regex, $value) == 1
        ) {
            return true;
        } else {
            return false;
        }
    }

}
