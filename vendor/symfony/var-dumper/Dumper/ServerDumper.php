<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\VarDumper\Dumper;

use RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Data;
use RectorPrefix20211020\Symfony\Component\VarDumper\Dumper\ContextProvider\ContextProviderInterface;
use RectorPrefix20211020\Symfony\Component\VarDumper\Server\Connection;
/**
 * ServerDumper forwards serialized Data clones to a server.
 *
 * @author Maxime Steinhausser <maxime.steinhausser@gmail.com>
 */
class ServerDumper implements \RectorPrefix20211020\Symfony\Component\VarDumper\Dumper\DataDumperInterface
{
    private $connection;
    private $wrappedDumper;
    /**
     * @param string                     $host             The server host
     * @param DataDumperInterface|null   $wrappedDumper    A wrapped instance used whenever we failed contacting the server
     * @param ContextProviderInterface[] $contextProviders Context providers indexed by context name
     */
    public function __construct(string $host, \RectorPrefix20211020\Symfony\Component\VarDumper\Dumper\DataDumperInterface $wrappedDumper = null, array $contextProviders = [])
    {
        $this->connection = new \RectorPrefix20211020\Symfony\Component\VarDumper\Server\Connection($host, $contextProviders);
        $this->wrappedDumper = $wrappedDumper;
    }
    public function getContextProviders() : array
    {
        return $this->connection->getContextProviders();
    }
    /**
     * {@inheritdoc}
     * @param \Symfony\Component\VarDumper\Cloner\Data $data
     */
    public function dump($data)
    {
        if (!$this->connection->write($data) && $this->wrappedDumper) {
            $this->wrappedDumper->dump($data);
        }
    }
}
