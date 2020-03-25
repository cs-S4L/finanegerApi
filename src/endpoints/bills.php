<?php
namespace src\endpoints;

use src\interfaces;

class Bills extends Endpoint implements interfaces\iEndpoint
{

    protected $finances;

    public function __construct($data)
    {
        parent::__construct($data);

        $this->finances = new classes\Finances($this->encrypt());
    }

    public function set()
    {

    }

    public function get()
    {
        //return just one if data.id is set
        if (is_null($this->data)) {
            //check for User in Session
            echo ('test');
        } else {
            //check credentials
        }

        $return = array(
            'id1' => array(
                'dueDate' => '20.04.2020',
                'description' => 'bla blub',
                'amount' => 'number',
            ),
            'id2' => array(
                'dueDate' => '20.04.2020',
                'description' => 'bla blub',
                'amount' => 'number',

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

    public function update()
    {

    }

    public function delete()
    {

    }

}
