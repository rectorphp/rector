<?php

namespace RectorPrefix20211123\React\Socket;

use RectorPrefix20211123\Evenement\EventEmitter;
use RectorPrefix20211123\React\EventLoop\LoopInterface;
final class SocketServer extends \RectorPrefix20211123\Evenement\EventEmitter implements \RectorPrefix20211123\React\Socket\ServerInterface
{
    private $server;
    /**
     * The `SocketServer` class is the main class in this package that implements the `ServerInterface` and
     * allows you to accept incoming streaming connections, such as plaintext TCP/IP or secure TLS connection streams.
     *
     * ```php
     * $socket = new React\Socket\SocketServer('127.0.0.1:0');
     * $socket = new React\Socket\SocketServer('127.0.0.1:8000');
     * $socket = new React\Socket\SocketServer('127.0.0.1:8000', $context);
     * ```
     *
     * This class takes an optional `LoopInterface|null $loop` parameter that can be used to
     * pass the event loop instance to use for this object. You can use a `null` value
     * here in order to use the [default loop](https://github.com/reactphp/event-loop#loop).
     * This value SHOULD NOT be given unless you're sure you want to explicitly use a
     * given event loop instance.
     *
     * @param string         $uri
     * @param array          $context
     * @param ?LoopInterface $loop
     * @throws \InvalidArgumentException if the listening address is invalid
     * @throws \RuntimeException if listening on this address fails (already in use etc.)
     */
    public function __construct($uri, array $context = array(), \RectorPrefix20211123\React\EventLoop\LoopInterface $loop = null)
    {
        // apply default options if not explicitly given
        $context += array('tcp' => array(), 'tls' => array(), 'unix' => array());
        $scheme = 'tcp';
        $pos = \strpos($uri, '://');
        if ($pos !== \false) {
            $scheme = \substr($uri, 0, $pos);
        }
        if ($scheme === 'unix') {
            $server = new \RectorPrefix20211123\React\Socket\UnixServer($uri, $loop, $context['unix']);
        } else {
            if (\preg_match('#^(?:\\w+://)?\\d+$#', $uri)) {
                throw new \InvalidArgumentException('Invalid URI given');
            }
            $server = new \RectorPrefix20211123\React\Socket\TcpServer(\str_replace('tls://', '', $uri), $loop, $context['tcp']);
            if ($scheme === 'tls') {
                $server = new \RectorPrefix20211123\React\Socket\SecureServer($server, $loop, $context['tls']);
            }
        }
        $this->server = $server;
        $that = $this;
        $server->on('connection', function (\RectorPrefix20211123\React\Socket\ConnectionInterface $conn) use($that) {
            $that->emit('connection', array($conn));
        });
        $server->on('error', function (\Exception $error) use($that) {
            $that->emit('error', array($error));
        });
    }
    public function getAddress()
    {
        return $this->server->getAddress();
    }
    public function pause()
    {
        $this->server->pause();
    }
    public function resume()
    {
        $this->server->resume();
    }
    public function close()
    {
        $this->server->close();
    }
}
