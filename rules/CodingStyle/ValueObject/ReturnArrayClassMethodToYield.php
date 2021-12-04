<?php

declare(strict_types=1);

namespace Rector\CodingStyle\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;

final class ReturnArrayClassMethodToYield
{
    public function __construct(
        private readonly string $type,
        private readonly string $method
    ) {
        RectorAssert::className($type);
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
