<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

final class FunctionToStaticCall
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
    private $method;
    public function __construct(string $function, string $class, string $method)
    {
        $this->function = $function;
        $this->class = $class;
        $this->method = $method;
    }
    public function getClass() : string
    {
        return $this->class;
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
