<?php
namespace src\endpoints;

use src\interfaces;

class ListFinances implements interfaces\iEndpoint {

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
                'left'=> 'left bla blub',
                'text'=> 'bla blub',
				'number'=> 'number',
            ),
            'id2' => array(
                'left'=> 'left',
                'text'=> 'bla blub',
                'number'=> 'number'

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

