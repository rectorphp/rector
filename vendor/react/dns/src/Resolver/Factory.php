<?php

namespace RectorPrefix20220531\React\Dns\Resolver;

use RectorPrefix20220531\React\Cache\ArrayCache;
use RectorPrefix20220531\React\Cache\CacheInterface;
use RectorPrefix20220531\React\Dns\Config\Config;
use RectorPrefix20220531\React\Dns\Config\HostsFile;
use RectorPrefix20220531\React\Dns\Query\CachingExecutor;
use RectorPrefix20220531\React\Dns\Query\CoopExecutor;
use RectorPrefix20220531\React\Dns\Query\ExecutorInterface;
use RectorPrefix20220531\React\Dns\Query\FallbackExecutor;
use RectorPrefix20220531\React\Dns\Query\HostsFileExecutor;
use RectorPrefix20220531\React\Dns\Query\RetryExecutor;
use RectorPrefix20220531\React\Dns\Query\SelectiveTransportExecutor;
use RectorPrefix20220531\React\Dns\Query\TcpTransportExecutor;
use RectorPrefix20220531\React\Dns\Query\TimeoutExecutor;
use RectorPrefix20220531\React\Dns\Query\UdpTransportExecutor;
use RectorPrefix20220531\React\EventLoop\Loop;
use RectorPrefix20220531\React\EventLoop\LoopInterface;
final class Factory
{
    /**
     * Creates a DNS resolver instance for the given DNS config
     *
     * As of v1.7.0 it's recommended to pass a `Config` object instead of a
     * single nameserver address. If the given config contains more than one DNS
     * nameserver, all DNS nameservers will be used in order. The primary DNS
     * server will always be used first before falling back to the secondary or
     * tertiary DNS server.
     *
     * @param Config|string  $config DNS Config object (recommended) or single nameserver address
     * @param ?LoopInterface $loop
     * @return \React\Dns\Resolver\ResolverInterface
     * @throws \InvalidArgumentException for invalid DNS server address
     * @throws \UnderflowException when given DNS Config object has an empty list of nameservers
     */
    public function create($config, \RectorPrefix20220531\React\EventLoop\LoopInterface $loop = null)
    {
        $executor = $this->decorateHostsFileExecutor($this->createExecutor($config, $loop ?: \RectorPrefix20220531\React\EventLoop\Loop::get()));
        return new \RectorPrefix20220531\React\Dns\Resolver\Resolver($executor);
    }
    /**
     * Creates a cached DNS resolver instance for the given DNS config and cache
     *
     * As of v1.7.0 it's recommended to pass a `Config` object instead of a
     * single nameserver address. If the given config contains more than one DNS
     * nameserver, all DNS nameservers will be used in order. The primary DNS
     * server will always be used first before falling back to the secondary or
     * tertiary DNS server.
     *
     * @param Config|string   $config DNS Config object (recommended) or single nameserver address
     * @param ?LoopInterface  $loop
     * @param ?CacheInterface $cache
     * @return \React\Dns\Resolver\ResolverInterface
     * @throws \InvalidArgumentException for invalid DNS server address
     * @throws \UnderflowException when given DNS Config object has an empty list of nameservers
     */
    public function createCached($config, \RectorPrefix20220531\React\EventLoop\LoopInterface $loop = null, \RectorPrefix20220531\React\Cache\CacheInterface $cache = null)
    {
        // default to keeping maximum of 256 responses in cache unless explicitly given
        if (!$cache instanceof \RectorPrefix20220531\React\Cache\CacheInterface) {
            $cache = new \RectorPrefix20220531\React\Cache\ArrayCache(256);
        }
        $executor = $this->createExecutor($config, $loop ?: \RectorPrefix20220531\React\EventLoop\Loop::get());
        $executor = new \RectorPrefix20220531\React\Dns\Query\CachingExecutor($executor, $cache);
        $executor = $this->decorateHostsFileExecutor($executor);
        return new \RectorPrefix20220531\React\Dns\Resolver\Resolver($executor);
    }
    /**
     * Tries to load the hosts file and decorates the given executor on success
     *
     * @param ExecutorInterface $executor
     * @return ExecutorInterface
     * @codeCoverageIgnore
     */
    private function decorateHostsFileExecutor(\RectorPrefix20220531\React\Dns\Query\ExecutorInterface $executor)
    {
        try {
            $executor = new \RectorPrefix20220531\React\Dns\Query\HostsFileExecutor(\RectorPrefix20220531\React\Dns\Config\HostsFile::loadFromPathBlocking(), $executor);
        } catch (\RuntimeException $e) {
            // ignore this file if it can not be loaded
        }
        // Windows does not store localhost in hosts file by default but handles this internally
        // To compensate for this, we explicitly use hard-coded defaults for localhost
        if (\DIRECTORY_SEPARATOR === '\\') {
            $executor = new \RectorPrefix20220531\React\Dns\Query\HostsFileExecutor(new \RectorPrefix20220531\React\Dns\Config\HostsFile("127.0.0.1 localhost\n::1 localhost"), $executor);
        }
        return $executor;
    }
    /**
     * @param Config|string $nameserver
     * @param LoopInterface $loop
     * @return CoopExecutor
     * @throws \InvalidArgumentException for invalid DNS server address
     * @throws \UnderflowException when given DNS Config object has an empty list of nameservers
     */
    private function createExecutor($nameserver, \RectorPrefix20220531\React\EventLoop\LoopInterface $loop)
    {
        if ($nameserver instanceof \RectorPrefix20220531\React\Dns\Config\Config) {
            if (!$nameserver->nameservers) {
                throw new \UnderflowException('Empty config with no DNS servers');
            }
            // Hard-coded to check up to 3 DNS servers to match default limits in place in most systems (see MAXNS config).
            // Note to future self: Recursion isn't too hard, but how deep do we really want to go?
            $primary = \reset($nameserver->nameservers);
            $secondary = \next($nameserver->nameservers);
            $tertiary = \next($nameserver->nameservers);
            if ($tertiary !== \false) {
                // 3 DNS servers given => nest first with fallback for second and third
                return new \RectorPrefix20220531\React\Dns\Query\CoopExecutor(new \RectorPrefix20220531\React\Dns\Query\RetryExecutor(new \RectorPrefix20220531\React\Dns\Query\FallbackExecutor($this->createSingleExecutor($primary, $loop), new \RectorPrefix20220531\React\Dns\Query\FallbackExecutor($this->createSingleExecutor($secondary, $loop), $this->createSingleExecutor($tertiary, $loop)))));
            } elseif ($secondary !== \false) {
                // 2 DNS servers given => fallback from first to second
                return new \RectorPrefix20220531\React\Dns\Query\CoopExecutor(new \RectorPrefix20220531\React\Dns\Query\RetryExecutor(new \RectorPrefix20220531\React\Dns\Query\FallbackExecutor($this->createSingleExecutor($primary, $loop), $this->createSingleExecutor($secondary, $loop))));
            } else {
                // 1 DNS server given => use single executor
                $nameserver = $primary;
            }
        }
        return new \RectorPrefix20220531\React\Dns\Query\CoopExecutor(new \RectorPrefix20220531\React\Dns\Query\RetryExecutor($this->createSingleExecutor($nameserver, $loop)));
    }
    /**
     * @param string $nameserver
     * @param LoopInterface $loop
     * @return ExecutorInterface
     * @throws \InvalidArgumentException for invalid DNS server address
     */
    private function createSingleExecutor($nameserver, \RectorPrefix20220531\React\EventLoop\LoopInterface $loop)
    {
        $parts = \parse_url($nameserver);
        if (isset($parts['scheme']) && $parts['scheme'] === 'tcp') {
            $executor = $this->createTcpExecutor($nameserver, $loop);
        } elseif (isset($parts['scheme']) && $parts['scheme'] === 'udp') {
            $executor = $this->createUdpExecutor($nameserver, $loop);
        } else {
            $executor = new \RectorPrefix20220531\React\Dns\Query\SelectiveTransportExecutor($this->createUdpExecutor($nameserver, $loop), $this->createTcpExecutor($nameserver, $loop));
        }
        return $executor;
    }
    /**
     * @param string $nameserver
     * @param LoopInterface $loop
     * @return TimeoutExecutor
     * @throws \InvalidArgumentException for invalid DNS server address
     */
    private function createTcpExecutor($nameserver, \RectorPrefix20220531\React\EventLoop\LoopInterface $loop)
    {
        return new \RectorPrefix20220531\React\Dns\Query\TimeoutExecutor(new \RectorPrefix20220531\React\Dns\Query\TcpTransportExecutor($nameserver, $loop), 5.0, $loop);
    }
    /**
     * @param string $nameserver
     * @param LoopInterface $loop
     * @return TimeoutExecutor
     * @throws \InvalidArgumentException for invalid DNS server address
     */
    private function createUdpExecutor($nameserver, \RectorPrefix20220531\React\EventLoop\LoopInterface $loop)
    {
        return new \RectorPrefix20220531\React\Dns\Query\TimeoutExecutor(new \RectorPrefix20220531\React\Dns\Query\UdpTransportExecutor($nameserver, $loop), 5.0, $loop);
    }
}
