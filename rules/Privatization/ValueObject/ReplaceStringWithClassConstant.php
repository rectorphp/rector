<?php

declare(strict_types=1);

namespace Rector\Privatization\ValueObject;

use PHPStan\Type\ObjectType;

final class ReplaceStringWithClassConstant
{
    /**
     * @param class-string $classWithConstants
     */
    public function __construct(
        private string $class,
        private string $method,
        private int $argPosition,
        private string $classWithConstants
    ) {
    }

    public function getObjectType(): ObjectType
    {
        return new ObjectType($this->class);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return class-string
     */
    public function getClassWithConstants(): string
    {
        return $this->classWithConstants;
    }

    public function getArgPosition(): int
    {
        return $this->argPosition;
    }
}
