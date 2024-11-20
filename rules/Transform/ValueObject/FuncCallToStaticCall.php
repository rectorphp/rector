<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use Rector\Validation\RectorAssert;
final class FuncCallToStaticCall
{
    /**
     * @readonly
     */
    private string $oldFuncName;
    /**
     * @readonly
     */
    private string $newClassName;
    /**
     * @readonly
     */
    private string $newMethodName;
    public function __construct(string $oldFuncName, string $newClassName, string $newMethodName)
    {
        $this->oldFuncName = $oldFuncName;
        $this->newClassName = $newClassName;
        $this->newMethodName = $newMethodName;
        RectorAssert::functionName($oldFuncName);
        RectorAssert::className($newClassName);
        RectorAssert::methodName($newMethodName);
    }
    public function getOldFuncName() : string
    {
        return $this->oldFuncName;
    }
    public function getNewClassName() : string
    {
        return $this->newClassName;
    }
    public function getNewMethodName() : string
    {
        return $this->newMethodName;
    }
}
