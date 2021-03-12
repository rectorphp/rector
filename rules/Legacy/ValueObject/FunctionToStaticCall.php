<?php

declare(strict_types=1);

namespace Rector\Legacy\ValueObject;

final class FunctionToStaticCall
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

    public function __construct(string $function, string $class, string $method)
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
