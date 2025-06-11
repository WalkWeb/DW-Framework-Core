<?php

declare(strict_types=1);

namespace Tests;

use Dotenv\Dotenv;
use WalkWeb\NW\App;
use WalkWeb\NW\AppException;
use WalkWeb\NW\Container;
use WalkWeb\NW\Route\Router;
use WalkWeb\NW\Runtime;
use WalkWeb\NW\Traits\StringTrait;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    use StringTrait;

    protected App $app;
    protected string $dir;

    /**
     * @throws AppException
     */
    public function setUp(): void
    {
        $this->dir = __DIR__;
        $router = require $this->dir . '/../routes/web.php';
        $this->app = new App($router, $this->getContainer());
    }

    public function tearDown(): void
    {
        parent::tearDown();

        restore_exception_handler();
    }

    /**
     * @param Router $router
     * @return App
     * @throws AppException
     */
    protected function getApp(Router $router): App
    {
        return new App($router, $this->getContainer());
    }

    /**
     * @param string $appEnv
     * @return Container
     * @throws AppException
     */
    protected function getContainer(string $appEnv = 'test', string $viewDir = 'views/'): Container
    {
        $path = $this->dir . '/../';
        $dotenv = Dotenv::createImmutable($path, '.env.test');
        $dotenv->load();

        $container = new Container(
            $appEnv,
            $path,
            $_ENV['KEY'],
            self::validateDbConfig($_ENV['DATABASE_URL']),
            self::validateSmtpConfig($_ENV['SMTP_URL']),
            (bool)$_ENV['SAVE_LOG'],
            $path,
            $path . 'cache',
            $path . $viewDir,
            $path . 'migrations/',
            $_ENV['TEMPLATE'],
            $path . 'translations/',
            $_ENV['LANGUAGE']
        );

        $container->set(Runtime::class, new Runtime());
        return $container;
    }

    /**
     * @param string $url
     * @return array[]
     * @throws AppException
     */
    private static function validateDbConfig(string $url): array
    {
        $params = explode(':', $url);

        if (count($params) !== 4) {
            throw new AppException('Invalid database configuration: ' . $url);
        }

        return [
            'default' => [
                'user'     => $params[0],
                'password' => $params[1],
                'host'     => $params[2],
                'database' => $params[3],
            ],
        ];
    }

    /**
     * @param string $url
     * @return array[]
     * @throws AppException
     */
    private static function validateSmtpConfig(string $url): array
    {
        $params = explode(':', $url);

        if (count($params) !== 6) {
            throw new AppException('Invalid smtp configuration: ' . $url);
        }

        return [
            'smtp_host'     => $params[0],
            'smtp_port'     => (int)$params[1],
            'smtp_auth'     => (bool)$params[2],
            'smtp_user'     => $params[3],
            'smtp_password' => $params[4],
            'from'          => $params[5],
        ];
    }
}
