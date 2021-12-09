<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211209\Symfony\Component\EventDispatcher;

use RectorPrefix20211209\Symfony\Contracts\EventDispatcher\Event;
/**
 * Event encapsulation class.
 *
 * Encapsulates events thus decoupling the observer from the subject they encapsulate.
 *
 * @author Drak <drak@zikula.org>
 *
 * @implements \ArrayAccess<string, mixed>
 * @implements \IteratorAggregate<string, mixed>
 */
class GenericEvent extends \RectorPrefix20211209\Symfony\Contracts\EventDispatcher\Event implements \ArrayAccess, \IteratorAggregate
{
    protected $subject;
    protected $arguments;
    /**
     * Encapsulate an event with $subject and $args.
     *
     * @param mixed $subject   The subject of the event, usually an object or a callable
     * @param array $arguments Arguments to store in the event
     */
    public function __construct($subject = null, array $arguments = [])
    {
        $this->subject = $subject;
        $this->arguments = $arguments;
    }
    /**
     * Getter for subject property.
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }
    /**
     * Get argument by key.
     *
     * @throws \InvalidArgumentException if key is not found
     * @return mixed
     * @param string $key
     */
    public function getArgument($key)
    {
        if ($this->hasArgument($key)) {
            return $this->arguments[$key];
        }
        throw new \InvalidArgumentException(\sprintf('Argument "%s" not found.', $key));
    }
    /**
     * Add argument to event.
     *
     * @return $this
     * @param mixed $value
     * @param string $key
     */
    public function setArgument($key, $value)
    {
        $this->arguments[$key] = $value;
        return $this;
    }
    /**
     * Getter for all arguments.
     */
    public function getArguments() : array
    {
        return $this->arguments;
    }
    /**
     * Set args property.
     *
     * @return $this
     * @param mixed[] $args
     */
    public function setArguments($args = [])
    {
        $this->arguments = $args;
        return $this;
    }
    /**
     * Has argument.
     * @param string $key
     */
    public function hasArgument($key) : bool
    {
        return \array_key_exists($key, $this->arguments);
    }
    /**
     * ArrayAccess for argument getter.
     *
     * @param mixed $key Array key
     *
     * @throws \InvalidArgumentException if key does not exist in $this->args
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->getArgument($key);
    }
    /**
     * ArrayAccess for argument setter.
     *
     * @param mixed $key Array key to set
     * @param mixed $value
     */
    public function offsetSet($key, $value) : void
    {
        $this->setArgument($key, $value);
    }
    /**
     * ArrayAccess for unset argument.
     *
     * @param mixed $key Array key
     */
    public function offsetUnset($key) : void
    {
        if ($this->hasArgument($key)) {
            unset($this->arguments[$key]);
        }
    }
    /**
     * ArrayAccess has argument.
     *
     * @param mixed $key Array key
     */
    public function offsetExists($key) : bool
    {
        return $this->hasArgument($key);
    }
    /**
     * IteratorAggregate for iterating over the object like an array.
     */
    public function getIterator() : \ArrayIterator
    {
        return new \ArrayIterator($this->arguments);
    }
}
