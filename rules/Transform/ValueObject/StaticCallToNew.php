<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use Rector\Validation\RectorAssert;
final class StaticCallToNew
{
    /**
     * @readonly
     */
    private string $class;
    /**
     * @readonly
     */
    private string $method;
    public function __construct(string $class, string $method)
    {
        $this->class = $class;
        $this->method = $method;
        RectorAssert::className($class);
        RectorAssert::methodName($method);
    }
    public function getClass() : string
    {
        return $this->class;
    }
    public function getMethod() : string
    {
        return $this->method;
    }
}
