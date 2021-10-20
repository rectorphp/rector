<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\VarDumper\Caster;

use RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Stub;
/**
 * Casts Redis class from ext-redis to array representation.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 *
 * @final
 */
class RedisCaster
{
    private const SERIALIZERS = [\Redis::SERIALIZER_NONE => 'NONE', \Redis::SERIALIZER_PHP => 'PHP', 2 => 'IGBINARY'];
    private const MODES = [\Redis::ATOMIC => 'ATOMIC', \Redis::MULTI => 'MULTI', \Redis::PIPELINE => 'PIPELINE'];
    private const COMPRESSION_MODES = [
        0 => 'NONE',
        // Redis::COMPRESSION_NONE
        1 => 'LZF',
    ];
    private const FAILOVER_OPTIONS = [\RedisCluster::FAILOVER_NONE => 'NONE', \RedisCluster::FAILOVER_ERROR => 'ERROR', \RedisCluster::FAILOVER_DISTRIBUTE => 'DISTRIBUTE', \RedisCluster::FAILOVER_DISTRIBUTE_SLAVES => 'DISTRIBUTE_SLAVES'];
    public static function castRedis(\Redis $c, array $a, \RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested)
    {
        $prefix = \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\Caster::PREFIX_VIRTUAL;
        if (!($connected = $c->isConnected())) {
            return $a + [$prefix . 'isConnected' => $connected];
        }
        $mode = $c->getMode();
        return $a + [$prefix . 'isConnected' => $connected, $prefix . 'host' => $c->getHost(), $prefix . 'port' => $c->getPort(), $prefix . 'auth' => $c->getAuth(), $prefix . 'mode' => isset(self::MODES[$mode]) ? new \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\ConstStub(self::MODES[$mode], $mode) : $mode, $prefix . 'dbNum' => $c->getDbNum(), $prefix . 'timeout' => $c->getTimeout(), $prefix . 'lastError' => $c->getLastError(), $prefix . 'persistentId' => $c->getPersistentID(), $prefix . 'options' => self::getRedisOptions($c)];
    }
    public static function castRedisArray(\RedisArray $c, array $a, \RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested)
    {
        $prefix = \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\Caster::PREFIX_VIRTUAL;
        return $a + [$prefix . 'hosts' => $c->_hosts(), $prefix . 'function' => \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\ClassStub::wrapCallable($c->_function()), $prefix . 'lastError' => $c->getLastError(), $prefix . 'options' => self::getRedisOptions($c)];
    }
    public static function castRedisCluster(\RedisCluster $c, array $a, \RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested)
    {
        $prefix = \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\Caster::PREFIX_VIRTUAL;
        $failover = $c->getOption(\RedisCluster::OPT_SLAVE_FAILOVER);
        $a += [$prefix . '_masters' => $c->_masters(), $prefix . '_redir' => $c->_redir(), $prefix . 'mode' => new \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\ConstStub($c->getMode() ? 'MULTI' : 'ATOMIC', $c->getMode()), $prefix . 'lastError' => $c->getLastError(), $prefix . 'options' => self::getRedisOptions($c, ['SLAVE_FAILOVER' => isset(self::FAILOVER_OPTIONS[$failover]) ? new \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\ConstStub(self::FAILOVER_OPTIONS[$failover], $failover) : $failover])];
        return $a;
    }
    /**
     * @param \Redis|\RedisArray|\RedisCluster $redis
     */
    private static function getRedisOptions($redis, array $options = []) : \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\EnumStub
    {
        $serializer = $redis->getOption(\Redis::OPT_SERIALIZER);
        if (\is_array($serializer)) {
            foreach ($serializer as &$v) {
                if (isset(self::SERIALIZERS[$v])) {
                    $v = new \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\ConstStub(self::SERIALIZERS[$v], $v);
                }
            }
        } elseif (isset(self::SERIALIZERS[$serializer])) {
            $serializer = new \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\ConstStub(self::SERIALIZERS[$serializer], $serializer);
        }
        $compression = \defined('Redis::OPT_COMPRESSION') ? $redis->getOption(\Redis::OPT_COMPRESSION) : 0;
        if (\is_array($compression)) {
            foreach ($compression as &$v) {
                if (isset(self::COMPRESSION_MODES[$v])) {
                    $v = new \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\ConstStub(self::COMPRESSION_MODES[$v], $v);
                }
            }
        } elseif (isset(self::COMPRESSION_MODES[$compression])) {
            $compression = new \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\ConstStub(self::COMPRESSION_MODES[$compression], $compression);
        }
        $retry = \defined('Redis::OPT_SCAN') ? $redis->getOption(\Redis::OPT_SCAN) : 0;
        if (\is_array($retry)) {
            foreach ($retry as &$v) {
                $v = new \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\ConstStub($v ? 'RETRY' : 'NORETRY', $v);
            }
        } else {
            $retry = new \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\ConstStub($retry ? 'RETRY' : 'NORETRY', $retry);
        }
        $options += ['TCP_KEEPALIVE' => \defined('Redis::OPT_TCP_KEEPALIVE') ? $redis->getOption(\Redis::OPT_TCP_KEEPALIVE) : 0, 'READ_TIMEOUT' => $redis->getOption(\Redis::OPT_READ_TIMEOUT), 'COMPRESSION' => $compression, 'SERIALIZER' => $serializer, 'PREFIX' => $redis->getOption(\Redis::OPT_PREFIX), 'SCAN' => $retry];
        return new \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\EnumStub($options);
    }
}
