<?php

declare (strict_types=1);
namespace Rector\Laravel\ValueObject;

use PHPStan\Type\ObjectType;
final class TypeToTimeMethodAndPosition
{
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $methodName;
    /**
     * @var int
     */
    private $position;
    public function __construct(string $type, string $methodName, int $position)
    {
        $this->type = $type;
        $this->methodName = $methodName;
        $this->position = $position;
    }
    public function getObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->type);
    }
    public function getMethodName() : string
    {
        return $this->methodName;
    }
    public function getPosition() : int
    {
        return $this->position;
    }
}
