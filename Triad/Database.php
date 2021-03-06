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
    protected $options;

    public function __construct($dns, $user, $password, $persistent = false, $options = array()) {
        $this->dns = $dns;
        $this->user = $user;
        $this->password = $password;
        $this->persistent = $persistent;
        $this->options = $options;
    }

    public function connect() {
        if (is_null($this->db)) {
            $options = $this->options;
            $options[PDO::ATTR_PERSISTENT] = $this->persistent;

            $this->db = new PDO($this->dns, $this->user, $this->password, $options);
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

    /**
     * @deprecated use fetchColumn
     */
    public function fetchSingle($sql, $params = null) {
        return $this->fetchColumn($sql, $params);
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
            $updateQuery .= ($first ? "" : ",") . " ".$this->prefixIdent($key, $table)." = :".$this->escapeIdent($key)."";
            $first = false;
        }

        $updateQuery .= $this->getWhereCondition($table, $id, $saveData);
        $this->exec($updateQuery, $saveData);
    }

    public function delete($table, $id) {
        $params = [];
        $updateQuery = "DELETE FROM " . $this->escapeIdent($table) . $this->getWhereCondition($table, $id, $params);
        $this->exec($updateQuery, $params);
    }

    public function insert($table, $saveData, $ignore = false) {
        $keys = join(",", $this->prefixIdent(array_keys($saveData), $table));
        $values = ":".join(", :", $this->escapeIdent(array_keys($saveData)));
        $insertAction = $ignore ? "INSERT IGNORE INTO" : "INSERT INTO";
        $insertQuery = $insertAction . " " . $this->escapeIdent($table) . "(" . $keys . ") VALUES(" . $values . ")";
        $this->exec($insertQuery, $saveData);
    }

    public function read($table, $id, $columns = []) {
        $columnsQuery = "*";
        $valueReturn = false;

        if (is_array($columns) && count($columns) > 0) {
            $columnsQuery = join(",", $this->prefixIdent($columns, $table));
        }

        if (is_string($columns)) {
            $columnsQuery = $this->prefixIdent($columns, $table);
            $valueReturn = true;
        }

        $params = [];
        $selectQuery = "SELECT " . $columnsQuery . " FROM " . $this->escapeIdent($table) . $this->getWhereCondition($table, $id, $params);

        if ($valueReturn)
            return $this->fetchColumn($selectQuery, $params);

        return $this->fetch($selectQuery, $params);
    }

    public function insertUpdate($table, $id, $columns) {
        if (is_string($id))
            $id = ["id" => $id];

        $exists = $this->read($table, $id, key($id));
        if ($exists) {
            $this->update($table, $id, $columns);
        }
        else {
            $this->insert($table, array_merge($id, $columns));
        }
    }

    protected function getWhereCondition($table, $id, &$params) {
        $whereQuery = " WHERE ";

        if (is_array($id)) {
            $join = [];
            foreach ($id as $k => $v) {
                $key = $this->escapeIdent($k);
                $join[] = $this->prefixIdent($key, $table) . " = :$key";
                $params[$key] = $v;
            }

            $whereQuery .= join(" AND ", $join);
        }
        else {
            $params["id"] = $id;
            $whereQuery .= $this->prefixIdent("id", $table) . " = :id";
        }

        return $whereQuery;
    }

    public function escapeIdent($column) {
        if (is_array($column))
            return array_map([$this, "escapeIdent"], $column);

        $column = preg_replace('/[^A-Za-z0-9_-]+/', '', $column);
        return $column;
    }

    public function prefixIdent($column, $table) {
        if (is_array($column)) {
            $list = [];
            foreach ($column as $c) {
                $list[] = $this->escapeIdent($table) . "." . $this->escapeIdent($c);
            }
            return $list;
        }

        return $this->escapeIdent($table) . "." . $this->escapeIdent($column);
    }

    public function getTablesStructure() {
        $database = $this->fetchColumn('SELECT DATABASE()');

        // TODO add BINARY column definition

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
            if (empty($column["column_name"]))
                continue;

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
            if (empty($index["column_name"]))
                continue;

            $tables[$index["table_name"]]["indexes"][$index["index_name"]]["unique"] = !$index["non_unique"];
            $tables[$index["table_name"]]["indexes"][$index["index_name"]]["columns"][] = $index["column_name"];
        }

        foreach ($schemas as $schema) {
            if (empty($schema["table_name"]))
                continue;

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

    public function fetchColumn($sql, $params = null) {
        $instance = $this->setUpLog("fetchColumn", $sql, $params);
        $result = parent::fetchColumn($sql, $params);
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
