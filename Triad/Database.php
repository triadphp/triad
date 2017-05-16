<?php
/**
 * Triad - Lightweight MVP / HMVP Framework
 * @link http://
 * @author Marek Vavrecan, vavrecan@gmail.com
 * @copyright 2013 Marek Vavrecan
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3
 * @version 1.0.0
 */

namespace Triad;

use \Triad\Exceptions\DatabaseException;
use \PDO;

class Database
{
    /**
     * @var PDO
     */
    protected $db = null;

    protected $dns;
    protected $user;
    protected $password;
    protected $persistent;

    public function __construct($dns, $user, $password, $persistent = false) {
        $this->dns = $dns;
        $this->user = $user;
        $this->password = $password;
        $this->persistent = $persistent;
    }

    public function connect() {
        if (is_null($this->db)) {
            $this->db = new PDO($this->dns, $this->user, $this->password, array(PDO::ATTR_PERSISTENT => $this->persistent));
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            //  paranoid, huh, we will not need these anymore
            unset($this->user);
            unset($this->password);
        }
    }

    public function isConnected() {
        return !is_null($this->db);
    }

    public function exec($sql, $params = null) {
        $statement = $this->db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

        if (!($return = $statement->execute($params))) {
            throw new DatabaseException($statement->errorInfo());
        }

        return $return;
    }

    public function lastInsertId($name = null) {
        return $this->db->lastInsertId($name);
    }

