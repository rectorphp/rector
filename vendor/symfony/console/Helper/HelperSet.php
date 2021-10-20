<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\Console\Helper;

use RectorPrefix20211020\Symfony\Component\Console\Command\Command;
use RectorPrefix20211020\Symfony\Component\Console\Exception\InvalidArgumentException;
/**
 * HelperSet represents a set of helpers to be used with a command.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class HelperSet implements \IteratorAggregate
{
    /**
     * @var Helper[]
     */
    private $helpers = [];
    private $command;
    /**
     * @param Helper[] $helpers An array of helper
     */
    public function __construct(array $helpers = [])
    {
        foreach ($helpers as $alias => $helper) {
            $this->set($helper, \is_int($alias) ? null : $alias);
        }
    }
    /**
     * @param \Symfony\Component\Console\Helper\HelperInterface $helper
     * @param string|null $alias
     */
    public function set($helper, $alias = null)
    {
        $this->helpers[$helper->getName()] = $helper;
        if (null !== $alias) {
            $this->helpers[$alias] = $helper;
        }
        $helper->setHelperSet($this);
    }
    /**
     * Returns true if the helper if defined.
     *
     * @return bool true if the helper is defined, false otherwise
     * @param string $name
     */
    public function has($name)
    {
        return isset($this->helpers[$name]);
    }
    /**
     * Gets a helper value.
     *
     * @return HelperInterface The helper instance
     *
     * @throws InvalidArgumentException if the helper is not defined
     * @param string $name
     */
    public function get($name)
    {
        if (!$this->has($name)) {
            throw new \RectorPrefix20211020\Symfony\Component\Console\Exception\InvalidArgumentException(\sprintf('The helper "%s" is not defined.', $name));
        }
        return $this->helpers[$name];
    }
    /**
     * @param \Symfony\Component\Console\Command\Command|null $command
     */
    public function setCommand($command = null)
    {
        $this->command = $command;
    }
    /**
     * Gets the command associated with this helper set.
     *
     * @return Command A Command instance
     */
    public function getCommand()
    {
        return $this->command;
    }
    /**
     * @return \Traversable<Helper>
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new \ArrayIterator($this->helpers);
    }
}
