<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;
final class GetAndSetToMethodCall
{
    /**
     * @var class-string
     * @readonly
     */
    private $classType;
    /**
     * @readonly
     * @var string
     */
    private $getMethod;
    /**
     * @readonly
     * @var string
     */
    private $setMethod;
    /**
     * @param class-string $classType
     */
    public function __construct(string $classType, string $getMethod, string $setMethod)
    {
        $this->classType = $classType;
        $this->getMethod = $getMethod;
        $this->setMethod = $setMethod;
        RectorAssert::methodName($getMethod);
        RectorAssert::methodName($setMethod);
    }
    public function getGetMethod() : string
    {
        return $this->getMethod;
    }
    public function getSetMethod() : string
    {
        return $this->setMethod;
    }
    public function getObjectType() : ObjectType
    {
        return new ObjectType($this->classType);
    }
}
