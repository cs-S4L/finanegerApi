<?php
namespace src\database;

class Database
{

    private $conn;

    public function __construct()
    {
        try {
            global $cfg;

            $dsn = "mysql:host={$cfg['db']["host"]};dbname={$cfg['db']["name"]};charset=utf8mb4;port={$cfg['db']["port"]}";

            $this->conn = new \PDO($dsn, $cfg['db']["user"], $cfg['db']["password"], array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+00:00'"));
            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        } catch (\PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public static function get()
    {
        static $database_object = null;

        if (is_null($database_object)) {
            $database_object = new self();
        }

        return $database_object;
    }

    public function insertIntoDatabase($table, $data, &$lastInsertId = '', &$error = '')
    {
        $fields = "";
        $params = "";

        foreach ($data as $key => $value) {
            $fields .= "$key, ";
            $params .= ":$key, ";
        }

        $fields = rtrim($fields, ", ");
        $params = rtrim($params, ", ");

        $sql = $this->conn->prepare(
            "INSERT INTO $table ($fields) VALUES ($params)"
        );

        foreach ($data as $key => &$value) {
            $sql->bindParam(":$key", $value);
        }

        try {
            $return = $sql->execute();
            $lastInsertId = $this->conn->lastInsertId();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
        return $return;
    }

    public function readFromDatabase(
        $table,
        $where = null,
        $selection = '*',
        $limit = '',
        $order = '',
        $direction = '',
        $offset = '',
        $fetchMode = \PDO::FETCH_ASSOC
    ) {
        $statement = "";
        if (is_null($where)) {
            $statement = "SELECT $selection FROM $table";
        } else {
            $statement = "SELECT $selection FROM $table WHERE $where";
        }

        if (!empty($order)) {
            $statement .= " ORDER BY $order $direction";

        }

        if (!empty($limit)) {
            $statement .= " Limit $limit";

            if (!empty($offset)) {
                $statement .= " OFFSET $offset";
            }
        }

        $sql = $this->conn->prepare($statement);

        $sql->execute();

        return $sql->fetchAll($fetchMode);
    }

    public function deleteFromDatabase(
        $table,
        $where = null,
        $fetchMode = \PDO::FETCH_ASSOC
    ) {
        $statement = "";
        if (is_null($where)) {
            return false;
        }
        $statement = "DELETE FROM $table WHERE $where";

        $sql = $this->conn->prepare($statement);

        return $sql->execute();
    }

    public function updateDatabase(
        $table,
        $values,
        $where = null
    ) {
        $statement = "";
        $hasWhere = !is_null($where);

        $set = '';
        foreach ($values as $key => $value) {
            if (empty($set)) {
                $set .= "$key = :$key";
            } else {
                $set .= ", $key = :$key";
            }
        }

        if ($hasWhere) {
            $whereString = '';
            foreach ($where as $key => $value) {
                if (empty($whereString)) {
                    $whereString .= "$key = :$key";
                } else {
                    $whereString .= " AND $key = :$key";
                }
            }
        }

        if ($hasWhere) {
            $statement = "UPDATE $table SET $set WHERE $whereString";
        } else {
            $statement = "UPDATE $table SET $set";
        }

        $sql = $this->conn->prepare($statement);

        foreach ($values as $key => $value) {
            $sql->bindValue(":$key", $value);
        }

        if ($hasWhere) {
            foreach ($where as $key => $value) {
                $sql->bindValue(":$key", $value);
            }
        }

        return $sql->execute();
    }

    public function addToValueInTable(
        $table,
        $row,
        $value,
        $where = null,
        $operator = '+'
    ) {
        if (!is_null($where)) {
            $statement = "UPDATE $table SET $row = $row $operator $value WHERE $where";
        } else {
            $statement = "UPDATE $table SET $row = $row $operator $value";
        }

        $sql = $this->conn->prepare($statement);

        return $sql->execute();
    }

}
