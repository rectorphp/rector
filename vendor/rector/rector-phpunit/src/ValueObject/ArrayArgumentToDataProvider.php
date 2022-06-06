<?php

declare (strict_types=1);
namespace Rector\PHPUnit\ValueObject;

use PHPStan\Type\ObjectType;
final class ArrayArgumentToDataProvider
{
    /**
     * @readonly
     * @var string
     */
    private $class;
    /**
     * @readonly
     * @var string
     */
    private $oldMethod;
    /**
     * @readonly
     * @var string
     */
    private $newMethod;
    /**
     * @readonly
     * @var string
     */
    private $variableName;
    public function __construct(string $class, string $oldMethod, string $newMethod, string $variableName)
    {
        $this->class = $class;
        $this->oldMethod = $oldMethod;
        $this->newMethod = $newMethod;
        $this->variableName = $variableName;
    }
    public function getObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->class);
    }
    public function getOldMethod() : string
    {
        return $this->oldMethod;
    }
    public function getNewMethod() : string
    {
        return $this->newMethod;
    }
    public function getVariableName() : string
    {
        return $this->variableName;
    }
}
