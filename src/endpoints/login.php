<?php
namespace src\endpoints;

use src\interfaces;

class Login implements interfaces\iEndpoint {

	public function set($data) {
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
		die();
	}

	public function get($data) {
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
	
	public function update($data) {
		echo('Action not implemented');
		die();
	}
	
	public function delete($data) {
		echo('Action not implemented');	
		die();	
	}

    public function executeEndpoint($data) {
		echo('Action not implemented');
		die();
    }

}

