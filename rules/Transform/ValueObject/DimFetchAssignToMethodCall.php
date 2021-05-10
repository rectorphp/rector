<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
final class DimFetchAssignToMethodCall
{
    /**
     * @var string
     */
    private $listClass;
    /**
     * @var string
     */
    private $itemClass;
    /**
     * @var string
     */
    private $addMethod;
    public function __construct(string $listClass, string $itemClass, string $addMethod)
    {
        $this->listClass = $listClass;
        $this->itemClass = $itemClass;
        $this->addMethod = $addMethod;
    }
    public function getListObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->listClass);
    }
    public function getItemObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->itemClass);
    }
    public function getAddMethod() : string
    {
        return $this->addMethod;
    }
}
