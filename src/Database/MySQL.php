<?php

namespace Scraper\Database;
use Scraper\Util;

/**
 * Class Database
 * @package Database
 * @author Joost Mul <scraper@jmul.net>
 */
final class MySQL implements Database
{
    const NAME = 'MySQL';

    /**
     * THe underlying MySQL database connection
     *
     * @var \mysqli
     */
    private $connection;

    /**
     * Whether or not the connection should have been initiated
     * @var bool
     */
    private $connectionLoaded = false;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $database;

    /**
     * @var string
     */
    private $host;

    /**
     * MySQL constructor.
     * @param array $settings
     */
    public function __construct($settings)
    {
        $this->username = Util::arrayGet($settings, 'username', '');
        $this->password = Util::arrayGet($settings, 'password', '');
        $this->database = Util::arrayGet($settings, 'database', '');
        $this->host     = Util::arrayGet($settings, 'host', '');
    }

    /**
     * Executes the given query and returns the mysql_result of it. Bind the given params to the query's
     * prepared statement
     *
     * @param  string $query
     * @param  array  $params
     * @param  string $types
     * @return \mysqli_stmt
     * @throws \Exception
     */
    public function query($query, $params = [], $types = '')
    {
        if (count($params) !== strlen($types)) {
            throw new \Exception("MySQL Error: Given parameter amount does not match the types");
        }

        $this->ensureConnection();

        $stmt = $this->connection->prepare($query);
        if (!empty($params)) {
            $args = [];
            array_unshift($params, $types);
            $count = count($params);

            for ($i = 0; $i < $count; $i++) {
                $args[$i] = & $params[$i];
            }

            if (!$stmt) {
                throw new \Exception($this->connection->error);
            }
            call_user_func_array([$stmt, 'bind_param'], $args);
        }

        if (!$stmt) {
            throw new \Exception($this->connection->error);
        }

        $stmt->execute();
        if (!$stmt) {
            throw new \Exception($stmt->error, $stmt->errno);
        }

        return $stmt;
    }

    /**
     * @param string $queries
     */
    public function multiQuery($queries)
    {
        $this->connection->multi_query($queries);
    }

    /**
     * Returns the first row of the given query's result set.
     *
     * @param string $query
     * @param array  $params
     * @param string $types
     * @return mixed
     */
    public function fetchOne($query, $params = [], $types = '')
    {
        $result = $this->query($query, $params, $types);
        $result = $result->get_result();
        $return = null;

        while ($row = $result->fetch_assoc()) {
            $return = $row;
            break;
        }

        return $return;
    }

    /**
     * Change the database connection to make use of another database with the given name
     *
     * @param string $databaseName
     */
    public function changeDb($databaseName)
    {
        $this->ensureConnection();
        $this->connection->select_db($databaseName);
    }

    /**
     * Executes the query and returns its result set as an array with associative arrays
     *
     * @param string $query
     * @param array  $params
     * @param string $types
     * @return mixed
     */
    public function fetchAll($query, $params = [], $types = '')
    {
        $result = $this->query($query, $params, $types);
        $result = $result->get_result();
        $return = [];
        while ($row = $result->fetch_assoc()) {
            $return[] = $row;
        }

        return $return;
    }

    /**
     * Makes sure the connection is made with the database.
     *
     * @throws \Exception
     */
    protected function ensureConnection()
    {
        if (!$this->connectionLoaded) {
            $this->connection = mysqli_connect(
                $this->host,
                $this->username,
                $this->password,
                $this->database
            );


            if (!$this->connection) {
                throw new \Exception("MySql connect error: " . mysqli_connect_error());
            }

            $this->connectionLoaded = true;
        }
    }

    /**
     * @return string
     */
    public static function getName()
    {
        return self::NAME;
    }
}
