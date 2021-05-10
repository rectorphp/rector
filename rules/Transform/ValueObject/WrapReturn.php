<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;

final class WrapReturn
{
    public function __construct(
        private string $type,
        private string $method,
        private bool $isArrayWrap
    ) {
    }

    public function getObjectType(): ObjectType
    {
        return new ObjectType($this->type);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function isArrayWrap(): bool
    {
        return $this->isArrayWrap;
    }
}
