<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\HttpFoundation\Session\Storage\Handler;

use RectorPrefix20211020\Doctrine\DBAL\DriverManager;
use RectorPrefix20211020\Symfony\Component\Cache\Adapter\AbstractAdapter;
use RectorPrefix20211020\Symfony\Component\Cache\Traits\RedisClusterProxy;
use RectorPrefix20211020\Symfony\Component\Cache\Traits\RedisProxy;
/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
class SessionHandlerFactory
{
    /**
     * @param \Redis|\RedisArray|\RedisCluster|\Predis\ClientInterface|RedisProxy|RedisClusterProxy|\Memcached|\PDO|string $connection Connection or DSN
     */
    public static function createHandler($connection) : \RectorPrefix20211020\Symfony\Component\HttpFoundation\Session\Storage\Handler\AbstractSessionHandler
    {
        if (!\is_string($connection) && !\is_object($connection)) {
            throw new \TypeError(\sprintf('Argument 1 passed to "%s()" must be a string or a connection object, "%s" given.', __METHOD__, \get_debug_type($connection)));
        }
        switch (\true) {
            case $connection instanceof \Redis:
            case $connection instanceof \RedisArray:
            case $connection instanceof \RedisCluster:
            case $connection instanceof \RectorPrefix20211020\Predis\ClientInterface:
            case $connection instanceof \RectorPrefix20211020\Symfony\Component\Cache\Traits\RedisProxy:
            case $connection instanceof \RectorPrefix20211020\Symfony\Component\Cache\Traits\RedisClusterProxy:
                return new \RectorPrefix20211020\Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler($connection);
            case $connection instanceof \Memcached:
                return new \RectorPrefix20211020\Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler($connection);
            case $connection instanceof \PDO:
                return new \RectorPrefix20211020\Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler($connection);
            case !\is_string($connection):
                throw new \InvalidArgumentException(\sprintf('Unsupported Connection: "%s".', \get_debug_type($connection)));
            case \str_starts_with($connection, 'file://'):
                $savePath = \substr($connection, 7);
                return new \RectorPrefix20211020\Symfony\Component\HttpFoundation\Session\Storage\Handler\StrictSessionHandler(new \RectorPrefix20211020\Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler('' === $savePath ? null : $savePath));
            case \str_starts_with($connection, 'redis:'):
            case \str_starts_with($connection, 'rediss:'):
            case \str_starts_with($connection, 'memcached:'):
                if (!\class_exists(\RectorPrefix20211020\Symfony\Component\Cache\Adapter\AbstractAdapter::class)) {
                    throw new \InvalidArgumentException(\sprintf('Unsupported DSN "%s". Try running "composer require symfony/cache".', $connection));
                }
                $handlerClass = \str_starts_with($connection, 'memcached:') ? \RectorPrefix20211020\Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler::class : \RectorPrefix20211020\Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler::class;
                $connection = \RectorPrefix20211020\Symfony\Component\Cache\Adapter\AbstractAdapter::createConnection($connection, ['lazy' => \true]);
                return new $handlerClass($connection);
            case \str_starts_with($connection, 'pdo_oci://'):
                if (!\class_exists(\RectorPrefix20211020\Doctrine\DBAL\DriverManager::class)) {
                    throw new \InvalidArgumentException(\sprintf('Unsupported DSN "%s". Try running "composer require doctrine/dbal".', $connection));
                }
                $connection = \RectorPrefix20211020\Doctrine\DBAL\DriverManager::getConnection(['url' => $connection])->getWrappedConnection();
            // no break;
            case \str_starts_with($connection, 'mssql://'):
            case \str_starts_with($connection, 'mysql://'):
            case \str_starts_with($connection, 'mysql2://'):
            case \str_starts_with($connection, 'pgsql://'):
            case \str_starts_with($connection, 'postgres://'):
            case \str_starts_with($connection, 'postgresql://'):
            case \str_starts_with($connection, 'sqlsrv://'):
            case \str_starts_with($connection, 'sqlite://'):
            case \str_starts_with($connection, 'sqlite3://'):
                return new \RectorPrefix20211020\Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler($connection);
        }
        throw new \InvalidArgumentException(\sprintf('Unsupported Connection: "%s".', $connection));
    }
}
