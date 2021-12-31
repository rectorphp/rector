<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211231\Symfony\Component\Console\Helper;

use RectorPrefix20211231\Symfony\Component\Console\Exception\InvalidArgumentException;
/**
 * HelperSet represents a set of helpers to be used with a command.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @implements \IteratorAggregate<string, Helper>
 */
class HelperSet implements \IteratorAggregate
{
    /** @var array<string, Helper> */
    private $helpers = [];
    /**
     * @param Helper[] $helpers An array of helper
     */
    public function __construct(array $helpers = [])
    {
        foreach ($helpers as $alias => $helper) {
            $this->set($helper, \is_int($alias) ? null : $alias);
        }
    }
    public function set(\RectorPrefix20211231\Symfony\Component\Console\Helper\HelperInterface $helper, string $alias = null)
    {
        $this->helpers[$helper->getName()] = $helper;
        if (null !== $alias) {
            $this->helpers[$alias] = $helper;
        }
        $helper->setHelperSet($this);
    }
    /**
     * Returns true if the helper if defined.
     */
    public function has(string $name) : bool
    {
        return isset($this->helpers[$name]);
    }
    /**
     * Gets a helper value.
     *
     * @throws InvalidArgumentException if the helper is not defined
     */
    public function get(string $name) : \RectorPrefix20211231\Symfony\Component\Console\Helper\HelperInterface
    {
        if (!$this->has($name)) {
            throw new \RectorPrefix20211231\Symfony\Component\Console\Exception\InvalidArgumentException(\sprintf('The helper "%s" is not defined.', $name));
        }
        return $this->helpers[$name];
    }
    public function getIterator() : \Traversable
    {
        return new \ArrayIterator($this->helpers);
    }
}
