<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;

final class NewArgToMethodCall
{
    /**
     * @param mixed $value
     */
    public function __construct(
        private string $type,
        private $value,
        private string $methodCall
    ) {
    }

    public function getObjectType(): ObjectType
    {
        return new ObjectType($this->type);
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getMethodCall(): string
    {
        return $this->methodCall;
    }
}
