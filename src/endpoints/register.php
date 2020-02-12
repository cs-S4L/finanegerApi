<?php
namespace src\endpoints;

use src\interfaces;

class Register implements interfaces\iEndpoint {

    public function executeEndpoint($data) {
        if (is_null($data)) {
            echo 'No Data received!';
            die();
        }

        $return = array(
            //erfolgreich
            // 'userToken' => array(
            //     'email' => 'email',
            //     'password' => 'someHash'
            // ),
            //nicht erfolgreich
            'error' => array(
                'err_RegisterForm' => 'Etwas ist schief gelaufen. Bitte prüfe deine Eingabe!',
                'err_RegisterName' => 'Der Name xy',
            )
        );

        echo json_encode($return);
    }
}