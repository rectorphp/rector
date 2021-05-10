<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

final class FunctionToStaticCall
{
    public function __construct(
        private string $function,
        private string $class,
        private string $method
    ) {
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