    public function fetch($sql, $params = array()) {
        $statement = $this->db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

        if (!$statement->execute($params)) {
            throw new DatabaseException($statement->errorInfo());
        }

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchObject($class, $sql, $params = array()) {
        $statement = $this->db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

        if (!$statement->execute($params)) {
            throw new DatabaseException($statement->errorInfo());
        }

        return $statement->fetchObject($class);
    }

    public function fetchColumn($sql, $params = null) {
        $statement = $this->db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

        if (!$statement->execute($params)) {
            throw new DatabaseException($statement->errorInfo());
        }

        return $statement->fetchColumn();
    }

    public function fetchAll($sql, $params = array()) {
        $statement = $this->db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

        if (!$statement->execute($params)) {
            throw new DatabaseException($statement->errorInfo());
        }

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchAllColumn($sql, $params = null) {
        $statement = $this->db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

        if (!$statement->execute($params)) {
            throw new DatabaseException($statement->errorInfo());
        }

        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    public function fetchAllObject($class, $sql, $params = null) {
        $statement = $this->db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

        if (!$statement->execute($params)) {
            throw new DatabaseException($statement->errorInfo());
        }

        return $statement->fetchAll(PDO::FETCH_CLASS, $class);
    }

    public function callbackFetchAll($callback, $sql, $params = null) {
        $statement = $this->db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

        if (!$statement->execute($params)) {
            throw new DatabaseException($statement->errorInfo());
        }

        // This will give better memory usage on big data result
        while ($data = $statement->fetch(PDO::FETCH_ASSOC)) {
            $callback($data);
        }
    }

    public function beginTransaction() {
        $this->db->beginTransaction();
    }

    public function rollBack() {
        $this->db->rollBack();
    }

    public function commit() {
        $this->db->commit();
    }

    public function inTransaction() {
        return $this->db->inTransaction();
    }

    public function update($table, $id, $saveData) {
        $updateQuery = "UPDATE ".$this->escapeIdent($table)." SET ";

        $first = true;
        foreach ($saveData as $key => $value) {
            $updateQuery .= ($first ? "" : ",") . " ".$this->escapeIdent($key)." = :".$this->escapeIdent($key)."";
            $first = false;
        }

        if (is_array($id)) {
            $key = $this->escapeIdent(key($id));
            $value = current($id);

            $saveData[$key] = $value;
            $updateQuery .= " WHERE $key = :$key";
        }
        else {
            $saveData["id"] = $id;
            $updateQuery .= " WHERE id = :id";
        }

        $this->exec($updateQuery, $saveData);
    }

    public function delete($table, $id) {
        $updateQuery = "DELETE FROM " . $this->escapeIdent($table);
        $params = [];

        if (is_array($id)) {
            $key = $this->escapeIdent(key($id));
            $value = current($id);

            $params[$key] = $value;
            $updateQuery .= " WHERE $key = :$key";
        }
        else {
            $params["id"] = $id;
            $updateQuery .= " WHERE id = :id";
        }

        $this->exec($updateQuery, $params);
    }

    public function insert($table, $saveData) {
        $keys = join(",", $this->escapeIdent(array_keys($saveData)));
        $values = ":".join(", :", $this->escapeIdent(array_keys($saveData)));
        $insertQuery = "INSERT INTO " . $this->escapeIdent($table) . "(" . $keys . ") VALUES(" . $values . ")";
        $this->exec($insertQuery, $saveData);
    }

    public function escapeIdent($column) {
        if (is_array($column))
            return array_map([$this, "escapeIdent"], $column);

        $column = preg_replace('/[^A-Za-z0-9_-]+/', '', $column);
        return $column;
    }

    public function getTablesStructure() {
        $database = $this->fetchColumn('SELECT DATABASE()');

        $columns = $this->fetchAll("SELECT
            table_name, column_name, is_nullable, character_set_name, collation_name, column_type, column_key, extra
        FROM information_schema.columns
            WHERE table_schema = :database ORDER BY table_name, ordinal_position", ["database" => $database]);

        $indexes = $this->fetchAll("SELECT
            table_name, index_name, column_name, non_unique
        FROM information_schema.statistics
            WHERE table_schema = :database ORDER BY table_name", ["database" => $database]);

        $schemas = $this->fetchAll("SELECT
            table_name, engine, table_collation
        FROM information_schema.tables
            WHERE table_schema = :database ORDER BY table_name", ["database" => $database]);

        $tables = [];
        foreach ($columns as $column) {
            $data = [
                "name" => $column["column_name"],
                "nullable" => $column["is_nullable"] == "YES",
                "character_set" => $column["character_set_name"],
                "collation" => $column["collation_name"],
                "type" => $column["column_type"],
                "extra" => $column["extra"],
            ];
            $tables[$column["table_name"]]["columns"][] = $data;
        }

        foreach ($indexes as $index) {
            $tables[$index["table_name"]]["indexes"][$index["index_name"]]["unique"] = !$index["non_unique"];
            $tables[$index["table_name"]]["indexes"][$index["index_name"]]["columns"][] = $index["column_name"];
        }

        foreach ($schemas as $schema) {
            $tables[$schema["table_name"]]["engine"] = $schema["engine"];
            $tables[$schema["table_name"]]["table_collation"] = $schema["table_collation"];
        }

        return $tables;
    }
}

class DatabaseDebug extends Database
{
    private $log = array();

    private function setUpLog($method, $sql, $params) {
        $instance = array();
        $instance["method"] = $method;
        $instance["sql"] = $sql;
        $instance["params"] = $params;
        $instance["start_time"] = microtime(true);
        return $instance;
    }

    private function tearDownLog($instance) {
        $instance["end_time"] = microtime(true);
        $instance["duration"] = $instance["end_time"] - $instance["start_time"];
        array_push($this->log, $instance);
    }

    public function getLog() {
        return $this->log;
    }

    public function connect()
    {
        parent::connect();
    }

    public function exec($sql, $params = null) {
        $instance = $this->setUpLog("exec", $sql, $params);
        $result = parent::exec($sql, $params);
        $this->tearDownLog($instance);

        return $result;
    }

    public function fetch($sql, $params = array()) {
        $instance = $this->setUpLog("fetch", $sql, $params);
        $result = parent::fetch($sql, $params);
        $this->tearDownLog($instance);

        return $result;
    }

    public function fetchSingle($sql, $params = null, $column = null) {
        $instance = $this->setUpLog("fetchSingle", $sql, $params);
        $result = parent::fetchSingle($sql, $params, $column);
        $this->tearDownLog($instance);

        return $result;
    }

    public function fetchAll($sql, $params = array()) {
        $instance = $this->setUpLog("fetchAll", $sql, $params);
        $result = parent::fetchAll($sql, $params);
        $this->tearDownLog($instance);

        return $result;
    }

    public function fetchList($sql, $params = null, $column = null) {
        $instance = $this->setUpLog("fetchList", $sql, $params);
        $result = parent::fetchList($sql, $params, $column);
        $this->tearDownLog($instance);

        return $result;
    }
}
