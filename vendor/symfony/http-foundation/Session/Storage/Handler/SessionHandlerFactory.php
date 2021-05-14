<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20210514\Symfony\Component\HttpFoundation\Session\Storage\Handler;

use RectorPrefix20210514\Doctrine\DBAL\DriverManager;
use RectorPrefix20210514\Symfony\Component\Cache\Adapter\AbstractAdapter;
use RectorPrefix20210514\Symfony\Component\Cache\Traits\RedisClusterProxy;
use RectorPrefix20210514\Symfony\Component\Cache\Traits\RedisProxy;
/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
class SessionHandlerFactory
{
    /**
     * @param \Redis|\RedisArray|\RedisCluster|\Predis\ClientInterface|RedisProxy|RedisClusterProxy|\Memcached|\PDO|string $connection Connection or DSN
     */
    public static function createHandler($connection) : \RectorPrefix20210514\Symfony\Component\HttpFoundation\Session\Storage\Handler\AbstractSessionHandler
    {
        if (!\is_string($connection) && !\is_object($connection)) {
            throw new \TypeError(\sprintf('Argument 1 passed to "%s()" must be a string or a connection object, "%s" given.', __METHOD__, \get_debug_type($connection)));
        }
        switch (\true) {
            case $connection instanceof \Redis:
            case $connection instanceof \RedisArray:
            case $connection instanceof \RedisCluster:
            case $connection instanceof \RectorPrefix20210514\Predis\ClientInterface:
            case $connection instanceof \RectorPrefix20210514\Symfony\Component\Cache\Traits\RedisProxy:
            case $connection instanceof \RectorPrefix20210514\Symfony\Component\Cache\Traits\RedisClusterProxy:
                return new \RectorPrefix20210514\Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler($connection);
            case $connection instanceof \Memcached:
                return new \RectorPrefix20210514\Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler($connection);
            case $connection instanceof \PDO:
                return new \RectorPrefix20210514\Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler($connection);
            case !\is_string($connection):
                throw new \InvalidArgumentException(\sprintf('Unsupported Connection: "%s".', \get_debug_type($connection)));
            case 0 === \strpos($connection, 'file://'):
                return new \RectorPrefix20210514\Symfony\Component\HttpFoundation\Session\Storage\Handler\StrictSessionHandler(new \RectorPrefix20210514\Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler(\substr($connection, 7)));
            case 0 === \strpos($connection, 'redis:'):
            case 0 === \strpos($connection, 'rediss:'):
            case 0 === \strpos($connection, 'memcached:'):
                if (!\class_exists(\RectorPrefix20210514\Symfony\Component\Cache\Adapter\AbstractAdapter::class)) {
                    throw new \InvalidArgumentException(\sprintf('Unsupported DSN "%s". Try running "composer require symfony/cache".', $connection));
                }
                $handlerClass = 0 === \strpos($connection, 'memcached:') ? \RectorPrefix20210514\Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler::class : \RectorPrefix20210514\Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler::class;
                $connection = \RectorPrefix20210514\Symfony\Component\Cache\Adapter\AbstractAdapter::createConnection($connection, ['lazy' => \true]);
                return new $handlerClass($connection);
            case 0 === \strpos($connection, 'pdo_oci://'):
                if (!\class_exists(\RectorPrefix20210514\Doctrine\DBAL\DriverManager::class)) {
                    throw new \InvalidArgumentException(\sprintf('Unsupported DSN "%s". Try running "composer require doctrine/dbal".', $connection));
                }
                $connection = \RectorPrefix20210514\Doctrine\DBAL\DriverManager::getConnection(['url' => $connection])->getWrappedConnection();
            // no break;
            case 0 === \strpos($connection, 'mssql://'):
            case 0 === \strpos($connection, 'mysql://'):
            case 0 === \strpos($connection, 'mysql2://'):
            case 0 === \strpos($connection, 'pgsql://'):
            case 0 === \strpos($connection, 'postgres://'):
            case 0 === \strpos($connection, 'postgresql://'):
            case 0 === \strpos($connection, 'sqlsrv://'):
            case 0 === \strpos($connection, 'sqlite://'):
            case 0 === \strpos($connection, 'sqlite3://'):
                return new \RectorPrefix20210514\Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler($connection);
        }
        throw new \InvalidArgumentException(\sprintf('Unsupported Connection: "%s".', $connection));
    }
}
