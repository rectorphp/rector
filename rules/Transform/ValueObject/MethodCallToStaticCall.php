<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Validation\RectorAssert;
final class MethodCallToStaticCall
{
    /**
     * @readonly
     */
    private string $oldClass;
    /**
     * @readonly
     */
    private string $oldMethod;
    /**
     * @readonly
     */
    private string $newClass;
    /**
     * @readonly
     */
    private string $newMethod;
    public function __construct(string $oldClass, string $oldMethod, string $newClass, string $newMethod)
    {
        $this->oldClass = $oldClass;
        $this->oldMethod = $oldMethod;
        $this->newClass = $newClass;
        $this->newMethod = $newMethod;
        RectorAssert::className($oldClass);
        RectorAssert::className($oldMethod);
        RectorAssert::className($newClass);
        RectorAssert::className($newMethod);
    }
    public function getOldObjectType() : ObjectType
    {
        return new ObjectType($this->oldClass);
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
