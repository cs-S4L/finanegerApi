<?php
namespace src\endpoints;

use src\interfaces;

class ListBills implements interfaces\iEndpoint {

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
                'dueDate'=> 'left bla blub',
                'description'=> 'bla blub',
				'amount'=> 'number',
            ),
            'id2' => array(
                'dueDate'=> 'left',
                'description'=> 'bla blub',
                'amount'=> 'number'

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

