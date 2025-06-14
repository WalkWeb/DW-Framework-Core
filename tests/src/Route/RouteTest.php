<?php

declare(strict_types=1);

namespace Tests\src\Route;

use PHPUnit\Framework\Attributes\DataProvider;
use WalkWeb\NW\Request;
use WalkWeb\NW\Route\Route;
use Tests\AbstractTestCase;

class RouteTest extends AbstractTestCase
{
    /**
     * Тест на создание маршрута
     */
    public function testRouteCreate(): void
    {
        $name = 'testGetRoute';
        $path = 'home';
        $handler = 'TestContoller@index';
        $method = 'GET';
        $params = ['test' => 'test'];
        $namespace = 'namespace';

        $route = new Route($name, $path, $handler, $method, $params, $namespace);

        self::assertEquals($name, $route->getName());
        self::assertEquals($path, $route->getPath());
        self::assertEquals($namespace . '\\' . $handler, $route->getHandler());
        self::assertEquals($method, $route->getMethod());
        self::assertEquals($params, $route->getParams());
        self::assertEquals($namespace, $route->getNamespace());
        self::assertEquals([], $route->getMiddleware());

        $route->addMiddleware('CreatedByMiddleware');

        self::assertEquals([Route::DEFAULT_PRIORITY => 'CreatedByMiddleware'], $route->getMiddleware());
    }

    /**
     * Тесты на совпадение маршрута
     */
    #[DataProvider('matchDataProvider')]
    public function testRouteMatch(
        Request $request,
        string $name,
        string $path,
        string $handler,
        string $method,
        array $params,
        string $namespace,
        ?array $expectedResult
    ): void
    {
        $route = new Route($name, $path, $handler, $method, $params, $namespace);
        self::assertEquals($expectedResult, $route->match($request));
    }

    /**
     * @return array
     */
    public static function matchDataProvider(): array
    {
        $baseRequest = new Request([]);
        $postRequest = new Request(['REQUEST_URI' => '/post/10']);
        $postRequest->withAttribute('id', 10);
        $noMatchRequest = new Request(['REQUEST_URI' => '/post/abc']);

        // /u/Огромыч
        $cyrillicRequest = new Request(['REQUEST_URI' => '/u/%D0%9E%D0%B3%D1%80%D0%BE%D0%BC%D1%8B%D1%87']);

        return [
            // Совпадение маршрута и запроса
            [
                $baseRequest,
                'name',
                '/',
                'Handler',
                'GET',
                [],
                'namespace',
                [
                    'handler' => 'namespace\Handler',
                    'request' => $baseRequest,
                    'middleware' => [],
                ],
            ],
            // Не совпадение по методу
            [
                $baseRequest,
                'name',
                '/',
                'Handler',
                'POST',
                [],
                'namespace',
                null,
            ],
            // Совпадение маршрута и запроса + параметр
            [
                $postRequest,
                'name',
                '/post/{id}',
                'GetPostHandler',
                'GET',
                ['id' => '\d+'],
                'namespace',
                [
                    'handler' => 'namespace\GetPostHandler',
                    'request' => $postRequest,
                    'middleware' => [],
                ],
            ],
            // Не совпадение маршрута - ожидается int, а получен string в параметре
            [
                $noMatchRequest,
                'name',
                '/post/{id}',
                'GetPostHandler',
                'GET',
                ['id' => '\d+'],
                'namespace',
                null,
            ],
            // uri с кириллицей
            [
                $cyrillicRequest,
                'cyrillic',
                '/u/{name}',
                'GetPostHandler',
                'GET',
                ['name' => '[a-zA-Z0-9а-яА-ЯёЁ]+'],
                'namespace',
                [
                    'handler' => 'namespace\GetPostHandler',
                    'request' => $cyrillicRequest,
                    'middleware' => [],
                ],
            ],
        ];
    }
}
