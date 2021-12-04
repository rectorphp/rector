<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
final class MethodCallToStaticCall
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
    private $newClass;
    /**
     * @readonly
     * @var string
     */
    private $newMethod;
    public function __construct(string $oldClass, string $oldMethod, string $newClass, string $newMethod)
    {
        $this->oldClass = $oldClass;
        $this->oldMethod = $oldMethod;
        $this->newClass = $newClass;
        $this->newMethod = $newMethod;
    }
    public function getOldObjectType() : \PHPStan\Type\ObjectType
    {
        return new \PHPStan\Type\ObjectType($this->oldClass);
    }
    public function getOldMethod() : string
    {
        return $this->oldMethod;
    }
    public function getNewClass() : string
    {
        return $this->newClass;
    }
    public function getNewMethod() : string
    {
        return $this->newMethod;
    }
}
