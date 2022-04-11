<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

final class StaticCallRecipe
{
    public function __construct(
        private readonly string $className,
        private readonly string $methodName,
    ) {
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }
}
