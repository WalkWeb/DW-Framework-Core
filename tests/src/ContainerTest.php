<?php

declare(strict_types=1);

namespace Tests\src;

use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;
use WalkWeb\NW\AppException;
use WalkWeb\NW\Captcha;
use WalkWeb\NW\MySQL\ConnectionPool;
use WalkWeb\NW\Container;
use WalkWeb\NW\Cookie;
use WalkWeb\NW\Csrf;
use WalkWeb\NW\Logger;
use WalkWeb\NW\Mailer;
use WalkWeb\NW\Request;
use WalkWeb\NW\Runtime;
use WalkWeb\NW\Translation;
use WalkWeb\NW\Validator;
use Tests\AbstractTestCase;

class ContainerTest extends AbstractTestCase
{
    private const string PATH = __DIR__ . '/../../';

    /**
     * @throws AppException
     */
    #[DataProvider('createDataProvider')]
    public function testContainerCreate(array $smtpConfig): void
    {
        $container = $this->getContainer();

        $saveLog = false;

        self::assertEquals(
            new ConnectionPool($container),
            $container->getConnectionPool()
        );
        self::assertEquals(
            new Logger($saveLog, $container->getRootDir()),
            $container->getLogger()
        );
        self::assertEquals(
            new Mailer($container, $smtpConfig),
            $container->getMailer(),
        );
        self::assertEquals(new Csrf($container), $container->getCsrf());
        self::assertEquals(new Captcha($container), $container->getCaptcha());
        self::assertEquals(new Validator($container), $container->getValidator());
        self::assertEquals($container->getRootDir() . 'cache/', $container->getCacheDir());
        self::assertEquals($container->getRootDir() . 'views/', $container->getViewDir());
        self::assertEquals($container->getRootDir() . 'migrations/', $container->getMigrationDir());
        self::assertEquals($container->getRootDir() . 'translations/', $container->getTranslateDir());
        self::assertEquals('test', $container->getAppEnv());
    }

    /**
     * Тест на ручное добавление сервиса в контейнер
     *
     * @throws AppException
     */
    public function testContainerSetService(): void
    {
        $logger = new Logger(false, 'xxx');
        $logger->addLog('abc');

        $container = $this->getContainer();
        $container->set(Logger::class, $logger);

        self::assertEquals($logger, $container->getLogger());
    }

    /**
     * @throws AppException
     */
    public function testContainerGetConnectionPool(): void
    {
        $container = $this->getContainer();

        $connectionPool = $container->get(ConnectionPool::class);
        self::assertInstanceOf(ConnectionPool::class, $connectionPool);

        $connectionPool = $container->get('connection_pool');
        self::assertInstanceOf(ConnectionPool::class, $connectionPool);

        $connectionPool = $container->getConnectionPool();
        self::assertInstanceOf(ConnectionPool::class, $connectionPool);
    }

    /**
     * @throws AppException
     */
    public function testContainerGetLogger(): void
    {
        $container = $this->getContainer();

        $logger = $container->get(Logger::class);
        self::assertInstanceOf(Logger::class, $logger);

        $logger = $container->get('logger');
        self::assertInstanceOf(Logger::class, $logger);

        $logger = $container->getLogger();
        self::assertInstanceOf(Logger::class, $logger);
    }

    /**
     * @throws AppException
     */
    public function testContainerGetCsrf(): void
    {
        $container = $this->getContainer();

        $csrf = $container->get(Csrf::class);
        self::assertInstanceOf(Csrf::class, $csrf);

        $csrf = $container->get('csrf');
        self::assertInstanceOf(Csrf::class, $csrf);

        $csrf = $container->getCsrf();
        self::assertInstanceOf(Csrf::class, $csrf);
    }

    /**
     * @throws AppException
     */
    public function testContainerGetCaptcha(): void
    {
        $container = $this->getContainer();

        $captcha = $container->get(Captcha::class);
        self::assertInstanceOf(Captcha::class, $captcha);

        $captcha = $container->get('captcha');
        self::assertInstanceOf(Captcha::class, $captcha);

        $captcha = $container->getCaptcha();
        self::assertInstanceOf(Captcha::class, $captcha);
    }

    /**
     * @throws AppException
     */
    public function testContainerGetCookies(): void
    {
        $container = $this->getContainer();
        $cookie = new Cookie();

        $container->set(Cookie::class, $cookie);

        self::assertEquals($cookie, $container->getCookies());
    }

    /**
     * @throws AppException
     */
    public function testContainerGetRuntime(): void
    {
        $container = $this->getContainer();
        $cookie = new Runtime();

        $container->set(Runtime::class, $cookie);

        self::assertEquals($cookie, $container->getRuntime());
    }

    /**
     * @throws AppException
     */
    public function testContainerGetValidator(): void
    {
        $container = $this->getContainer();

        $validator = $container->get(Validator::class);
        self::assertInstanceOf(Validator::class, $validator);

        $validator = $container->get('validator');
        self::assertInstanceOf(Validator::class, $validator);

        $validator = $container->getValidator();
        self::assertInstanceOf(Validator::class, $validator);
    }

    /**
     * @throws AppException
     */
    public function testContainerGetMailer(): void
    {
        $container = $this->getContainer();

        $mailer = $container->get(Mailer::class);
        self::assertInstanceOf(Mailer::class, $mailer);

        $mailer = $container->get('mailer');
        self::assertInstanceOf(Mailer::class, $mailer);

        $mailer = $container->getMailer();
        self::assertInstanceOf(Mailer::class, $mailer);
    }

