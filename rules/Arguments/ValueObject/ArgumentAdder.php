<?php

declare(strict_types=1);

namespace Rector\Arguments\ValueObject;

use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Core\Validation\RectorAssert;

final class ArgumentAdder
{
    /**
     * @param mixed|null $argumentDefaultValue
     */
    public function __construct(
        private readonly string $class,
        private readonly string $method,
        private readonly int $position,
        private readonly ?string $argumentName = null,
        private $argumentDefaultValue = null,
        private readonly Type | null $argumentType = null,
        private readonly ?string $scope = null
    ) {
        RectorAssert::className($class);
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

    public function getArgumentType(): ?Type
    {
        return $this->argumentType;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }
}
