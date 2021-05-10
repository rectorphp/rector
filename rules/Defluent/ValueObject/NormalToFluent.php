<?php

declare(strict_types=1);

namespace Rector\Defluent\ValueObject;

use PHPStan\Type\ObjectType;

final class NormalToFluent
{
    /**
     * @param string[] $methodNames
     */
    public function __construct(
        private string $class,
        private array $methodNames
    ) {
    }

    public function getObjectType(): ObjectType
    {
        return new ObjectType($this->class);
    }

    /**
     * @return string[]
     */
    public function getMethodNames(): array
    {
        return $this->methodNames;
    }
}
