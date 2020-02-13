<?php
namespace src\endpoints;

use src\interfaces;

class FixedCosts implements interfaces\iEndpoint {

	public function set($data) {

	}

	public function get($data) {
		//return just one if data.id is set
		if (is_null($data)) {
			//check for User in Session
			echo ('test');
		} else {
			//check credentials
		}
	
		$return = array(
			'id1' => array(
				'date'=> 'date',
				'iteration'=> 'iteration',
				'description'=> 'description',
				'amount'=> 'amount',
			),
			'id2' => array(
				'date'=> 'left',
				'iteration'=> 'left',
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
	
	public function update($data) {
		
	}
	
	public function delete($data) {
		
	}

}

