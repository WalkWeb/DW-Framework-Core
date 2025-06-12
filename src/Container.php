<?php

declare(strict_types=1);

namespace WalkWeb\NW;

use Dotenv\Dotenv;
use WalkWeb\NW\MySQL\ConnectionPool;

class Container
{
    public const string APP_PROD  = 'prod';
    public const string APP_DEV   = 'dev';
    public const string APP_TEST  = 'test';

    public const string CACHE_DIR       = 'cache/';
    public const string VIEW_DIR        = 'views/';
    public const string MIGRATING_DIR   = 'migrations/';
    public const string TRANSLATION_DIR = 'translations/';

    public const string GET_ERROR = '%s cannot be created automatically, it must be added to the container via set() manually';

    private array $map = [
        ConnectionPool::class => ConnectionPool::class,
        'connection_pool'     => ConnectionPool::class,
        Logger::class         => Logger::class,
        'logger'              => Logger::class,
        Csrf::class           => Csrf::class,
        'csrf'                => Csrf::class,
        Captcha::class        => Captcha::class,
        'captcha'             => Captcha::class,
        Validator::class      => Validator::class,
        'validator'           => Validator::class,
        Mailer::class         => Mailer::class,
        'mailer'              => Mailer::class,
        Translation::class    => Translation::class,
        'translation'         => Translation::class,
        Request::class        => Request::class,
        Cookie::class         => Cookie::class,
        Runtime::class        => Runtime::class,

        // User this is any custom object
        'user'                => 'user',
    ];

    private array $storage = [];

    private string $appEnv;
    private string $rootDir;
    private string $secretKey;
    private array $dbConfigs;
    private array $mailerConfig;
    private bool $loggerSaveLog;
    private string $loggerDir;
    private string $cacheDir;
    private string $viewDir;
    private string $migrationDir;
    private string $template;
    private string $translateDir;
    private string $language;

    private array $env;

    /**
     * @param string $rootDir
     * @param string $file
     * @param string $viewDir
     * @param string $appEnv
     * @param string $template
     * @throws AppException
     */
    public function __construct(
        string $rootDir,
        string $file = '.env',
        string $viewDir = self::VIEW_DIR,
        string $appEnv = '',
        string $template = '',
    ) {
        $this->setEnv($rootDir, $file);
        $this->setAppEnv($appEnv ?: $this->getEnv('APP_ENV'));
        $this->rootDir = $rootDir;
        $this->secretKey = $this->getEnv('SECRET_KEY');
        $this->dbConfigs = self::validateDbConfig($this->getEnv('DATABASE_URL'));
        $this->mailerConfig = self::validateSmtpConfig($this->getEnv('SMTP_URL'));
        $this->loggerSaveLog = (bool)$this->getEnv('SAVE_LOG');
        $this->loggerDir = $rootDir;
        $this->cacheDir = $rootDir . self::CACHE_DIR;
        $this->viewDir = $rootDir . $viewDir;
        $this->migrationDir = $rootDir . self::MIGRATING_DIR;
        $this->template = $template ?: $this->getEnv('TEMPLATE');
        $this->translateDir = $rootDir . self::TRANSLATION_DIR;
        $this->language = $this->getEnv('LANGUAGE');
    }

    /**
     * @param string $id
     * @return object
     * @throws AppException
     */
    public function get(string $id): object
    {
        $class = $this->getNameService($id);

        if ($this->exist($class)) {
            return $this->storage[$class];
        }

        if ($class === Request::class || $class === Cookie::class || $class === Runtime::class || $class === 'user') {
            throw new AppException(
                sprintf(self::GET_ERROR, $class)
            );
        }

        return $this->createService($class);
    }

    /**
     * @param string $id
     * @param object $object
     * @throws AppException
     */
    public function set(string $id, object $object): void
    {
        $id = $this->getNameService($id);
        $this->storage[$id] = $object;
    }

    /**
     * @param string $id
     * @throws AppException
     */
    public function unset(string $id): void
    {
        $id = $this->getNameService($id);
        unset($this->storage[$id]);
    }

    /**
     * @param string $field
     * @return mixed
     * @throws AppException
     */
    public function getEnv(string $field): mixed
    {
        if (!array_key_exists($field, $this->env)) {
            throw new AppException("Parameter '$field' is not defined in environment");
        }

        return $this->env[$field];
    }

    /**
     * @return string
     */
    public function getRootDir(): string
    {
        return $this->rootDir;
    }

    /**
     * @return string
     */
    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    /**
     * @return ConnectionPool
     * @throws AppException
     */
    public function getConnectionPool(): ConnectionPool
    {
        /** @var ConnectionPool $service */
        $service = $this->get(ConnectionPool::class);
        return $service;
    }

