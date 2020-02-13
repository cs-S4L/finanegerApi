<?php
namespace src\endpoints;

use src\interfaces;

class Finances implements interfaces\iEndpoint {

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
			if ($data['id'] === 'create') {
				//Seite zum erstellen eines neuen Eintrags wurde geöffnet
				//Nur Kontooptionen zurück geben
				echo json_encode(array(
					'accountOptions' => array(
						array('value'=>'acc1', 'name'=>'Konto1'),
						array('value'=>'acc2', 'name'=>'Konto2')
					)
				));
				die();
			}
			
			echo json_encode(array(
				'item' => array(
					'description' => 'test',
					'type' => 'test',
					'amount' => 'test',
					'account' => 'test',
					'date' => 'test',
					'note' => 'thats a note',
				),
				'accountOptions' => array(
					array('value'=>'acc1', 'name'=>'Konto1'),
					array('value'=>'acc2', 'name'=>'Konto2')
				)
			));
			die();
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
		
		echo json_encode($return);
		die();
	}
	
	public function update($data) {
		
	}
	
	public function delete($data) {
		
	}
}

