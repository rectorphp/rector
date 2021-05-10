<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;

final class ClassConstFetchToValue
{
    /**
     * @param mixed $value
     */
    public function __construct(
        private string $class,
        private string $constant,
        private $value
    ) {
    }

    public function getObjectType(): ObjectType
    {
        return new ObjectType($this->class);
    }

    public function getConstant(): string
    {
        return $this->constant;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
