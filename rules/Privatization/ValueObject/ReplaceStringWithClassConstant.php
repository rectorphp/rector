<?php

declare (strict_types=1);
namespace Rector\Privatization\ValueObject;

use PHPStan\Type\ObjectType;
final class ReplaceStringWithClassConstant
{
    /**
     * @var string
     */
    private $class;
    /**
     * @var string
     */
    private $method;
    /**
     * @var int
     */
    private $argPosition;
    /**
     * @var class-string
     */
    private $classWithConstants;
    /**
     * @param class-string $classWithConstants
     */
    public function __construct(string $class, string $method, int $argPosition, string $classWithConstants)
    {
        $this->class = $class;
        $this->method = $method;
        $this->argPosition = $argPosition;
        $this->classWithConstants = $classWithConstants;
    }
    public function getObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->class);
    }
    public function getMethod() : string
    {
        return $this->method;
    }
    /**
     * @return class-string
     */
    public function getClassWithConstants() : string
    {
        return $this->classWithConstants;
    }
    public function getArgPosition() : int
    {
        return $this->argPosition;
    }
}