    /**
     * @return Logger
     * @throws AppException
     */
    public function getLogger(): Logger
    {
        /** @var Logger $service */
        $service = $this->get(Logger::class);
        return $service;
    }

    /**
     * @return Csrf
     * @throws AppException
     */
    public function getCsrf(): Csrf
    {
        /** @var Csrf $service */
        $service = $this->get(Csrf::class);
        return $service;
    }

    /**
     * @return Captcha
     * @throws AppException
     */
    public function getCaptcha(): Captcha
    {
        /** @var Captcha $service */
        $service = $this->get(Captcha::class);
        return $service;
    }

    /**
     * @return Validator
     * @throws AppException
     */
    public function getValidator(): Validator
    {
        /** @var Validator $service */
        $service = $this->get(Validator::class);
        return $service;
    }

    /**
     * @return Request
     * @throws AppException
     */
    public function getRequest(): Request
    {
        /** @var Request $service */
        $service = $this->get(Request::class);
        return $service;
    }

    /**
     * @return Cookie
     * @throws AppException
     */
    public function getCookies(): Cookie
    {
        /** @var Cookie $service */
        $service = $this->get(Cookie::class);
        return $service;
    }

    /**
     * @return Runtime
     * @throws AppException
     */
    public function getRuntime(): Runtime
    {
        /** @var Runtime $service */
        $service = $this->get(Runtime::class);
        return $service;
    }

    /**
     * @return Mailer
     * @throws AppException
     */
    public function getMailer(): Mailer
    {
        /** @var Mailer $service */
        $service = $this->get(Mailer::class);
        return $service;
    }

    /**
     * @return Translation
     * @throws AppException
     */
    public function getTranslation(): Translation
    {
        /** @var Translation $service */
        $service = $this->get(Translation::class);
        return $service;
    }

    /**
     * @return object
     * @throws AppException
     */
    public function getUser(): object
    {
        return $this->get('user');
    }

    /**
     * @return string
     */
    public function getCacheDir(): string
    {
        return $this->cacheDir;
    }

    /**
     * @return string
     */
    public function getViewDir(): string
    {
        return $this->viewDir;
    }

    /**
     * @return string
     */
    public function getMigrationDir(): string
    {
        return $this->migrationDir;
    }

    /**
     * @return string
     */
    public function getAppEnv(): string
    {
        return $this->appEnv;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @return string
     */
    public function getTranslateDir(): string
    {
        return $this->translateDir;
    }

    /**
     * @param string $template
     */
    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    /**
     * @param string $id
     * @return string
     * @throws AppException
     */
    private function getNameService(string $id): string
    {
        if (!array_key_exists($id, $this->map)) {
            throw new AppException('Unknown service: ' . $id);
        }

        return $this->map[$id];
    }

    /**
     * @param string $class
     * @return bool
     */
    public function exist(string $class): bool
    {
        try {
            $class = $this->getNameService($class);
            return array_key_exists($class, $this->storage);
        } catch (AppException) {
            // Контейнер может иметь только фиксированный набор сервисов. Если указан неизвестный - значит он не может
            // быть добавлен.
            return false;
        }
    }

    /**
     * Паттерн контейнер внедрения зависимостей, который автоматически, через рефлексию, определяет зависимости в
     * конструкторе и создает их не используется в целях максимальной производительности
     *
     * @param string $class
     * @return object
     * @throws AppException
     */
    private function createService(string $class): object
    {
        $service = match ($class) {
            ConnectionPool::class => new ConnectionPool($this, $this->dbConfigs),
            Mailer::class => new Mailer($this, $this->mailerConfig),
            Logger::class => new Logger($this->loggerSaveLog, $this->loggerDir),
            Translation::class => new Translation($this, $this->language),
            default => new $class($this),
        };

        $this->storage[$this->map[$class]] = $service;
        return $service;
    }

    /**
     * @param string $appEnv
     * @throws AppException
     */
    private function setAppEnv(string $appEnv): void
    {
        if ($appEnv !== self::APP_PROD && $appEnv !== self::APP_DEV && $appEnv !== self::APP_TEST) {
            throw new AppException(
                'Invalid APP_ENV. Valid values: ' . self::APP_PROD . ', ' . self::APP_DEV . ', ' . self::APP_TEST
            );
        }

        $this->appEnv = $appEnv;
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

    /**
     * @param string $path
     * @param string $file
     * @return void
     */
    private function setEnv(string $path, string $file): void
    {
        $dotenv = Dotenv::createImmutable($path, $file);
        $dotenv->load();
        $this->env = $_ENV;
    }
}
