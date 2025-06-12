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
     * @param string $viewDir
     * @return Container
     * @throws AppException
     */
    protected function getContainer(
        string $appEnv = '',
        string $viewDir = Container::VIEW_DIR
    ): Container
    {
        $container = new Container($this->dir . '/../', '.env.test', $viewDir, $appEnv);
        $container->set(Runtime::class, new Runtime());
        return $container;
    }
}
