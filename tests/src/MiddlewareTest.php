<?php

declare(strict_types=1);

namespace Tests\src;

use Tests\AbstractTestCase;
use Tests\utils\ExampleMiddleware;
use WalkWeb\NW\AppException;
use WalkWeb\NW\Response;

class MiddlewareTest extends AbstractTestCase
{
    /**
     * @throws AppException
     */
    public function testMiddlewareRedirect(): void
    {
        $middleware = new ExampleMiddleware($this->getContainer());
        $uri = '/banned';
        $response = new Response('', Response::FOUND);
        $response->withHeader('Location', $uri);

        self::assertEquals($response, $middleware->redirect($uri));
    }
}
