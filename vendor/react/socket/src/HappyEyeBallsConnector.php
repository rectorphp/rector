<?php

namespace RectorPrefix20211231\React\Socket;

use RectorPrefix20211231\React\Dns\Resolver\ResolverInterface;
use RectorPrefix20211231\React\EventLoop\Loop;
use RectorPrefix20211231\React\EventLoop\LoopInterface;
use RectorPrefix20211231\React\Promise;
final class HappyEyeBallsConnector implements \RectorPrefix20211231\React\Socket\ConnectorInterface
{
    private $loop;
    private $connector;
    private $resolver;
    public function __construct(\RectorPrefix20211231\React\EventLoop\LoopInterface $loop = null, \RectorPrefix20211231\React\Socket\ConnectorInterface $connector = null, \RectorPrefix20211231\React\Dns\Resolver\ResolverInterface $resolver = null)
    {
        // $connector and $resolver arguments are actually required, marked
        // optional for technical reasons only. Nullable $loop without default
        // requires PHP 7.1, null default is also supported in legacy PHP
        // versions, but required parameters are not allowed after arguments
        // with null default. Mark all parameters optional and check accordingly.
        if ($connector === null || $resolver === null) {
            throw new \InvalidArgumentException('Missing required $connector or $resolver argument');
        }
        $this->loop = $loop ?: \RectorPrefix20211231\React\EventLoop\Loop::get();
        $this->connector = $connector;
        $this->resolver = $resolver;
    }
    public function connect($uri)
    {
        $original = $uri;
        if (\strpos($uri, '://') === \false) {
            $uri = 'tcp://' . $uri;
            $parts = \parse_url($uri);
            unset($parts['scheme']);
        } else {
            $parts = \parse_url($uri);
        }
        if (!$parts || !isset($parts['host'])) {
            return \RectorPrefix20211231\React\Promise\reject(new \InvalidArgumentException('Given URI "' . $original . '" is invalid (EINVAL)', \defined('SOCKET_EINVAL') ? \SOCKET_EINVAL : 22));
        }
        $host = \trim($parts['host'], '[]');
        // skip DNS lookup / URI manipulation if this URI already contains an IP
        if (\false !== \filter_var($host, \FILTER_VALIDATE_IP)) {
            return $this->connector->connect($original);
        }
        $builder = new \RectorPrefix20211231\React\Socket\HappyEyeBallsConnectionBuilder($this->loop, $this->connector, $this->resolver, $uri, $host, $parts);
        return $builder->connect();
    }
}
