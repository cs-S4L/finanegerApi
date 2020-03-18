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

        // $this->iv_size = \mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        // $this->iv = mcrypt_create_iv($this->iv_size, MCRYPT_RAND);

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

            // $cipher_method = 'aes-128-ctr';
            // $enc_key = openssl_digest(php_uname(), 'SHA256', true);
            // $enc_iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher_method));
            // $crypted_token = openssl_encrypt($token, $cipher_method, $enc_key, 0, $enc_iv) . "::" . bin2hex($enc_iv);

            // openssl_private_encrypt(
            //     $value,
            //     $args[$key],
            //     openssl_pkey_get_private($this->encryptKey)
            // );
            // $args[$key] = mcrypt_encrypt(
            //     MCRYPT_RIJNDAEL_256,
            //     $this->encryptKey,
            //     $value,
            //     MCRYPT_MODE_ECB,
            //     $this->iv
            // );
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

            // openssl_private_decrypt(
            //     $value,
            //     $args[$key],
            //     openssl_pkey_get_private($this->encryptKey)
            // );
            // $args[$key] = mcrypt_decrypt(
            //     MCRYPT_RIJNDAEL_256,
            //     $this->encryptKey,
            //     $value,
            //     MCRYPT_MODE_ECB,
            //     $this->iv
            // );
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
            "user_id = $userId"
        );

        if (empty($result)
            || count($result) > 1
        ) {
            return false;
        }

        return $result[0];
    }
}
