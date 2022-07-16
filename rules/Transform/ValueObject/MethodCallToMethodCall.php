<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use Rector\Core\Validation\RectorAssert;
final class MethodCallToMethodCall
{
    /**
     * @var class-string
     * @readonly
     */
    private $oldType;
    /**
     * @readonly
     * @var string
     */
    private $oldMethod;
    /**
     * @var class-string
     * @readonly
     */
    private $newType;
    /**
     * @readonly
     * @var string
     */
    private $newMethod;
    /**
     * @param class-string $oldType
     * @param class-string $newType
     */
    public function __construct(string $oldType, string $oldMethod, string $newType, string $newMethod)
    {
        $this->oldType = $oldType;
        $this->oldMethod = $oldMethod;
        $this->newType = $newType;
        $this->newMethod = $newMethod;
        RectorAssert::className($oldType);
        RectorAssert::methodName($oldMethod);
        RectorAssert::className($newType);
        RectorAssert::methodName($newMethod);
    }
    public function getOldType() : string
    {
        return $this->oldType;
    }
    public function getOldMethod() : string
    {
        return $this->oldMethod;
    }
    public function getNewType() : string
    {
        return $this->newType;
    }
    public function getNewMethod() : string
    {
        return $this->newMethod;
    }
}
