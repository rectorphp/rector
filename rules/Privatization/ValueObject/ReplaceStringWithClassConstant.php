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
        private readonly string $class,
        private readonly string $method,
        private readonly int $argPosition,
        private readonly string $classWithConstants,
        private readonly bool $caseInsensitive = false
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

    public function isCaseInsensitive(): bool
    {
        return $this->caseInsensitive;
    }
}
