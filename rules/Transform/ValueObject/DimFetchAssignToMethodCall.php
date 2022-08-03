<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;
final class DimFetchAssignToMethodCall
{
    /**
     * @var class-string
     * @readonly
     */
    private $listClass;
    /**
     * @var class-string
     * @readonly
     */
    private $itemClass;
    /**
     * @readonly
     * @var string
     */
    private $addMethod;
    /**
     * @param class-string $listClass
     * @param class-string $itemClass
     */
    public function __construct(string $listClass, string $itemClass, string $addMethod)
    {
        $this->listClass = $listClass;
        $this->itemClass = $itemClass;
        $this->addMethod = $addMethod;
        RectorAssert::methodName($addMethod);
    }
    public function getListObjectType() : ObjectType
    {
        return new ObjectType($this->listClass);
    }
    public function getItemObjectType() : ObjectType
    {
        return new ObjectType($this->itemClass);
    }
    public function getAddMethod() : string
    {
        return $this->addMethod;
    }
}
