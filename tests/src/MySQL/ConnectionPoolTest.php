<?php

declare(strict_types=1);

namespace Tests\src\MySQL;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\AbstractTestCase;
use WalkWeb\NW\AppException;
use WalkWeb\NW\Container;
use WalkWeb\NW\MySQL\ConnectionPool;

class ConnectionPoolTest extends AbstractTestCase
{
    /**
     * @throws AppException
     */
    #[DataProvider('convertConnectionNameDataProvider')]
    public function testConnectionPoolConvertConnectionName(string $name, string $expectedName): void
    {
        self::assertEquals($expectedName, $this->getConnectionPool()->convertConnectionName($name));
    }

    /**
     * @throws AppException
     */
    #[DataProvider('getConfigDataProvider')]
    public function testConnectionPoolGetConfigSuccess(string $name, array $expectedConfig): void
    {
        self::assertEquals($expectedConfig, $this->getConnectionPool()->getConfig($name));
    }

    /**
     * @throws AppException
     */
    public function testConnectionPoolGetConfigFail()
    {
        $url = 'YOUR_DB_USER_NAME#YOUR_DB_PASSWORD#DB_HOST#DATABASE_NAME#3306';
        $_ENV['DATABASE_INVALID'] = $url;

        $container = new Container(__DIR__ . '/../../../', '.env.test');
        $pool = new ConnectionPool($container);

        $this->expectException(AppException::class);
        $this->expectExceptionMessage("Invalid database configuration: $url");
        $pool->getConfig('DATABASE_INVALID');
    }

    /**
     * @throws AppException
     */
    public function testConnectionPoolGetCountQuery(): void
    {
        self::assertEquals(0, $this->getConnectionPool()->getCountQuery());
    }

    /**
     * @throws AppException
     */
    public function testConnectionPoolGetQueries(): void
    {
        self::assertEquals([], $this->getConnectionPool()->getQueries());
    }

    /**
     * @return array
     */
    public static function convertConnectionNameDataProvider(): array
    {
        return [
            [
                ConnectionPool::DEFAULT,
                'DATABASE_URL',
            ],
            [
                'custom',
                'DATABASE_CUSTOM',
            ],
            [
                'Other',
                'DATABASE_OTHER',
            ],
        ];
    }

    /**
     * @return array
     */
    public static function getConfigDataProvider(): array
    {
        return [
            [
                'DATABASE_URL',
                [
                    'user'     => 'YOUR_DB_USER_NAME',
                    'password' => 'YOUR_DB_PASSWORD',
                    'host'     => 'DB_HOST',
                    'database' => 'DATABASE_NAME',
                    'port'     => 3306,
                ],
            ],
        ];
    }

    /**
     * @return ConnectionPool
     * @throws AppException
     */
    private function getConnectionPool(): ConnectionPool
    {
        return new ConnectionPool(self::getContainer());
    }
}
