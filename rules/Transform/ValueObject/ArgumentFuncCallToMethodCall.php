<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

final class ArgumentFuncCallToMethodCall
{
    public function __construct(
        private string $function,
        private string $class,
        private ?string $methodIfArgs = null,
        private ?string $methodIfNoArgs = null
    ) {
    }

    public function getFunction(): string
    {
        return $this->function;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getMethodIfNoArgs(): ?string
    {
        return $this->methodIfNoArgs;
    }

    public function getMethodIfArgs(): ?string
    {
        return $this->methodIfArgs;
    }
}
