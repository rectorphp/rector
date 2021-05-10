<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

final class ArrayFuncCallToMethodCall
{
    /**
     * @var string
     */
    private $function;
    /**
     * @var string
     */
    private $class;
    /**
     * @var string
     */
    private $arrayMethod;
    /**
     * @var string
     */
    private $nonArrayMethod;
    public function __construct(string $function, string $class, string $arrayMethod, string $nonArrayMethod)
    {
        $this->function = $function;
        $this->class = $class;
        $this->arrayMethod = $arrayMethod;
        $this->nonArrayMethod = $nonArrayMethod;
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
