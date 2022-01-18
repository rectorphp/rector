<?php

declare (strict_types=1);
namespace Rector\CakePHP\ValueObject;

use PHPStan\Type\ObjectType;
final class RenameMethodCallBasedOnParameter
{
    /**
     * @readonly
     * @var string
     */
    private $oldClass;
    /**
     * @readonly
     * @var string
     */
    private $oldMethod;
    /**
     * @readonly
     * @var string
     */
    private $parameterName;
    /**
     * @readonly
     * @var string
     */
    private $newMethod;
    public function __construct(string $oldClass, string $oldMethod, string $parameterName, string $newMethod)
    {
        $this->oldClass = $oldClass;
        $this->oldMethod = $oldMethod;
        $this->parameterName = $parameterName;
        $this->newMethod = $newMethod;
    }
    public function getOldMethod() : string
    {
        return $this->oldMethod;
    }
    public function getParameterName() : string
    {
        return $this->parameterName;
    }
    public function getNewMethod() : string
    {
        return $this->newMethod;
    }
    public function getOldObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->oldClass);
    }
}
