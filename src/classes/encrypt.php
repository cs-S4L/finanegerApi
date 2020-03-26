<?php

namespace src\classes;

use src\database as db;

class Encrypt
{
    //initialization Vector Size
    private $iv_size;
    //initialization Vector
    private $iv;
    private $encryptKey;

    public function __construct($userId)
    {
        $this->database = db\Database::get();

        $this->encryptKey = $this->getUserEncryptKey($userId);
    }

    public function encryptData(&...$args)
    {
        $cipherMethod = 'aes-128-ctr';
        $encKey = openssl_digest($this->encryptKey, 'SHA256', true);
        $encIv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipherMethod));

        foreach ($args as $key => $value) {

            $args[$key] = openssl_encrypt(
                $value,
                $cipherMethod,
                $encKey,
                0,
                $encIv
            ) . "::" . bin2hex($encIv);
        }
    }

    public function decryptData(&...$args)
    {
        $cipherMethod = 'aes-128-ctr';
        $encKey = openssl_digest($this->encryptKey, 'SHA256', true);
        foreach ($args as $key => $value) {
            list($args[$key], $encIv) = explode("::", $value);
            $args[$key] = openssl_decrypt(
                $args[$key],
                $cipherMethod,
                $encKey,
                0,
                hex2bin($encIv)
            );
        }
    }

    public static function createUserEncryptKey($randomString)
    {
        return \hash('sha256', time() . $randomString);
    }

    private function getUserEncryptKey($userId)
    {
        $result = $this->database->readFromDatabase(
            'users',
            "id = $userId"
        );

        if (empty($result)
            || count($result) > 1
        ) {
            return false;
        }

        return $result[0]['encryptKey'];
    }
}
