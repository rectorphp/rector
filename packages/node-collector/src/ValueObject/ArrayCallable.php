<?php

declare(strict_types=1);

namespace Rector\NodeCollector\ValueObject;

use ReflectionMethod;

final class ArrayCallable
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $method;

    public function __construct(string $class, string $method)
    {
        $this->class = $class;
        $this->method = $method;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function isExistingMethod(): bool
    {
        return method_exists($this->class, $this->method);
    }

    public function getReflectionMethod(): ReflectionMethod
    {
        return new ReflectionMethod($this->class, $this->method);
    }
}
