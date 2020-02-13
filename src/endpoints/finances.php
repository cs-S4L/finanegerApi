<?php
namespace src\endpoints;

use src\interfaces;

class Finances implements interfaces\iEndpoint {

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
				'date'=> 'left bla blub',
				'description'=> 'bla blub',
				'amount'=> 'number',
			),
			'id2' => array(
				'date'=> 'left',
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

