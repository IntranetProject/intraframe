<?php
/**
 * Created by PhpStorm.
 * User: bennet
 * Date: 06.07.18
 * Time: 00:38
 */

namespace intraframe\Util;


class DataBase {

    protected static $_instance = null;
    private $connection = null;
    private $raw = false;
    private $table;
    private $queryType = null;
    private $queryHasWhereClause = false;
    private $queryWhereUseAnd = false;
    private $finalQuery;
    private $finalParams = [];
    private $queryUpdate;
    private $queryInsert;
    private $querySelect;
    private $queryWhere;
    private $queryOrderType;
    private $queryOrderColumn;
    private $queryUseOrder = false;
    private $queryUseLimit = false;
    private $queryLimitOffset;
    private $queryLimitCount;

    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    protected function __clone() {
    }

    protected function __construct() {
        $config = Config::getInstance();
        try {
            $pdo = new PDO("mysql:host=" . $config->getSQLHost() . ";dbname=" . $config->getSQLDatabase() . ";charset=utf8", $config->getSQLUsername(), $config->getSQLPassword());
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection = $pdo;
        } catch (PDOException $e) {
            echo "PDO Unable to connect. (" . $e->getMessage() . ")";
        }
    }

    /**
     * @return PDO the created SQL Connection
     */
    public function getConnection(): PDO {
        return $this->connection;
    }

    /**
     * @return string the created SQL Query
     */
    public function getQuery(): string {
        self::build();
        return $this->finalQuery;
    }

    /**
     * since this class is based on prepared statements it automatically creates all the
     * necessary parameters
     * @return array with all parameters
     */
    public function getParams(): array {
        return $this->finalParams;
    }

    /**
     * If you do not want to use the QueryBuilder (in this class included)
     * you are able to run raw queries
     */
    public function raw(string $query, array $params = []): db {
        $this->raw = true;
        $this->finalQuery = $query;
        $this->finalParams = $params;
        return $this;
    }

    /**
     * pick a table
     * @param $table The Table name
     * @return db chainable class
     */
    public function table(string $table): db {
        $this->raw = false;
        $this->table = $table;
        return $this;
    }

    /**
     * @param $columns colums with column => value syntax
     * @return db chainable class
     */
    public function update(array $columns): db {
        $this->queryType = "update";
        $this->queryUpdate = $columns;
        return $this;
    }

    /**
     * @param $columns colums with column => value syntax
     * @return db chainable class
     */
    public function insert(array $columns): db {
        $this->queryType = "insert";
        $this->queryInsert = $columns;
        return $this;
    }

    /**
     * tells the query builder that you are trying to delete an entry
     * @return db chainable class
     */
    public function delete(): db {
        $this->queryType = "delete";
        return $this;
    }

    /**
     * @param string columns the string type of rows you want to select e.g. "id,fullname"
     * @return db chainable class
     */
    public function select(string $columns): db {
        $this->queryType = "select";
        $this->querySelect = $columns;
        return $this;
    }

    /**
     * @param array $columns syntax e.g. id => 13
     * @param bool $useAnd use AND in the Where clause
     * @return db chainable class
     */
    public function where(array $columns, bool $useAnd = false): db {
        $this->queryWhere = $columns;
        $this->queryHasWhereClause = true;
        $this->queryWhereUseAnd = $useAnd;
        return $this;
    }

    /**
     * @param string $orderType ASC or DESC
     * @param string $orderColumn the column you want it to be ordered by
     * @return db chainable class
     */
    public function order(string $orderType, string $orderColumn): db {
        $this->queryOrderType = $orderType;
        $this->queryOrderColumn = $orderColumn;
        $this->queryUseOrder = true;
        return $this;
    }

    /**
     * @param int $itemCount the amount of items you want to be displayed
     * @param int $offset the offset you want to apply
     * @return db chainable class
     */
    public function limit(int $itemCount, int $offset): db {
        $this->queryUseLimit = true;
        $this->queryLimitCount = $itemCount;
        $this->queryLimitOffset = $offset;
        return $this;
    }

    /**
     * builds the query
     */
    private function build() {
        if ($this->queryType == "update") {
            $query = "UPDATE `{$this->table}` SET ";
            foreach ($this->queryUpdate as $key => $value) {
                $query .= "`{$key}` = :{$key}, ";
                $this->finalParams[$key] = $value;
            }
            $query = trim($query, ', ');
            if ($this->queryHasWhereClause) {
                $query .= " WHERE ";
                foreach ($this->queryWhere as $key => $value) {
                    $query .= "`{$key}` = :{$key} " . ($this->queryWhereUseAnd ? "AND" : "OR");
                    $this->finalParams[$key] = $value;
                }
                $query = trim($query, ($this->queryWhereUseAnd ? "AND" : "OR"));
            }
            $query .= ";";
            $this->finalQuery = $query;
        } else if ($this->queryType == "insert") {
            $query = "INSERT INTO `{$this->table}` (";
            foreach ($this->queryInsert as $key => $value) {
                $query .= "`{$key}`, ";
            }
            $query = trim($query, ', ') . ") VALUES (";
            foreach ($this->queryInsert as $key => $value) {
                $query .= ":{$key}, ";
                $this->finalParams[$key] = $value;
            }
            $query = trim($query, ", ") . ");";
            $this->finalQuery = $query;
        } elseif ($this->queryType == "select") {
            $query = "SELECT {$this->querySelect}" . ($this->queryHasWhereClause ? " WHERE " : "");
            if ($this->queryHasWhereClause) {
                foreach ($this->queryWhere as $key => $value) {
                    $query .= "`{$key}` = :{$key}" . ($this->queryWhereUseAnd ? " AND " : " OR ");
                    $this->finalParams[$key] = $value;
                }
                $query = trim($query, ($this->queryWhereUseAnd ? "AND " : "OR "));
            }
            if ($this->queryUseOrder) {
                $query .= " ORDER BY `{$this->queryOrderColumn}` {$this->queryOrderType}";
            }
            if ($this->queryUseLimit) {
                $query .= " LIMIT {$this->queryLimitOffset}, {$this->queryLimitCount}";
            }
            $query .= ";";
            $this->finalQuery = $query;
        } elseif ($this->queryType == "delete") {
            $query = "DELETE FROM `{$this->table}` ";
            if ($this->queryHasWhereClause) {
                $query .= "WHERE ";
                foreach ($this->queryWhere as $key => $value) {
                    $query .= "`{$key}` = :{$key}" . ($this->queryWhereUseAnd ? " AND " : " OR ");
                    $this->finalParams[$key] = $value;
                }
                $query = trim($query, $this->queryWhereUseAnd ? " AND " : " OR ");
            }
            $query .= ";";
            $this->finalQuery = $query;
        } else {
            throw new Exception("Unknown query type", 1);
        }
    }

    /**
     * @return int (lastInsertId) or array(Data) depending on query type
     */
    public function execute() {
        $statement = $this->connection->prepare($this->finalQuery);
        $statement->execute($this->finalParams);
        if (explode(' ', $query)[0] == 'SELECT') {
            $data = $statement->fetchAll();
            return $data;
        }
        if (explode(' ', $query)[0] == 'INSERT') {
            return $con->lastInsertId();
        }
    }

    /**
     * closes the PDO Connection
     */
    public function closeConnection() {
        $this->connection = null;
    }
}