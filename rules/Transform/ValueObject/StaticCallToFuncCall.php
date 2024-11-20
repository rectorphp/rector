<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
use Rector\Validation\RectorAssert;
final class StaticCallToFuncCall
{
    /**
     * @readonly
     */
    private string $class;
    /**
     * @readonly
     */
    private string $method;
    /**
     * @readonly
     */
    private string $function;
    public function __construct(string $class, string $method, string $function)
    {
        $this->class = $class;
        $this->method = $method;
        $this->function = $function;
        RectorAssert::className($class);
        RectorAssert::methodName($method);
        RectorAssert::functionName($function);
    }
    public function getObjectType() : ObjectType
    {
        return new ObjectType($this->class);
    }
    public function getMethod() : string
    {
        return $this->method;
    }
    public function getFunction() : string
    {
        return $this->function;
    }
}
