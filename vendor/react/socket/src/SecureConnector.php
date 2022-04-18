<?php

namespace RectorPrefix20220418\React\Socket;

use RectorPrefix20220418\React\EventLoop\Loop;
use RectorPrefix20220418\React\EventLoop\LoopInterface;
use RectorPrefix20220418\React\Promise;
use BadMethodCallException;
use InvalidArgumentException;
use UnexpectedValueException;
final class SecureConnector implements \RectorPrefix20220418\React\Socket\ConnectorInterface
{
    private $connector;
    private $streamEncryption;
    private $context;
    public function __construct(\RectorPrefix20220418\React\Socket\ConnectorInterface $connector, \RectorPrefix20220418\React\EventLoop\LoopInterface $loop = null, array $context = array())
    {
        $this->connector = $connector;
        $this->streamEncryption = new \RectorPrefix20220418\React\Socket\StreamEncryption($loop ?: \RectorPrefix20220418\React\EventLoop\Loop::get(), \false);
        $this->context = $context;
    }
    public function connect($uri)
    {
        if (!\function_exists('stream_socket_enable_crypto')) {
            return \RectorPrefix20220418\React\Promise\reject(new \BadMethodCallException('Encryption not supported on your platform (HHVM < 3.8?)'));
            // @codeCoverageIgnore
        }
        if (\strpos($uri, '://') === \false) {
            $uri = 'tls://' . $uri;
        }
        $parts = \parse_url($uri);
        if (!$parts || !isset($parts['scheme']) || $parts['scheme'] !== 'tls') {
            return \RectorPrefix20220418\React\Promise\reject(new \InvalidArgumentException('Given URI "' . $uri . '" is invalid (EINVAL)', \defined('SOCKET_EINVAL') ? \SOCKET_EINVAL : 22));
        }
        $context = $this->context;
        $encryption = $this->streamEncryption;
        $connected = \false;
        $promise = $this->connector->connect(\str_replace('tls://', '', $uri))->then(function (\RectorPrefix20220418\React\Socket\ConnectionInterface $connection) use($context, $encryption, $uri, &$promise, &$connected) {
            // (unencrypted) TCP/IP connection succeeded
            $connected = \true;
            if (!$connection instanceof \RectorPrefix20220418\React\Socket\Connection) {
                $connection->close();
                throw new \UnexpectedValueException('Base connector does not use internal Connection class exposing stream resource');
            }
            // set required SSL/TLS context options
            foreach ($context as $name => $value) {
                \stream_context_set_option($connection->stream, 'ssl', $name, $value);
            }
            // try to enable encryption
            return $promise = $encryption->enable($connection)->then(null, function ($error) use($connection, $uri) {
                // establishing encryption failed => close invalid connection and return error
                $connection->close();
                throw new \RuntimeException('Connection to ' . $uri . ' failed during TLS handshake: ' . $error->getMessage(), $error->getCode());
            });
        }, function (\Exception $e) use($uri) {
            if ($e instanceof \RuntimeException) {
                $message = \preg_replace('/^Connection to [^ ]+/', '', $e->getMessage());
                $e = new \RuntimeException('Connection to ' . $uri . $message, $e->getCode(), $e);
                // avoid garbage references by replacing all closures in call stack.
                // what a lovely piece of code!
                $r = new \ReflectionProperty('Exception', 'trace');
                $r->setAccessible(\true);
                $trace = $r->getValue($e);
                // Exception trace arguments are not available on some PHP 7.4 installs
                // @codeCoverageIgnoreStart
                foreach ($trace as &$one) {
                    if (isset($one['args'])) {
                        foreach ($one['args'] as &$arg) {
                            if ($arg instanceof \Closure) {
                                $arg = 'Object(' . \get_class($arg) . ')';
                            }
                        }
                    }
                }
                // @codeCoverageIgnoreEnd
                $r->setValue($e, $trace);
            }
            throw $e;
        });
        return new \RectorPrefix20220418\React\Promise\Promise(function ($resolve, $reject) use($promise) {
            $promise->then($resolve, $reject);
        }, function ($_, $reject) use(&$promise, $uri, &$connected) {
            if ($connected) {
                $reject(new \RuntimeException('Connection to ' . $uri . ' cancelled during TLS handshake (ECONNABORTED)', \defined('SOCKET_ECONNABORTED') ? \SOCKET_ECONNABORTED : 103));
            }
            $promise->cancel();
            $promise = null;
        });
    }
}
