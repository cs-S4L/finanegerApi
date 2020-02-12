<?php
namespace src\endpoints;

use src\interfaces;

class ListFinances implements interfaces\iEndpoint {

    public function executeEndpoint($data) {
        if (is_null($data)) {
            //check for User in Session
        } else {
            //check credentials
        }

        $return = array(
            'id1' => array(
                'left'=> 'left',
                'text'=> 'bla blub',
                'number'=> 'number'

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

