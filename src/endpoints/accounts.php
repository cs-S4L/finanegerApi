<?php
namespace src\endpoints;

use src\interfaces;

class Accounts implements interfaces\iEndpoint {

	public function set($data) {
		if (!isset($data)) {
			echo 'No Data given';
			die();
		}

		echo json_encode(array('success'=>true));
		// echo json_encode(array('error'=>array('amount' => 'Error amount')));
		die();
	}

	public function get($data) {
		// wenn id gesetzt ist, einzelnen Eintrag zurück geben
		if (isset($data['id'])) {
			echo json_encode(array(
				'item' => array(
					'description' => 'test',
					'bank' => 'test',
					'balance' => 'test',
					'owner' => 'test',
				),
			));
			die();
		}

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
	
	public function update($data) {
		
	}
	
	public function delete($data) {
		
	}

}

