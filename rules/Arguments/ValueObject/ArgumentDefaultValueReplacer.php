<?php

declare(strict_types=1);

namespace Rector\Arguments\ValueObject;

use PHPStan\Type\ObjectType;

final class ArgumentDefaultValueReplacer
{
    /**
     * @param mixed $valueBefore
     * @param mixed $valueAfter
     */
    public function __construct(
        private string $class,
        private string $method,
        private int $position,
        private $valueBefore,
        private $valueAfter
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
    public function getValueBefore()
    {
        return $this->valueBefore;
    }

    /**
     * @return mixed
     */
    public function getValueAfter()
    {
        return $this->valueAfter;
    }
}
