<?php
namespace src\database;

class Database {

	private $conn;

	public function __construct() {
		try {
			global $cfg;

        $dsn = "mysql:host={$cfg['db']["host"]};dbname={$cfg['db']["name"]};charset=utf8mb4;port={$cfg['db']["port"]}";

        $this->conn = new \PDO($dsn, $cfg['db']["user"], $cfg['db']["password"], array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+00:00'"));
		}
		catch(\PDOException $e)
		{
			die("Connection failed: " . $e->getMessage());
		}
	}

	public static function get() {
        static $database_object = null;

        if (is_null($database_object)) $database_object = new self();

        return $database_object;
    }

	public function insertIntoDatabase($table, $data) {
		$fields = "";
		$params = "";

		foreach ($data as $key => $value) {
			$fields .= "$key, ";
			$params .= ":$key, ";
		}

		$fields = rtrim($fields ,", ");
		$params = rtrim($params ,", ");

		$sql = $this->conn->prepare(
			"INSERT INTO $table ($fields) VALUES ($params)"
		);

		foreach ($data as $key => &$value) {
			$sql->bindParam(":$key", $value);
		}

		return $sql->execute();
	}
	public function readFromDatabase($table, $where = null, $selection = '*', $fetchMode=\PDO::FETCH_ASSOC) {
		$statement = "";
		if (is_null($where)) {
			$statement = "SELECT $selection FROM $table";
		} else {
			$statement = "SELECT $selection FROM $table WHERE $where";
		}
		
		$sql = $this->conn->prepare($statement);
		
		// $this->logDatabaseAccess($statement);
		
		$sql->execute();
		
		return $sql->fetchAll($fetchMode);
	}
	
	public function deleteFromDatabase($table, $where = null, $fetchMode=\PDO::FETCH_ASSOC) {
		$statement = "";
		if (is_null($where)) {
			return false;
		}
		$statement = "DELETE FROM $table WHERE $where";

		$sql = $this->conn->prepare($statement);

		// $this->logDatabaseAccess($statement);

		$sql->execute();

		return $sql->fetchAll($fetchMode);
	}
	
	// public function updateDatabase($table, $values, $where=null) {
	// 	$statement = "";
	// 	$hasWhere = !is_null($where);

    //     $set = '';
    //     foreach($values as $key => $value) {
    //     	$set .= "$key = :$key";
    //     }

    //     if ($hasWhere) {
    //     	$whereString = '';
    //     	foreach($where as $key => $value) {
    //     		$whereString .= "$key = :$key";
    //     	}
    //     }

	// 	if ($hasWhere) {
	// 		$statement = "UPDATE $table SET $set WHERE $whereString";
	// 	} else {
	// 		$statement = "UPDATE $table SET $set";
	// 	}

	// 	$sql = $this->conn->prepare($statement);

	// 	foreach($values as $key => $value) {
	// 		$sql->bindValue(":$key", $value);
	// 	}

	// 	if ($hasWhere) {
	// 		foreach($where as $key => $value) {
	// 			$sql->bindValue(":$key", $value);
	// 		}
	// 	}

	// 	return $sql->execute();
	// }

	private function logDatabaseAccess($statement) {
		if (SERVER_MODE == 'PRODUCTION') {
			return;
		}

		$log = date('m/d/Y h:i:s a', time()).':      '.$statement;

		//copied from stackoverflow.com
		//https://stackoverflow.com/questions/24972424/create-or-write-append-in-text-file
 		$myfile = file_put_contents(DIR__ROOT.'dbLogs.txt', $log.PHP_EOL , FILE_APPEND | LOCK_EX);

	}

}