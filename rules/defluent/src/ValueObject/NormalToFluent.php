<?php

declare(strict_types=1);

namespace Rector\Defluent\ValueObject;

use PHPStan\Type\ObjectType;

final class NormalToFluent
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string[]
     */
    private $methodNames = [];

    /**
     * @param string[] $methodNames
     */
    public function __construct(string $class, array $methodNames)
    {
        $this->class = $class;
        $this->methodNames = $methodNames;
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
