<?php
namespace src\connections;

class Database
{
    private $dbh;
    private $sqlStringArray = [];
    private $sqlSthArray= [];

    private function __construct()
    {
        global $cfg;

        $dsn = "mysql:host={$cfg['db']["db_host"]};dbname={$cfg['db']["db_name"]};charset=utf8mb4;port={$cfg['db']["port"]}";

        $this->dbh = new \PDO($dsn, $cfg['db']["db_user"], $cfg['db']["db_password"], array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+00:00'"));
    }

    public static function getConnection()
    {
        static $database_object = null;

        if (is_null($database_object)) $database_object = new self();

        return $database_object;
    }

    private function prepareUniqueStatement(string $sql): \PDOStatement
    {
        if (($sth_array_key = array_search($sql, $this->sqlStringArray)) !== false) {
           // entsprechendes Statement wurde schonmal prepared
            return $this->sqlSthArray[$sth_array_key];
        } else {
            $sth = $this->dbh->prepare($sql);

            $this->sqlStringArray[] = $sql;
            $this->sqlSthArray[] = $sth;

            return $sth;
        }
    }

    public function create(string $tablename, array $data): bool
    {
        $columnArray = array();
        $placeholderArray = array();
        $paramArray = array();

        foreach ($data as $column_name => $value) {
            $columnArray[] = "$column_name";

            $current_placeholder = ":" . $column_name;
            $placeholderArray[] = $current_placeholder;

            $paramArray[$current_placeholder] = $value;
        }

        $columnString = implode(", ", $columnArray);
        $placeholderString = implode(", ", $placeholderArray);

        $sql = "INSERT INTO $tablename ($columnString) VALUES ($placeholderString)";
        $sth = $this->prepareUniqueStatement($sql);

        return $sth->execute($paramArray);
    }

    public function read(string $sql, array $data = null, int $fetch_style = \PDO::FETCH_OBJ, $fetch_argument = null, array $ctor_args = null): array
    {
        $sth = $this->prepareUniqueStatement($sql);
        $sth->execute($data);

        /*while($row = $sth->fetch(\PDO::FETCH_ASSOC)) {
            var_dump($row);
        }
        die();*/

        if (!is_null($fetch_argument) && !is_null($ctor_args)) {
            return $sth->fetchAll($fetch_style, $fetch_argument, $ctor_args);
        } elseif (!is_null($fetch_argument) && is_null($ctor_args)) {
            return $sth->fetchAll($fetch_style, $fetch_argument);
        } else {
            return $sth->fetchAll($fetch_style);
        }
    }

    public function update(string $table, array $data, string $where, array $where_params = null): int
    {
        $set_array = array();
        $param_array = array();

        foreach ($data as $column_name => $value) {
            $current_placeholder = ":$column_name";
            $set_array[] = "$column_name=$current_placeholder";

            $param_array[$current_placeholder] = $value;
        }

        $set_string = implode(", ", $set_array);
        $complete_param_array = array_merge($param_array, $where_params);

        $sql = "UPDATE $table SET $set_string WHERE $where";

        $sth = $this->prepareUniqueStatement($sql);
        $sth->execute($complete_param_array);

        return $sth->rowCount();
    }

    public function delete(string $table, string $where = null, array $where_params = null): int
    {
        if (!is_null($where)) {
            $sql = "DELETE FROM $table WHERE $where";
        } else {
            $sql = "# noinspection SqlWithoutWhere DELETE FROM $table";
        }

        $sth = $this->prepareUniqueStatement($sql);
        $sth->execute($where_params);

        return $sth->rowCount();
    }

    public function checkExists($value, string $table, string $column): bool
    {
        $sql = "SELECT COUNT($column) as 'counter' FROM $table WHERE $column = :placeholder";
        $sth = $this->prepareUniqueStatement($sql);
        $sth->execute(array(":placeholder" => $value));
        $result = $sth->fetch(\PDO::FETCH_ASSOC);

        if ($result["counter"] === "0") {
            return false;
        } else {
            return true;
        }
    }

    public function reactToTrigger(object $sender, string $trigger)
    {
        if ($trigger === "xsrf_token") {
            $this->delete("token", "type='xsrf_token' AND expires < NOW()");

            $data = [
              "token_string" => $sender->getXSRFToken(),
              "type" => $trigger,
              "expires" => gmdate("Y-m-d H:i:s", time() + 900)
            ];

            if (!$this->create("token", $data)) {
              $sender->errorOnXSRFToken();
            }
        }
    }
}