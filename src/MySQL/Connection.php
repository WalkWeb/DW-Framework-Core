<?php

namespace WalkWeb\NW\MySQL;

use mysqli;
use WalkWeb\NW\AppException;
use WalkWeb\NW\Container;
use WalkWeb\NW\Logger;
use Throwable;

class Connection
{
    public const string ERROR_CONNECT = 'Unable connect to MySQL: ';
    public const string ERROR_PREPARE = 'Execution error SQL: ';

    private mysqli $connection;

    private string $error = '';
    private int $queryNumber = 0;
    private array $queries = [];
    private Logger $logger;

    /**
     * @throws AppException
     */
    public function __construct(
        string $host,
        string $user,
        string $password,
        string $database,
        int $port,
        Container $container
    ) {
        $this->logger = $container->getLogger();
        $this->createConnection($host, $user, $password, $database, $port);
    }

    /**
     * Close connection
     */
    public function __destruct()
    {
        $this->connection->close();
    }

    public function isSuccess(): bool
    {
        return $this->getError() === '';
    }

    /**
     * @throws AppException
     */
    public function setError(string $error): void
    {
        $this->logger->addLog($error);
        $this->error = $error;
    }

    public function getError(): string
    {
        if ($this->error) {
            return $this->error;
        }

        return $this->connection->error;
    }

    /**
     * Prepare and run sql query
     *
     * @throws AppException
     */
    public function query(string $sql, array $params = [], bool $single = false): array
    {
        $this->error = '';

        if ($single) {
            $sql .= ' LIMIT 1';
        }

        $this->saveQuery($sql);

        $param_arr = null;

        if (count($params) > 0) {
            $param_types = '';
            $param_arr = [''];
            foreach ($params as $key => $val) {
                $param_types .= $val['type'];
                $param_arr[] = &$params[$key]['value']; // values are passed by reference.
            }
            $param_arr[0] = $param_types;
        }

        try {
            $stmt = $this->connection->prepare($sql);
            if ($stmt === false) {
                $this->setError(self::ERROR_PREPARE . $this->connection->errno . ' ' . $this->connection->error . '. SQL: ' . $sql);
            } else {
                // if parameters have not arrived, then bind_param is not required
                if (count($params) > 0) {
                    call_user_func_array([$stmt, 'bind_param'], $param_arr);
                }
                if ($stmt->execute()) {
                    $res = $stmt->get_result();
                    if ($res !== false) {
                        $result = [];
                        $i = 0;
                        while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
                            $result[] = $row;
                            $i++;
                        }
                        if ($single && ($i === 1)) {
                            $result = $result[0];
                        }
                    }
                } else {
                    $this->setError($stmt->errno . ' ' . $stmt->error . '. SQL: ' . $sql);
                }
            }
        } catch (Throwable $e) {
            throw new AppException($e->getMessage());
        }

        if (!$this->isSuccess()) {
            throw new AppException($this->getError());
        }

        $this->queryNumber++;

        return $result ?? [];
    }

    /**
     * Return ID insert row
     */
    public function getInsertId(): int|string
    {
        return mysqli_insert_id($this->connection);
    }

    /**
     * Set autocommit mode
     *
     * @param $mode boolean true - autocommit, false - no autocommit
     * @return bool
     */
    public function autocommit(bool $mode): bool
    {
        return $this->connection->autocommit($mode);
    }

    /**
     * Close commit and apply changes
     *
     * @return bool
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * Close commit and skip changes
     *
     * @return bool
     */
    public function rollback(): bool
    {
        return $this->connection->rollback();
    }

    /**
     * Return executes query number
     *
     * @return int
     */
    public function getCountQuery(): int
    {
        return $this->queryNumber;
    }

    /**
     * Return executes queries
     *
     * @return string[]
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    /**
     * @throws AppException
     */
    private function createConnection(string $host, string $user, string $password, string $database, int $port): void
    {
        try {
            $this->connection = mysqli_connect($host, $user, $password, $database, $port);
        } catch (Throwable $e) {
            $error = self::ERROR_CONNECT . $e->getMessage();
            $this->logger->addLog($error);
            throw new AppException($error);
        }

        $this->connection->query('SET NAMES utf8');
        $this->connection->set_charset('utf8');
    }

    /**
     * @param string $query
     */
    private function saveQuery(string $query): void
    {
        $this->queries[] = $query;
    }
}
