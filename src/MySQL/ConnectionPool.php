<?php

declare(strict_types=1);

namespace WalkWeb\NW\MySQL;

use WalkWeb\NW\AppException;
use WalkWeb\NW\Container;

/**
 * ConnectionPool создан для того, чтобы можно было работать сразу с несколькими разными базами MySQL
 *
 * @package WalkWeb\NW
 */
class ConnectionPool
{
    public const string DEFAULT = 'default';

    /**
     * @var Connection[]
     */
    private array $connections = [];

    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get database connection
     *
     * If no exist - create, else - return exist.
     *
     * If name not set - uses "DATABASE_URL", else use "DATABASE_NAME_CONNECTION", example:
     *
     * .env:
     * DATABASE_SECONDARY="${MYSQL_USER}:${MYSQL_PASSWORD}:${MYSQL_HOST}:${MYSQL_DATABASE}:${MYSQL_PORT}"
     *
     * php:
     * $connectionPool->getConnection('secondary')
     *
     *
     * @param string $name
     * @return Connection
     * @throws AppException
     */
    public function getConnection(string $name = self::DEFAULT): Connection
    {
        if (array_key_exists($name, $this->connections)) {
            return $this->connections[$name];
        }

        $config = $this->getConfig($this->convertConnectionName($name));
        $this->connections[$name] = $this->create($config);

        return $this->connections[$name];
    }

    /**
     * @param string $name
     * @return string
     */
    public function convertConnectionName(string $name): string
    {
        if ($name === self::DEFAULT) {
            return 'DATABASE_URL';
        }

        return 'DATABASE_' . mb_strtoupper($name);
    }

    /**
     * @param string $name
     * @return array
     * @throws AppException
     */
    public function getConfig(string $name): array
    {
        $params = explode(':', $url = $this->container->getEnv($name));

        if (count($params) !== 5) {
            throw new AppException('Invalid database configuration: ' . $url);
        }

        return [
            'user'     => $params[0],
            'password' => $params[1],
            'host'     => $params[2],
            'database' => $params[3],
            'port'     => (int)$params[4],
        ];
    }

    /**
     * Возвращает суммарное количество запросов по всем подключениям.
     *
     * @return int
     */
    public function getCountQuery(): int
    {
        $count = 0;

        foreach ($this->connections as $connection) {
            $count += $connection->getCountQuery();
        }

        return $count;
    }

    /**
     * Возвращает все запросы по всем подключениям.
     *
     * @return array
     */
    public function getQueries(): array
    {
        return array_map(function ($connection) {
            return $connection->getQueries();
        }, $this->connections);
    }

    /**
     * Create new connection
     *
     * @param array $params
     * @return Connection
     * @throws AppException
     */
    private function create(array $params): Connection
    {
        return new Connection(
            $params['host'],
            $params['user'],
            $params['password'],
            $params['database'],
            $params['port'],
            $this->container,
        );
    }
}
