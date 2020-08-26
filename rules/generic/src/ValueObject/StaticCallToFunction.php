<?php

declare(strict_types=1);

namespace Rector\Generic\ValueObject;

final class StaticCallToFunction
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $function;

    public function __construct(string $class, string $method, string $function)
    {
        $this->class = $class;
        $this->method = $method;
        $this->function = $function;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getFunction(): string
    {
        return $this->function;
    }
}
