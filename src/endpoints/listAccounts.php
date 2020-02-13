<?php
namespace src\endpoints;

use src\interfaces;

class ListAccounts implements interfaces\iEndpoint {

    public function executeEndpoint($data) {
		//return just one if data.id is set
        if (is_null($data)) {
			//check for User in Session
			echo ('test');
        } else {
            //check credentials
        }

        $return = array(
            'id1' => array(
                'type'=> 'left bla blub',
                'description'=> 'bla blub',
				'balance'=> 'number',
            ),
            'id2' => array(
                'type'=> 'left',
                'description'=> 'bla blub',
                'balance'=> 'number'

            ),
            
        );

        // $return = {
        //     'id1' => {
        //     },
        //     ),
        // };

        echo json_encode($return);
        die();
    }

}

