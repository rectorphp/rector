<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202304\Symfony\Component\DependencyInjection\Loader\Configurator;

use RectorPrefix202304\Symfony\Component\Config\Loader\ParamConfigurator;
class EnvConfigurator extends ParamConfigurator
{
    /**
     * @var string[]
     */
    private $stack;
    public function __construct(string $name)
    {
        $this->stack = \explode(':', $name);
    }
    public function __toString() : string
    {
        return '%env(' . \implode(':', $this->stack) . ')%';
    }
    /**
     * @return $this
     */
    public function __call(string $name, array $arguments)
    {
        $processor = \strtolower(\preg_replace(['/([A-Z]+)([A-Z][a-z])/', '/([a-z\\d])([A-Z])/'], 'RectorPrefix202304\\1_\\2', $name));
        $this->custom($processor, ...$arguments);
        return $this;
    }
    /**
     * @return $this
     */
    public function custom(string $processor, ...$args)
    {
        \array_unshift($this->stack, $processor, ...$args);
        return $this;
    }
    /**
     * @return $this
     */
    public function base64()
    {
        \array_unshift($this->stack, 'base64');
        return $this;
    }
    /**
     * @return $this
     */
    public function bool()
    {
        \array_unshift($this->stack, 'bool');
        return $this;
    }
    /**
     * @return $this
     */
    public function not()
    {
        \array_unshift($this->stack, 'not');
        return $this;
    }
    /**
     * @return $this
     */
    public function const()
    {
        \array_unshift($this->stack, 'const');
        return $this;
    }
    /**
     * @return $this
     */
    public function csv()
    {
        \array_unshift($this->stack, 'csv');
        return $this;
    }
    /**
     * @return $this
     */
    public function file()
    {
        \array_unshift($this->stack, 'file');
        return $this;
    }
    /**
     * @return $this
     */
    public function float()
    {
        \array_unshift($this->stack, 'float');
        return $this;
    }
    /**
     * @return $this
     */
    public function int()
    {
        \array_unshift($this->stack, 'int');
        return $this;
    }
    /**
     * @return $this
     */
    public function json()
    {
        \array_unshift($this->stack, 'json');
        return $this;
    }
    /**
     * @return $this
     */
    public function key(string $key)
    {
        \array_unshift($this->stack, 'key', $key);
        return $this;
    }
    /**
     * @return $this
     */
    public function url()
    {
        \array_unshift($this->stack, 'url');
        return $this;
    }
    /**
     * @return $this
     */
    public function queryString()
    {
        \array_unshift($this->stack, 'query_string');
        return $this;
    }
    /**
     * @return $this
     */
    public function resolve()
    {
        \array_unshift($this->stack, 'resolve');
        return $this;
    }
    /**
     * @return $this
     */
    public function default(string $fallbackParam)
    {
        \array_unshift($this->stack, 'default', $fallbackParam);
        return $this;
    }
    /**
     * @return $this
     */
    public function string()
    {
        \array_unshift($this->stack, 'string');
        return $this;
    }
    /**
     * @return $this
     */
    public function trim()
    {
        \array_unshift($this->stack, 'trim');
        return $this;
    }
    /**
     * @return $this
     */
    public function require()
    {
        \array_unshift($this->stack, 'require');
        return $this;
    }
}
