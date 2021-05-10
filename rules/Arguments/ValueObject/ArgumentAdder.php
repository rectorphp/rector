<?php

declare(strict_types=1);

namespace Rector\Arguments\ValueObject;

use PHPStan\Type\ObjectType;

final class ArgumentAdder
{
    /**
     * @param mixed|null $argumentDefaultValue
     */
    public function __construct(
        private string $class,
        private string $method,
        private int $position,
        private ?string $argumentName = null,
        private $argumentDefaultValue = null,
        private ?string $argumentType = null,
        private ?string $scope = null
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

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getArgumentName(): ?string
    {
        return $this->argumentName;
    }

    /**
     * @return mixed|null
     */
    public function getArgumentDefaultValue()
    {
        return $this->argumentDefaultValue;
    }

    public function getArgumentType(): ?string
    {
        return $this->argumentType;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }
}
