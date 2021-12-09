<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Core\Validation\RectorAssert;
final class SingleToManyMethod
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
    private $singleMethodName;
    /**
     * @readonly
     * @var string
     */
    private $manyMethodName;
    public function __construct(string $class, string $singleMethodName, string $manyMethodName)
    {
        $this->class = $class;
        $this->singleMethodName = $singleMethodName;
        $this->manyMethodName = $manyMethodName;
        \Rector\Core\Validation\RectorAssert::className($class);
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
