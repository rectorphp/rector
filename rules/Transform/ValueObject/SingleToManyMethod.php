<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
final class SingleToManyMethod
{
    /**
     * @var string
     */
    private $class;
    /**
     * @var string
     */
    private $singleMethodName;
    /**
     * @var string
     */
    private $manyMethodName;
    public function __construct(string $class, string $singleMethodName, string $manyMethodName)
    {
        $this->class = $class;
        $this->singleMethodName = $singleMethodName;
        $this->manyMethodName = $manyMethodName;
    }
    public function getObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->class);
    }
    public function getSingleMethodName() : string
    {
        return $this->singleMethodName;
    }
    public function getManyMethodName() : string
    {
        return $this->manyMethodName;
    }
}
