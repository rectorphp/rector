<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Transform\ValueObject;

use RectorPrefix20220606\Rector\Core\Validation\RectorAssert;
use RectorPrefix20220606\Rector\Transform\Contract\ValueObject\ArgumentFuncCallToMethodCallInterface;
final class ArrayFuncCallToMethodCall implements ArgumentFuncCallToMethodCallInterface
{
    /**
     * @var non-empty-string
     * @readonly
     */
    private $function;
    /**
     * @var non-empty-string
     * @readonly
     */
    private $class;
    /**
     * @var non-empty-string
     * @readonly
     */
    private $arrayMethod;
    /**
     * @var non-empty-string
     * @readonly
     */
    private $nonArrayMethod;
    /**
     * @param non-empty-string $function
     * @param non-empty-string $class
     * @param non-empty-string $arrayMethod
     * @param non-empty-string $nonArrayMethod
     */
    public function __construct(string $function, string $class, string $arrayMethod, string $nonArrayMethod)
    {
        $this->function = $function;
        $this->class = $class;
        $this->arrayMethod = $arrayMethod;
        $this->nonArrayMethod = $nonArrayMethod;
        RectorAssert::className($class);
    }
    public function getFunction() : string
    {
        return $this->function;
    }
    public function getClass() : string
    {
        return $this->class;
    }
    public function getArrayMethod() : string
    {
        return $this->arrayMethod;
    }
    public function getNonArrayMethod() : string
    {
        return $this->nonArrayMethod;
    }
}