    /**
     * @throws AppException
     */
    public function testContainerGetTranslation(): void
    {
        $container = $this->getContainer();

        $translation = $container->get(Translation::class);
        self::assertInstanceOf(Translation::class, $translation);

        $translation = $container->get('translation');
        self::assertInstanceOf(Translation::class, $translation);

        $translation = $container->getTranslation();
        self::assertInstanceOf(Translation::class, $translation);

        self::assertEquals('ru', $translation->getLanguage());
    }

    /**
     * @throws AppException
     */
    public function testContainerGetRequestSuccess(): void
    {
        $container = $this->getContainer();
        $request = new Request(['example' => 123]);

        $container->set(Request::class, $request);

        self::assertEquals($request, $container->getRequest());
    }

    /**
     * @throws AppException
     */
    public function testContainerGetUserNotSet(): void
    {
        $container = $this->getContainer();

        $this->expectException(AppException::class);
        $this->expectExceptionMessage(sprintf(Container::GET_ERROR, 'user'));
        $container->getUser();
    }

    /**
     * @throws AppException
     */
    public function testContainerGetUserSuccess(): void
    {
        $container = $this->getContainer();
        $user = new stdClass();

        self::assertFalse($container->exist('user'));

        $container->set('user', $user);

        self::assertTrue($container->exist('user'));
        self::assertEquals($user, $container->getUser());
    }

    /**
     * @throws AppException
     */
    public function testContainerUnset(): void
    {
        $container = $this->getContainer();
        $user = new stdClass();

        self::assertFalse($container->exist('user'));

        $container->set('user', $user);

        self::assertTrue($container->exist('user'));

        $container->unset('user');

        self::assertFalse($container->exist('user'));
    }

    /**
     * Тест на ситуацию, когда запрашиваются сервисы Request/Cookie/Runtime до того, как они установлены через set()
     *
     * @throws AppException
     */
    #[DataProvider('getServiceErrorDataProvider')]
    public function testContainerGetServiceFail(string $class, string $error): void
    {
        $container = new Container(self::PATH);

        $this->expectException(AppException::class);
        $this->expectExceptionMessage($error);
        $container->get($class);
    }

    /**
     * Аналогично testContainerGetServiceFail, только запрос идет к конкретному методу на получение сервиса
     *
     * @throws AppException
     */
    #[DataProvider('getMethodServiceErrorDataProvider')]
    public function testContainerGetMethodServiceFail(string $method, string $error): void
    {
        $container = new Container(self::PATH);

        $this->expectException(AppException::class);
        $this->expectExceptionMessage($error);
        $container->$method();
    }

    /**
     * Тест на успешную установку APP_ENV
     *
     * @throws AppException
     */
    public function testContainerSetAppEnvSuccess(): void
    {
        self::assertEquals(Container::APP_TEST, $this->getContainer()->getAppEnv());
    }

    /**
     * Тест на попытку указать некорректный APP_ENV
     */
    public function testContainerSetAppEnvFail(): void
    {
        $this->expectException(AppException::class);
        $this->expectExceptionMessage('Invalid APP_ENV. Valid values: prod, dev, test');
        $this->getContainer('invalid_app_env');
    }

    /**
     * Тест на установку нового template
     */
    public function testContainerSetTemplate(): void
    {
        $container = $this->getContainer();

        self::assertEquals('default', $container->getTemplate());

        $template = 'new_template';
        $container->setTemplate($template);

        self::assertEquals($template, $container->getTemplate());
    }

    /**
     * Тест на ситуацию, когда запрашивается неизвестный сервис
     *
     * @throws AppException
     */
    public function testContainerUnknownService(): void
    {
        $this->expectException(AppException::class);
        $this->expectExceptionMessage('Unknown service: name_service');
        $this->getContainer()->get('name_service');
    }

    /**
     * @throws AppException
     */
    public function testContainerExistService(): void
    {
        $container = $this->getContainer();

        $container->get(Validator::class);

        self::assertTrue($container->exist(Validator::class));
        self::assertTrue($container->exist('validator'));
    }

    /**
     * @throws AppException
     */
    public function testContainerNoExistService(): void
    {
        self::assertFalse($this->getContainer()->exist('UnknownService'));
    }

    /**
     * @return array
     */
    public static function createDataProvider(): array
    {
        return [
            [
                [
                    'smtp_host'     => 'HOST',
                    'smtp_port'     => 465,
                    'smtp_auth'     => true,
                    'smtp_user'     => 'USER',
                    'smtp_password' => 'PASSWORD',
                    'from'          => 'mail@mail.com',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public static function getServiceErrorDataProvider(): array
    {
        return [
            [
                Request::class,
                sprintf(Container::GET_ERROR, 'Request'),
            ],
            [
                Cookie::class,
                sprintf(Container::GET_ERROR, 'Cookie'),
            ],
            [
                Runtime::class,
                sprintf(Container::GET_ERROR, 'Runtime'),
            ],
        ];
    }

    /**
     * @return array
     */
    public static function getMethodServiceErrorDataProvider(): array
    {
        return [
            [
                'getRequest',
                sprintf(Container::GET_ERROR, 'Request'),
            ],
            [
                'getCookies',
                sprintf(Container::GET_ERROR, 'Cookie'),
            ],
            [
                'getRuntime',
                sprintf(Container::GET_ERROR, 'Runtime'),
            ],
        ];
    }
}
