<?php

declare(strict_types=1);

namespace Rector\CodingStyle\ValueObject;

use PHPStan\Type\ObjectType;

final class ReturnArrayClassMethodToYield
{
    public function __construct(
        private string $type,
        private string $method
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
}
