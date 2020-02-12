<?php
namespace src\endpoints;

use src\interfaces;

class Login implements interfaces\iEndpoint {

    public function executeEndpoint($data) {
        if (is_null($data)) {
            //check for User in Session
        } else {
            //check credentials
        }

        $return = array(
            // Login erfolgreich oder noch eingeloggter User
            'userToken' => array(
                'email'=> 'Christopherschmitz96@web.de',
                'password'=> 'someHash',
            ),
            // login nicht erfolgreich
            // 'error' => array(
            //     'err_LoginForm' =>'testError'
            // ),
            //wenn keine data übergeben und kein User vorhanden leer
        );
        // $return = '';

        echo json_encode($return);
        die();
    }

}

