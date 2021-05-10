<?php

declare(strict_types=1);

namespace Rector\Removing\ValueObject;

use PHPStan\Type\ObjectType;

final class ArgumentRemover
{
    /**
     * @param mixed $value
     */
    public function __construct(
        private string $class,
        private string $method,
        private int $position,
        private $value
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

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
