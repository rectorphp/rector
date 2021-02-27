<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;

final class MethodCallToReturn
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $method;

    public function __construct(string $class, string $method)
    {
        $this->class = $class;
        $this->method = $method;
    }

    public function getObjectType(): ObjectType
    {
        return new ObjectType($this->class);
    }

    public function getMethod(): string
    {
        return $this->method;
    }
}
